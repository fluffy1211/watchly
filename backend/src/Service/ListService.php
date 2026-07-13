<?php

namespace App\Service;

use App\Entity\MovieList;
use App\Entity\User;

class ListService
{
    private const VALID_VISIBILITIES = [
        MovieList::VISIBILITY_PUBLIC,
        MovieList::VISIBILITY_PRIVATE,
    ];

    public function setTitle(MovieList $list, string $title): void
    {
        $title = trim($title);
        if ($title === '') {
            throw new \InvalidArgumentException('Title is required.');
        }
        if (strlen($title) > 255) {
            throw new \InvalidArgumentException('Title must be at most 255 characters.');
        }
        $list->setTitle($title);
    }

    public function setVisibility(MovieList $list, string $visibility): void
    {
        if (!in_array($visibility, self::VALID_VISIBILITIES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid visibility "%s".', $visibility));
        }
        $list->setVisibility($visibility);
    }

    public function canView(MovieList $list, ?User $requester): bool
    {
        return $list->isPublic() || $requester === $list->getOwner();
    }
}
