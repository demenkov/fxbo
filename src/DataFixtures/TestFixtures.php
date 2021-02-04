<?php

namespace FXBO\DataFixtures;

use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use FXBO\Entity\Rate;

class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ([
            [
                'date' => '1970-01-01',
                'base' => 'XXX',
                'quote' => 'YYY',
                'price' => '1',
                'provider' => 'test'
            ],
            [
                'date' => '1970-01-01',
                'base' => 'YYY',
                'quote' => 'KKK',
                'price' => '2',
                'provider' => 'test'
            ],
            [
                'date' => '1970-01-01',
                'base' => 'FFF',
                'quote' => 'KKK',
                'price' => '0.6',
                'provider' => 'test'
            ],
            [
                'date' => '1970-01-01',
                'base' => 'GGG',
                'quote' => 'FFF',
                'price' => '0.4',
                'provider' => 'test'
            ],
            [
                'date' => '1970-01-01',
                'base' => 'ZZZ',
                'quote' => 'ABC',
                'price' => '1',
                'provider' => 'test'
            ]
        ] as $rate) {
            $rate['date'] = new DateTimeImmutable($rate['date']);
            $manager->persist(new Rate(...array_values($rate)));
        }

        $manager->flush();
    }
}
