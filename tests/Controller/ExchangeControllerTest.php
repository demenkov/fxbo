<?php

namespace FXBO\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExchangeControllerTest extends WebTestCase
{
    public function rates(): array
    {
        return [
            ['1', 'ZZZ', 'ZZZ', '1'],
            ['1', 'XXX', 'YYY', '1'],
            ['1', 'YYY', 'XXX', '1'],
            ['0.5', 'XXX', 'KKK', '1'],
            ['2', 'YYY', 'KKK', '1'],
            ['0.5', 'KKK', 'YYY', '1'],
            ['0.833', 'XXX', 'FFF', '1'],
            ['0.333', 'XXX', 'GGG', '1'],
        ];
    }
    /**
     * @dataProvider rates
     */
    public function testExchange(
        string $expected,
        string $from,
        string $to,
        string $amount
    ): void {
        $client = static::createClient();
        $query = http_build_query([
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
        ]);
        $client->request('GET', '/exchange?' . $query);
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals(
            $expected,
            $client->getResponse()->getContent()
        );
    }
}
