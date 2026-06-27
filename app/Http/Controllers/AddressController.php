<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AddressController extends Controller
{
    private $geocoderApiKey;

    public function __construct()
    {
        $this->geocoderApiKey = config('services.yandex.maps_geocoder_key');
    }

    public function getCities(Request $request)
    {
        $query = $request->get('q', '');
        if (empty($query)) return response()->json([]);

        try {
            $response = Http::withoutVerifying()->get('https://geocode-maps.yandex.ru/1.x/', [
                'apikey' => $this->geocoderApiKey,
                'geocode' => $query,
                'format' => 'json',
                'results' => 20,
                'kind' => 'locality',
            ]);

            $data = $response->json();
            $cities = [];

            if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
                foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
                    $geoObject = $item['GeoObject'];
                    $metaData = $geoObject['metaDataProperty']['GeocoderMetaData'];
                    
                    if (!isset($metaData['kind']) || $metaData['kind'] !== 'locality') continue;
                    
                    $cities[] = [
                        'name' => $geoObject['name'],
                        'full_address' => $metaData['text'] ?? '',
                    ];
                }
            }

            return response()->json($cities);
        } catch (\Exception $e) {
            \Log::error('getCities: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStreets(Request $request)
    {
        $city = $request->get('city', '');
        $query = $request->get('q', '');
        if (empty($city) || empty($query)) return response()->json([]);

        try {
            $response = Http::withoutVerifying()->get('https://geocode-maps.yandex.ru/1.x/', [
                'apikey' => $this->geocoderApiKey,
                'geocode' => "$city, $query",
                'format' => 'json',
                'results' => 20,
                'kind' => 'street',
            ]);

            $data = $response->json();
            $streets = [];

            if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
                foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
                    $geoObject = $item['GeoObject'];
                    $streets[] = [
                        'name' => $geoObject['name'],
                        'full_address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? '',
                    ];
                }
            }

            return response()->json($streets);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getHouses(Request $request)
    {
        $city = $request->get('city', '');
        $street = $request->get('street', '');
        $query = $request->get('q', '');
        if (empty($city) || empty($street)) return response()->json([]);

        $fullQuery = "$city, $street";
        if (!empty($query)) $fullQuery .= " $query";

        try {
            $response = Http::withoutVerifying()->get('https://geocode-maps.yandex.ru/1.x/', [
                'apikey' => $this->geocoderApiKey,
                'geocode' => $fullQuery,
                'format' => 'json',
                'results' => 50,
                'kind' => 'house',
            ]);

            $data = $response->json();
            $houses = [];

            if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
                foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
                    $geoObject = $item['GeoObject'];
                    $coordinates = $geoObject['Point']['pos'] ?? '';
                    $coords = explode(' ', $coordinates);
                    
                    $houses[] = [
                        'name' => $geoObject['name'],
                        'full_address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? '',
                        'latitude' => $coords[1] ?? null,
                        'longitude' => $coords[0] ?? null,
                    ];
                }
            }

            return response()->json($houses);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function validateAddress(Request $request)
    {
        $city = $request->get('city', '');
        $street = $request->get('street', '');
        $house = $request->get('house', '');

        if (empty($city) || empty($street) || empty($house)) {
            return response()->json(['valid' => false, 'message' => 'Заполните все поля']);
        }

        try {
            $response = Http::withoutVerifying()->get('https://geocode-maps.yandex.ru/1.x/', [
                'apikey' => $this->geocoderApiKey,
                'geocode' => "$city, $street, $house",
                'format' => 'json',
                'results' => 1,
            ]);

            $data = $response->json();

            if (isset($data['response']['GeoObjectCollection']['featureMember'][0])) {
                $geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];
                $coords = explode(' ', $geoObject['Point']['pos'] ?? '');
                
                return response()->json([
                    'valid' => true,
                    'full_address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text'],
                    'latitude' => $coords[1] ?? null,
                    'longitude' => $coords[0] ?? null,
                ]);
            }

            return response()->json(['valid' => false, 'message' => 'Адрес не найден']);
        } catch (\Exception $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 500);
        }
    }
}