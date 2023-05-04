<?php

namespace App\Tests;

use App\Service\BinProviderService;
use App\Service\ExchangeRateService;
use App\Service\Impl\TransactionProcessorImpl;
use App\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class TransactionProcessorTest extends TestCase
{
    private const binLookup = [
        "number" => [
            "length" => 16,
            "luhn" => true
        ],
        "scheme" => "visa",
        "type" => "debit",
        "brand" => "Visa/Dankort",
        "prepaid" => false,
        "country" => [
            "numeric" => "208",
            "alpha2" => "DK",
            "name" => "Denmark",
            "emoji" => "🇩🇰",
            "currency" => "DKK",
            "latitude" => 56,
            "longitude" => 10
        ],
        "bank" => [
            "name" => "Jyske Bank",
            "url" => "www.jyskebank.dk",
            "phone" => "+4589893300",
            "city" => "Hjørring"
        ]
    ];
    private const exchangeRates = [
        "success" => true,
        "timestamp" => 1683194343,
        "base" => "EUR",
        "date" => "2023-05-04",
        "rates" => [
            "AED" => 4.059541,
            "AFN" => 96.034824,
            "ALL" => 111.05586,
            "AMD" => 428.585227,
            "ANG" => 1.984573,
            "AOA" => 562.67323,
            "ARS" => 248.938405,
            "AUD" => 1.656437,
            "AWG" => 1.992571,
            "AZN" => 1.875834,
            "BAM" => 1.952001,
            "BBD" => 2.223413,
            "BDT" => 117.282793,
            "BGN" => 1.955036,
            "BHD" => 0.416748,
            "BIF" => 2293.939046,
            "BMD" => 1.105448,
            "BND" => 1.466805,
            "BOB" => 7.609087,
            "BRL" => 5.520721,
            "BSD" => 1.101177,
            "BTC" => 3.8001626E-5,
            "BTN" => 90.065479,
            "BWP" => 14.52712,
            "BYN" => 2.779466,
            "BYR" => 21666.789773,
            "BZD" => 2.21962,
            "CAD" => 1.504173,
            "CDF" => 2314.809541,
            "CHF" => 0.980953,
            "CLF" => 0.032228,
            "CLP" => 889.277523,
            "CNY" => 7.645948,
            "COP" => 5142.269864,
            "CRC" => 598.052237,
            "CUC" => 1.105448,
            "CUP" => 29.294384,
            "CVE" => 110.044327,
            "CZK" => 23.48592,
            "DJF" => 196.06195,
            "DKK" => 7.451507,
            "DOP" => 60.124611,
            "DZD" => 149.525954,
            "EGP" => 34.212492,
            "ERN" => 16.581727,
            "ETB" => 60.073609,
            "EUR" => 1,
            "FJD" => 2.445197,
            "FKP" => 0.881087,
            "GBP" => 0.879306,
            "GEL" => 2.7414,
            "GGP" => 0.881087,
            "GHS" => 13.048427,
            "GIP" => 0.881087,
            "GMD" => 66.381748,
            "GNF" => 9466.746079,
            "GTQ" => 8.594661,
            "GYD" => 232.893557,
            "HKD" => 8.675504,
            "HNL" => 27.061065,
            "HRK" => 7.534884,
            "HTG" => 167.38255,
            "HUF" => 373.40719,
            "IDR" => 16249.152699,
            "ILS" => 4.024241,
            "IMP" => 0.881087,
            "INR" => 90.450555,
            "IQD" => 1442.498978,
            "IRR" => 46732.833266,
            "ISK" => 150.097899,
            "JEP" => 0.881087,
            "JMD" => 168.446722,
            "JOD" => 0.784318,
            "JPY" => 148.825973,
            "KES" => 150.769333,
            "KGS" => 96.749107,
            "KHR" => 4537.394588,
            "KMF" => 492.642854,
            "KPW" => 994.899762,
            "KRW" => 1462.640927,
            "KWD" => 0.338621,
            "KYD" => 0.917618,
            "KZT" => 490.956668,
            "LAK" => 19128.855893,
            "LBP" => 16528.778484,
            "LKR" => 352.126247,
            "LRD" => 182.955061,
            "LSL" => 20.329462,
            "LTL" => 3.264102,
            "LVL" => 0.668675,
            "LYD" => 5.250875,
            "MAD" => 11.061667,
            "MDL" => 19.733129,
            "MGA" => 4848.650759,
            "MKD" => 61.613799,
            "MMK" => 2312.441117,
            "MNT" => 3859.78273,
            "MOP" => 8.903811,
            "MRO" => 394.644909,
            "MUR" => 50.018934,
            "MVR" => 16.9686,
            "MWK" => 1130.320499,
            "MXN" => 19.822352,
            "MYR" => 4.922528,
            "MZN" => 69.919428,
            "NAD" => 20.196505,
            "NGN" => 508.893183,
            "NIO" => 40.272164,
            "NOK" => 11.824542,
            "NPR" => 144.102443,
            "NZD" => 1.769723,
            "OMR" => 0.425035,
            "PAB" => 1.101142,
            "PEN" => 4.090875,
            "PGK" => 3.881551,
            "PHP" => 61.195389,
            "PKR" => 312.324097,
            "PLN" => 4.584492,
            "PYG" => 7914.738683,
            "QAR" => 4.024976,
            "RON" => 4.929529,
            "RSD" => 117.274715,
            "RUB" => 86.568,
            "RWF" => 1226.546051,
            "SAR" => 4.145944,
            "SBD" => 9.21271,
            "SCR" => 14.641542,
            "SDG" => 662.718774,
            "SEK" => 11.344897,
            "SGD" => 1.468406,
            "SHP" => 1.345055,
            "SLE" => 25.096154,
            "SLL" => 21832.606788,
            "SOS" => 629.565911,
            "SRD" => 41.236578,
            "STD" => 22880.551185,
            "SVC" => 9.63529,
            "SYP" => 2777.490549,
            "SZL" => 20.156317,
            "THB" => 37.403403,
            "TJS" => 12.013347,
            "TMT" => 3.86907,
            "TND" => 3.363329,
            "TOP" => 2.603989,
            "TRY" => 21.543787,
            "TTD" => 7.476482,
            "TWD" => 33.911068,
            "TZS" => 2601.120215,
            "UAH" => 40.671177,
            "UGX" => 4100.962621,
            "USD" => 1.105448,
            "UYU" => 42.725876,
            "UZS" => 12595.087315,
            "VEF" => 2736904.421221,
            "VES" => 27.398144,
            "VND" => 25920.002715,
            "VUV" => 131.511759,
            "WST" => 3.006241,
            "XAF" => 654.743437,
            "XAG" => 0.043156,
            "XAU" => 0.000542,
            "XCD" => 2.98753,
            "XDR" => 0.818921,
            "XOF" => 654.737525,
            "XPF" => 119.830298,
            "YER" => 276.708708,
            "ZAR" => 20.168918,
            "ZMK" => 9950.358296,
            "ZMW" => 19.628153,
            "ZWL" => 355.953952
        ]
    ];

    public function test_getBinCountry_returns_country_class_by_given_bin_number()
    {
        $provider = new BinProviderService($this->mockBinApiClient());
        $country = $provider->getBinCountry('45717360');
        $this->assertObjectHasProperty('name', $country);
        $this->assertObjectHasProperty('currency', $country);
        $this->assertObjectHasProperty('countryCode', $country);
        $this->assertEquals($country->name, 'Denmark');
        $this->assertEquals($country->currency, 'DKK');
        $this->assertEquals($country->countryCode, 'DK');
    }

    public function test_getBinCountry_fails_when_bin_number_is_missing()
    {
        $provider = new BinProviderService($this->mockBinApiClient(), $this->mockRateApiClient());
        $this->expectException(\ArgumentCountError::class);
        $provider->getBinCountry();
    }

    public function test_getBinCountry_returns_503_when_remote_service_fails()
    {
        $mock = new MockHandler([
            function ($request) {
                $this->assertEquals('GET', $request->getMethod());
                return new Response(
                    502,
                    [
                        'accept' => 'application/json'
                    ],
                    json_encode(self::binLookup)
                );
            }
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $provider = new BinProviderService($client);
        $this->expectExceptionCode(503);
        $this->expectException(\Exception::class);
        $provider->getBinCountry(45717360);
    }

    public function test_getBinCountry_returns_422_when_remote_service_returns_other_than_200()
    {
        $mock = new MockHandler([
            function ($request) {
                $this->assertEquals('GET', $request->getMethod());
                return new Response(
                    204,
                    [
                        'accept' => 'application/json'
                    ],
                    json_encode(self::binLookup)
                );
            }
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $provider = new BinProviderService($client);
        $this->expectExceptionCode(422);
        $this->expectException(\Exception::class);
        $provider->getBinCountry(45717360);
    }

    public function test_getRate_returns_float_by_given_currency()
    {
        $processor = new ExchangeRateService($this->mockRateApiClient());
        $this->assertIsFloat($processor->getRate('usd'));
    }

    public function test_readTransactions_returns_array_of_transactions()
    {
        $mockFile = tmpfile();
        fwrite($mockFile, '{"bin": "1234", "amount": 10.00, "currency": "USD"}' . PHP_EOL);
        fwrite($mockFile, '{"bin": "5678", "amount": 20.00, "currency": "EUR"}' . PHP_EOL);
        fwrite($mockFile, '{"bin": "9012", "amount": 30.00, "currency": "GBP"}' . PHP_EOL);
        fseek($mockFile, 0);
        $metaData = stream_get_meta_data($mockFile);
        $fileUri = $metaData['uri'];
        $processor = new TransactionProcessorImpl();
        $response = $processor->readTransactions($fileUri);
        $this->assertIsArray($response);
        $this->assertContainsOnlyInstancesOf(Transaction::class, $response);
    }

    public function test_readTransactions_returns_500_when_broken_input()
    {
        $mockFile = tmpfile();
        fwrite($mockFile, '{"bin": "1234", "amount": 10.00}' . PHP_EOL);
        fwrite($mockFile, '{"bin": "5678", "amount": 20.00, "currency": "EUR"}' . PHP_EOL);
        fwrite($mockFile, '{"bin": "9012", "amount": 30.00, "currency": "GBP"}' . PHP_EOL);
        fseek($mockFile, 0);
        $metaData = stream_get_meta_data($mockFile);
        $filepath = $metaData['uri'];
        $processor = new TransactionProcessorImpl();
        $this->expectException(\Exception::class);
        $this->expectExceptionCode(422);
        $processor->readTransactions($filepath);
    }

    public function test_getCommissionedAmount_returns_BigDecimal_for_given_transaction()
    {
        $mockFile = tmpfile();
        fwrite($mockFile, '{"bin": "1234", "amount": 10.00, "currency": "USD"}' . PHP_EOL);
        fwrite($mockFile, '{"bin": "5678", "amount": 20.00, "currency": "EUR"}' . PHP_EOL);
        fwrite($mockFile, '{"bin": "9012", "amount": 30.00, "currency": "GBP"}' . PHP_EOL);
        fseek($mockFile, 0);
        $metaData = stream_get_meta_data($mockFile);
        $fileUri = $metaData['uri'];

        $processor = new TransactionProcessorImpl(new BinProviderService($this->mockBinApiClient()), new ExchangeRateService($this->mockRateApiClient()));

        $response = $processor->readTransactions($fileUri);

        $transaction = $response[0];

        $commission = $processor->getCommissionedAmount($transaction)->toFloat();


        $this->assertIsFloat($commission);
    }


    private function mockBinApiClient()
    {
        $mock = new MockHandler([
            function (Request $request) {
                $this->assertMatchesRegularExpression('/^https:\/\/lookup\.binlist\.net\/\d+$/i', $request->getUri());
                return new Response(
                    200,
                    [
                        'accept' => 'application/json'
                    ],
                    json_encode(self::binLookup)
                );
            },
            function (Request $request) {
                $this->assertEquals('GET', $request->getUri());
                return new Response(
                    200,
                    [
                        'accept' => 'application/json'
                    ],
                    json_encode(self::exchangeRates)
                );
            }
        ]);
        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);
    }

    private function mockRateApiClient()
    {
        $mock = new MockHandler([
            function (Request $request) {
                $this->assertEquals('https://api.apilayer.com/exchangerates_data/latest', $request->getUri());
                return new Response(
                    200,
                    [
                        'accept' => 'application/json'
                    ],
                    json_encode(self::exchangeRates)
                );
            }
        ]);
        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);
    }
}
