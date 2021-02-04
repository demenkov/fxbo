<?php

namespace FXBO\Tests\Controller;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use FXBO\Entity\Rate;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RateControllerTest extends WebTestCase
{
    private ?EntityManager $entityManager;
    private Rate $rate;

    protected function setUp(): void
    {
        parent::bootKernel();
        $this->entityManager = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->rate = new Rate(
            new DateTimeImmutable('now'),
            'TST',
            'TST',
            '111',
            'TST'
        );
        $this->entityManager->persist($this->rate);
        $this->entityManager->flush();
        $this->entityManager->refresh($this->rate);
    }
    public function testList(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/rate?quote=TST');
        self::assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals(1, count($data['items']));
    }
    public function testUpdate(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('PUT', '/rate/' . $this->rate->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], '{"price":"22"}');
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals($this->rate->getId(), $data['id']);
        self::assertEquals('22.000000', $data['price']);
    }
    public function testDelete(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('DELETE', '/rate/' . $this->rate->getId());
        self::assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $client->request('GET', '/rate?quote=TST');
        self::assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals(0, count($data['items']));
    }

    protected function tearDown(): void
    {
        $rate = $this->entityManager->find(Rate::class, $this->rate->getId());
        if ($rate) {
            $this->entityManager->remove($rate);
            $this->entityManager->flush();
        }
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
