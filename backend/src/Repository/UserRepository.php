<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /** @return array<int, array{total: int, watched: int, favorites: int}> keyed by user id */
    public function findAllWithStats(): array
    {
        $rows = $this->getEntityManager()->createQuery(
            'SELECT u.id,
                    COUNT(uc.id)                                              AS total,
                    SUM(CASE WHEN uc.status = :watched   THEN 1 ELSE 0 END)  AS watched,
                    SUM(CASE WHEN uc.isFavorite = true   THEN 1 ELSE 0 END)  AS favorites
             FROM App\Entity\User u
             LEFT JOIN App\Entity\UserCollection uc WITH uc.user = u
             GROUP BY u.id'
        )
        ->setParameter('watched', 'WATCHED')
        ->getArrayResult();

        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['id']] = [
                'total'     => (int) $row['total'],
                'watched'   => (int) $row['watched'],
                'favorites' => (int) $row['favorites'],
            ];
        }

        return $byId;
    }
}
