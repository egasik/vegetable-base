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
        
        if (empty($query)) {
            return response()->json([]);
        }

        $response = Http::withOptions(['verify' => false])->get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => $this->geocoderApiKey,
            'geocode' => "Иркутская область, $query",
            'format' => 'json',
            'results' => 20,
            'kind' => 'locality',
        ]);

        $data = $response->json();
        $cities = [];

        if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
            foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
                $geoObject = $item['GeoObject'];
                $name = $geoObject['name'];
                $description = $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? '';
                
                if (str_contains($description, 'Иркутская')) {
                    $cities[] = [
                        'name' => $name,
                        'full_address' => $description,
                    ];
                }
            }
        }

        return response()->json($cities);
    }

    public function getStreets(Request $request)
    {
        $city = $request->get('city', '');
        $query = $request->get('q', '');
        
        if (empty($city) || empty($query)) {
            return response()->json([]);
        }

        $response = Http::withOptions(['verify' => false])->get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => $this->geocoderApiKey,
            'geocode' => "Иркутская область, $city, $query",
            'format' => 'json',
            'results' => 20,
            'kind' => 'street',
        ]);

        $data = $response->json();
        $streets = [];

        if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
            foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
                $geoObject = $item['GeoObject'];
                $name = $geoObject['name'];
                $description = $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? '';
                
                if (str_contains($description, $city)) {
                    $streets[] = [
                        'name' => $name,
                        'full_address' => $description,
                    ];
                }
            }
        }

        return response()->json($streets);
    }

    public function getHouses(Request $request)
    {
        $city = $request->get('city', '');
        $street = $request->get('street', '');
        $query = $request->get('q', '');
        
        if (empty($city) || empty($street)) {
            return response()->json([]);
        }

        $fullQuery = "Иркутская область, $city, $street";
        if (!empty($query)) {
            $fullQuery .= ", $query";
        }

        $response = Http::withOptions(['verify' => false])->get('https://geocode-maps.yandex.ru/1.x/', [
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
                $name = $geoObject['name'];
                $description = $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? '';
                $coordinates = $geoObject['Point']['pos'] ?? '';
                
                if (str_contains($description, $city) && str_contains($description, $street)) {
                    $coords = explode(' ', $coordinates);
                    $houses[] = [
                        'name' => $name,
                        'full_address' => $description,
                        'latitude' => $coords[1] ?? null,
                        'longitude' => $coords[0] ?? null,
                    ];
                }
            }
        }

        return response()->json($houses);
    }

    public function validateAddress(Request $request)
    {
        $city = $request->get('city', '');
        $street = $request->get('street', '');
        $house = $request->get('house', '');
        
        if (empty($city) || empty($street) || empty($house)) {
            return response()->json([
                'valid' => false,
                'message' => 'Заполните все поля',
            ]);
        }

        $fullAddress = "Иркутская область, $city, $street, $house";

        $response = Http::withOptions(['verify' => false])->get('https://geocode-maps.yandex.ru/1.x/', [
            'apikey' => $this->geocoderApiKey,
            'geocode' => $fullAddress,
            'format' => 'json',
            'results' => 1,
        ]);

        $data = $response->json();

        if (isset($data['response']['GeoObjectCollection']['featureMember'][0])) {
            $geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];
            $coordinates = $geoObject['Point']['pos'] ?? '';
            $coords = explode(' ', $coordinates);
            
            return response()->json([
                'valid' => true,
                'full_address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text'],
                'latitude' => $coords[1] ?? null,
                'longitude' => $coords[0] ?? null,
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Адрес не найден',
        ]);
    }
}