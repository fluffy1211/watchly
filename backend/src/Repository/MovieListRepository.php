<?php

namespace App\Repository;

use App\Entity\MovieList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MovieListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieList::class);
    }

    /** @return MovieList[] */
    public function searchPublic(?string $query): array
    {
        $qb = $this->createQueryBuilder('l')
            ->join('l.owner', 'o')
            ->addSelect('o')
            ->where('l.visibility = :visibility')
            ->setParameter('visibility', MovieList::VISIBILITY_PUBLIC)
            ->orderBy('l.createdAt', 'DESC');

        if ($query !== null && $query !== '') {
            $qb->andWhere('l.title LIKE :query OR o.username LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /** @return MovieList[] */
    public function findPublicByOwnerUsername(string $username): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.owner', 'o')
            ->where('o.username = :username')
            ->andWhere('l.visibility = :visibility')
            ->setParameter('username', $username)
            ->setParameter('visibility', MovieList::VISIBILITY_PUBLIC)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return MovieList[] */
    public function findAllByOwnerUsername(string $username): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.owner', 'o')
            ->where('o.username = :username')
            ->setParameter('username', $username)
            ->orderBy('l.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
