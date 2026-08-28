<?php

namespace App\Service;

use App\Entity\CommentReport;
use App\Entity\ListComment;
use App\Entity\Report;
use App\Entity\Reportable;
use App\Entity\Review;
use App\Entity\ReviewReport;
use App\Entity\User;
use App\Enum\ReportAction;
use App\Exception\DuplicateReportException;
use App\Exception\InvalidReportReasonException;
use App\Exception\SelfReportException;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Files and resolves moderation reports for any Reportable content.
 */
class ReportService
{
    private const MAX_REASON_LENGTH = 500;

    public function __construct(
        private readonly ReportRepository $reports,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * @throws SelfReportException          reporter is the content's author
     * @throws DuplicateReportException     reporter already reported this content
     * @throws InvalidReportReasonException reason exceeds the length limit
     */
    public function file(User $reporter, Reportable $target, ?string $reason): Report
    {
        if ($target->getAuthor() === $reporter) {
            throw new SelfReportException('Cannot report your own content.');
        }

        if ($this->reports->findOneByTargetAndReporter($target, $reporter) !== null) {
            throw new DuplicateReportException('Content already reported.');
        }

        $report = $this->newReportFor($target)
            ->setReporter($reporter)
            ->setReason($this->validateReason($reason));

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }

    public function resolve(Report $report, ReportAction $action): void
    {
        if ($action === ReportAction::Delete) {
            $target = $report->getTarget();
            if ($target !== null) {
                // DB cascade clears this report and any siblings on the same target.
                $this->em->remove($target);
            }
        } else {
            $this->em->remove($report);
        }

        $this->em->flush();
    }

    private function newReportFor(Reportable $target): Report
    {
        return match (true) {
            $target instanceof ListComment => (new CommentReport())->setComment($target),
            $target instanceof Review      => (new ReviewReport())->setReview($target),
            default => throw new \InvalidArgumentException(sprintf('Unsupported reportable: %s', $target::class)),
        };
    }

    private function validateReason(?string $reason): ?string
    {
        $reason = trim((string) $reason);

        if ($reason === '') {
            return null;
        }

        if (strlen($reason) > self::MAX_REASON_LENGTH) {
            throw new InvalidReportReasonException(
                sprintf('Reason must be at most %d characters.', self::MAX_REASON_LENGTH)
            );
        }

        return $reason;
    }
}
