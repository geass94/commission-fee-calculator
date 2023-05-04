<?php

namespace App\Service;

use App\DTO\Country;
use Exception;
use GuzzleHttp\Client;

class BinProviderService
{
    public function __construct(private $httpClient = new Client(['verify' => false]))
    {
    }

    public function setHttpClient(Client $client): void
    {
        $this->httpClient = $client;
    }

    public function getBinCountry(int|string $bin): Country
    {
        try {
            $req = $this->httpClient->get("https://lookup.binlist.net/{$bin}");
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage(), 503);
        }
        if ($req->getStatusCode() !== 200) throw new Exception('API call failed', 422);
        $res = json_decode($req->getBody()->getContents(), true);
        return new Country($res['country']['name'], $res['country']['currency'], $res['country']['alpha2']);
    }
}