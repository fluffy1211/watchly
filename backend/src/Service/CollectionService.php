<?php

namespace App\Service;

use App\Entity\UserCollection;

class CollectionService
{
    private const VALID_STATUSES = [
        UserCollection::STATUS_WATCHLIST,
        UserCollection::STATUS_WATCHED,
    ];

    public function setStatus(UserCollection $uc, string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid status "%s".', $status));
        }

        $uc->setStatus($status);

        if ($status === UserCollection::STATUS_WATCHLIST) {
            $uc->setIsFavorite(false);
            $uc->setRating(null);
            $uc->setWatchedAt(null);
        } elseif ($status === UserCollection::STATUS_WATCHED && $uc->getWatchedAt() === null) {
            $uc->setWatchedAt(new \DateTimeImmutable());
        }
    }

    public function setFavorite(UserCollection $uc, bool $value): void
    {
        if ($value && !$uc->isWatched()) {
            throw new \LogicException('Cannot set favorite on a non-watched entry.');
        }
        $uc->setIsFavorite($value);
    }

    public function setRating(UserCollection $uc, ?int $rating): void
    {
        if (!$uc->isWatched()) {
            throw new \LogicException('Cannot rate a non-watched entry.');
        }
        if ($rating !== null && ($rating < 1 || $rating > 5)) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5.');
        }
        $uc->setRating($rating);
    }
}
