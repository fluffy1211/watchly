<?php

namespace App\Service;

class CommentReportService
{
    public function validateReason(?string $reason): ?string
    {
        $reason = trim((string) $reason);

        if ($reason === '') {
            return null;
        }
        if (strlen($reason) > 500) {
            throw new \InvalidArgumentException('Reason must be at most 500 characters.');
        }

        return $reason;
    }
}
