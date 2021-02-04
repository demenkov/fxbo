<?php

declare(strict_types=1);

namespace FXBO\Parser;

use RuntimeException;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Traversable;

final class EcbParser implements ParserInterface
{
    private const BASE = 'EUR';
    private const URL = '/stats/eurofxref/eurofxref-daily.xml';
    private HttpClientInterface $client;
    public function __construct(
        HttpClientInterface $ecb
    ) {
        $this->client = $ecb;
    }
    public function parse(): Traversable
    {
        $data = $this->fetch();
        $data->registerXPathNamespace('xmlns', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');
        /** @var SimpleXMLElement|false $elements */
        $elements = $data->xpath('//xmlns:Cube[@currency]');
        if (false === $elements) {
            throw new RuntimeException('unable to fetch ecb rates');
        }
        /** @var SimpleXMLElement|false $dates */
        $dates = $data->xpath('//xmlns:Cube[@time]/@time');
        if (false === $dates) {
            throw new RuntimeException('unable to fetch ecb rates');
        }
        $date = (string) $dates[0];
        /** @var SimpleXMLElement $element */
        foreach ($elements as $i => $element) {
            yield [
                'date' => $date,
                'base' => static::BASE,
                'quote' => (string) $element->xpath('//@currency')[$i],
                'price' => (string) $element->xpath('//@rate')[$i],
                'provider' => 'ecb',
            ];
        }
    }
    private function fetch(): SimpleXMLElement
    {
        $response = $this->client->request('GET', static::URL);
        if (200 === $response->getStatusCode()) {
            return new SimpleXMLElement($response->getContent());
        }
        throw new RuntimeException('unable to fetch ecb rates');
    }
}
