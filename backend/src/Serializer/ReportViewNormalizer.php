<?php

namespace App\Serializer;

use App\Entity\CommentReport;
use App\Entity\ListComment;
use App\Entity\Report;
use App\Entity\Reportable;
use App\Entity\Review;
use App\Entity\ReviewReport;

/**
 * Shapes a Report into the flat payload the admin moderation queue renders.
 * The per-target differences (comment→list title, review→film title) are the
 * only thing that varies, and they resolve here with a match on the subclass.
 */
class ReportViewNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public function normalize(Report $report): array
    {
        $target = $report->getTarget();

        return [
            'id'         => $report->getId(),
            'type'       => $report->getType(),
            'reason'     => $report->getReason(),
            'created_at' => $report->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'reporter'   => ['username' => $report->getReporter()?->getUsername()],
            'target'     => [
                'id'      => $target?->getId(),
                'excerpt' => $this->excerpt($target),
                'author'  => ['username' => $target?->getAuthor()?->getUsername()],
                'context' => $this->context($report),
            ],
        ];
    }

    private function excerpt(?Reportable $target): ?string
    {
        return match (true) {
            $target instanceof ListComment => $target->getContent(),
            $target instanceof Review      => $target->getContent(),
            default => null,
        };
    }

    private function context(Report $report): ?string
    {
        return match (true) {
            $report instanceof CommentReport => $report->getComment()?->getList()?->getTitle(),
            $report instanceof ReviewReport  => $report->getReview()?->getFilm()?->getTitle(),
            default => null,
        };
    }
}
