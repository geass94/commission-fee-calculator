<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;

class ExchangeRateService
{
    public function __construct(private $httpClient = new Client(['verify' => false]))
    {
    }

    public function getRate(string $currency): float
    {
        try {
            $req = $this->httpClient->get("https://api.apilayer.com/exchangerates_data/latest", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => 'mZ2vhmo0c2LBCE5wFRG1O6eI5HT1WOgs'
                ]
            ]);
        } catch (\Throwable $exception) {
            throw new Exception($exception->getMessage(), 503);
        }

        if ($req->getStatusCode() !== 200) throw new Exception('Failed to retrieve rates', 400);
        $res = json_decode($req->getBody()->getContents(), true);
        return $res['rates'][strtoupper($currency)];
    }
}