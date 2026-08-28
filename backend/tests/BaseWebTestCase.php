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
        static::getContainer()->get('cache.rate_limiter')->clear();
    }

    private function truncateAll(): void
    {
        $this->em->clear();
        $conn = $this->em->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['report', 'list_comment', 'list_film', 'movie_list', 'review', 'user_collection', 'film_genre', 'film', 'genre', 'utilisateur'] as $table) {
            $conn->executeStatement("TRUNCATE TABLE {$table}");
        }
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }
}
