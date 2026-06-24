<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCollection::class);
    }

    /** @return UserCollection[] */
    public function findByUserWithFilters(User $user, ?string $status, ?bool $favorite): array
    {
        $qb = $this->createQueryBuilder('uc')
            ->join('uc.film', 'f')
            ->leftJoin('f.genres', 'g')
            ->addSelect('f', 'g')
            ->where('uc.user = :user')
            ->setParameter('user', $user)
            ->orderBy('uc.addedAt', 'DESC');

        if ($status !== null) {
            $qb->andWhere('uc.status = :status')->setParameter('status', $status);
        }

        if ($favorite === true) {
            $qb->andWhere('uc.isFavorite = true');
        }

        return $qb->getQuery()->getResult();
    }

    /** @return UserCollection[] */
    public function findWatchedByUser(User $user): array
    {
        return $this->createQueryBuilder('uc')
            ->join('uc.film', 'f')
            ->leftJoin('f.genres', 'g')
            ->addSelect('f', 'g')
            ->where('uc.user = :user')
            ->andWhere('uc.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', UserCollection::STATUS_WATCHED)
            ->orderBy('uc.watchedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
