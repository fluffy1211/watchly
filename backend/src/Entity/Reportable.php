<?php

namespace App\Entity;

/**
 * Content that can be the target of a moderation Report.
 *
 * Implemented by ListComment and Review. The interface is deliberately minimal:
 * ReportService only needs the author to enforce the "cannot report your own
 * content" rule; presentation of a report (excerpt, context) lives in
 * ReportViewNormalizer, not here.
 */
interface Reportable
{
    public function getId(): ?int;

    public function getAuthor(): ?User;
}
