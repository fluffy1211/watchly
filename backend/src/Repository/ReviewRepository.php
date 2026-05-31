<?php

namespace App\Repository;

use App\Entity\Film;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByFilmWithUser(Film $film): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->addSelect('u')
            ->where('r.film = :film')
            ->setParameter('film', $film)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndFilm(User $user, Film $film): ?Review
    {
        return $this->findOneBy(['user' => $user, 'film' => $film]);
    }
}
