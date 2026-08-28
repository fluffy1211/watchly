<?php

namespace App\Repository;

use App\Entity\CommentReport;
use App\Entity\ListComment;
use App\Entity\Report;
use App\Entity\Reportable;
use App\Entity\Review;
use App\Entity\ReviewReport;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Report>
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * Every open report, newest first, across all target types.
     *
     * @return Report[]
     */
    public function findAllForModeration(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * The existing report by this reporter on this target, if any.
     *
     * STI can't filter the root entity by a subclass-only field, so this
     * dispatches to the concrete subclass repository.
     */
    public function findOneByTargetAndReporter(Reportable $target, User $reporter): ?Report
    {
        [$class, $field] = match (true) {
            $target instanceof ListComment => [CommentReport::class, 'comment'],
            $target instanceof Review      => [ReviewReport::class, 'review'],
            default => throw new \InvalidArgumentException(sprintf('Unsupported reportable: %s', $target::class)),
        };

        return $this->getEntityManager()
            ->getRepository($class)
            ->findOneBy([$field => $target, 'reporter' => $reporter]);
    }
}
