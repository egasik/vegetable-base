<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class Geocoder
{
    public function resolve(string $address): array
    {
        $key = config('services.yandex.geocoder_key');

        if (! $key) {
            throw ValidationException::withMessages([
                'delivery_address' => 'Не заполнен ключ API Геокодера.',
            ]);
        }

        $client = Http::acceptJson()->timeout(15);

        $certificates = resource_path('certificates/cacert.pem');

        if (is_file($certificates)) {
            $client = $client->withOptions([
                'verify' => $certificates,
            ]);
        }

        try {
            $response = $client->get(
                'https://geocode-maps.yandex.ru/v1/',
                [
                    'apikey' => $key,
                    'geocode' => $address,
                    'format' => 'json',
                    'results' => 1,
                    'lang' => 'ru_RU',
                ]
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw ValidationException::withMessages([
                'delivery_address' =>
                    'Не удалось подключиться к геокодеру. Проверьте интернет и сертификаты PHP.',
            ]);
        }

        if (! $response->successful()) {
            $message = match ($response->status()) {
                401, 403 =>
                    'Геокодер отклонил доступ. Проверьте ключ, подключение API и ограничения ключа.',
                429 =>
                    'Превышен лимит запросов геокодера. Повторите позже.',
                default =>
                    'Ошибка геокодера HTTP '.$response->status().'. Повторите позже.',
            };

            throw ValidationException::withMessages([
                'delivery_address' => $message,
            ]);
        }

        $object = data_get(
            $response->json(),
            'response.GeoObjectCollection.featureMember.0.GeoObject'
        );

        if (! $object) {
            throw ValidationException::withMessages([
                'delivery_address' =>
                    'Адрес не найден. Укажите город, улицу и номер дома.',
            ]);
        }

        $metadata = data_get(
            $object,
            'metaDataProperty.GeocoderMetaData',
            []
        );

        $components = data_get($metadata, 'Address.Components', []);

        $inRegion = collect($components)->contains(
            fn ($part) =>
                ($part['kind'] ?? '') === 'province'
                && mb_strtolower(trim($part['name'] ?? ''))
                    === 'иркутская область'
        );

        if (! $inRegion) {
            throw ValidationException::withMessages([
                'delivery_address' =>
                    'Доставка доступна только по Иркутску и Иркутской области.',
            ]);
        }

        if (! in_array(
            $metadata['precision'] ?? '',
            ['exact', 'number'],
            true
        )) {
            throw ValidationException::withMessages([
                'delivery_address' =>
                    'Уточните город, улицу и номер дома.',
            ]);
        }

        $coordinates = preg_split(
            '/\s+/',
            trim(data_get($object, 'Point.pos', ''))
        );

        if (
            count($coordinates) !== 2
            || ! is_numeric($coordinates[0])
            || ! is_numeric($coordinates[1])
        ) {
            throw ValidationException::withMessages([
                'delivery_address' =>
                    'Не удалось определить координаты дома.',
            ]);
        }

        return [
            'delivery_address' => $metadata['text'],
            'longitude' => $coordinates[0],
            'latitude' => $coordinates[1],
        ];
    }
}
