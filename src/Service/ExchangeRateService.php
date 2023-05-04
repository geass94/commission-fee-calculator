<?php
declare(strict_types=1);

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExchangeRateService
{
    public function __construct (private ParameterBagInterface $parameterBag, private $httpClient = new Client(['verify' => false]))
    {
    }

    public function setHttpClient(Client $client): void
    {
        $this->httpClient = $client;
    }

    public function getRate(string $currency): float
    {
        try {
            $req = $this->httpClient->get("https://api.apilayer.com/exchangerates_data/latest", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'apikey' => $this->parameterBag->get('apiLayerKey')
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