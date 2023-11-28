<?php

namespace App\Custom;

use GuzzleHttp\Client;

class GoogleApi {
    static function geocodeAddress($address)
    {
        $client = new Client();
        $apiKey = getenv('GOOGLE_API_KEY');
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';

        try {
            $response = $client->get($url, [
                'query' => [
                    'address' => $address,
                    'key' => $apiKey,
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (!empty($data['results'][0]['geometry']['location'])) {
                $location = $data['results'][0]['geometry']['location'];
                $latLongObj = [
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                ];
                return $latLongObj;
            } else {
                throw new Exception('No results found for the given address.');
            }
        } catch (Exception $e) {
            // Handle exception
            echo $e->getMessage();
        }
    }

    static function validateAddress ($address) {
        $client = new Client();
        $apiKey = getenv('GOOGLE_API_KEY');
        $url = "https://addressvalidation.googleapis.com/v1:validateAddress?key={$apiKey}";
        $body = [
            'address' => [
                'addressLines' => [$address]
            ]
        ];

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $body
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {
                $response_data = json_decode($response->getBody(), true);
                $verdict = $response_data['result']['verdict'];
                if(!isset($verdict['addressComplete']) || $verdict['addressComplete'] !== true || isset($response_data['result']['verdict']['hasUnconfirmedComponents'])) {
                    return false;
                }

                return true;
            } else {
                // handle error
                return false;
            }
        } catch (Exception $e) {
            // handle exception
            return false;
        }
    }
}


