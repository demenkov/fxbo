<?php

namespace FXBO\Tests\Parser;

use FXBO\Parser\CoinDeskParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CoinDescParserTest extends TestCase
{
    public function testParse(): void
    {
        $client = new MockHttpClient([
            new MockResponse('{"bpi":{"2020-12-31":28956.265}}', ['http_code' => 200]),
        ], 'http://localhost');
        $parser = new CoinDeskParser($client);
        self::assertEquals([
            [
                'price' => '28956.265',
                'date' => '2020-12-31',
                'base' => 'BTC',
                'quote' => 'USD',
                'provider' => 'coindesk',
            ],
        ], iterator_to_array($parser->parse()));
    }
}
