<?php

namespace FXBO\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OptionsControllerTest extends WebTestCase
{
    public function testOptions(): void
    {
        $client = static::createClient([], [
            'HTTP_ORIGIN' => 'test.test',
        ]);
        $client->request('OPTIONS', '/any');
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        self::assertEquals(
            'test.test',
            $client->getResponse()->headers->get('access-control-allow-origin')
        );
    }
}
