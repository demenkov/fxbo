<?php

declare(strict_types=1);

namespace FXBO\Parser;

use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Traversable;

final class CoinDeskParser implements ParserInterface
{
    private const BASE = 'BTC';
    private const QUOTE = 'USD';
    private const URL = '/v1/bpi/historical/close.json';
    private HttpClientInterface $client;
    public function __construct(
        HttpClientInterface $coinDesk
    ) {
        $this->client = $coinDesk;
    }
    public function parse(): Traversable
    {
        $data = $this->fetch();
        if (0 === count($data['bpi'])) {
            throw new RuntimeException('no price index in rates');
        }
        foreach ($data['bpi'] as $date => $price) {
            yield [
                'date' => $date,
                'base' => static::BASE,
                'quote' => static::QUOTE,
                'price' => (string) $price,
                'provider' => 'coindesk',
            ];
        }
    }
    private function fetch(): array
    {
        $response = $this->client->request('GET', static::URL);
        if (200 === $response->getStatusCode()) {
            return $response->toArray();
        }
        throw new RuntimeException('unable to fetch coindesk rates');
    }
}
