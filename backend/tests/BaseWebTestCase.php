<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    protected $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em     = static::getContainer()->get(EntityManagerInterface::class);
        $this->truncateAll();
    }

    private function truncateAll(): void
    {
        $conn = $this->em->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['review', 'user_collection', 'film_genre', 'film', 'genre', 'utilisateur'] as $table) {
            $conn->executeStatement('DELETE FROM ' . $table);
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }
}
