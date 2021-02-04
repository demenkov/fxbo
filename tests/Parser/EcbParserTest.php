<?php

namespace FXBO\Tests\Parser;

use FXBO\Parser\EcbParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class EcbParserTest extends TestCase
{
    public function testParse(): void
    {
        $xml = <<<XML
<gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref" capture-installed="true">
<gesmes:subject>Reference rates</gesmes:subject>
<gesmes:Sender>
<gesmes:name>European Central Bank</gesmes:name>
</gesmes:Sender>
<Cube>
<Cube time="2021-01-29">
<Cube currency="USD" rate="1.2136"/>
</Cube>
</Cube>
</gesmes:Envelope>
XML;
        $client = new MockHttpClient([
            new MockResponse($xml, ['http_code' => 200]),
        ], 'http://localhost');
        $parser = new EcbParser($client);
        self::assertEquals([
            [
                'price' => '1.2136',
                'date' => '2021-01-29',
                'base' => 'EUR',
                'quote' => 'USD',
                'provider' => 'ecb',
            ],
        ], iterator_to_array($parser->parse()));
    }
}
