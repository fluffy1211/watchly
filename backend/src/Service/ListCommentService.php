<?php

namespace App\Service;

class ListCommentService
{
    public function validateContent(string $content): string
    {
        $content = trim($content);

        if (strlen($content) < 1) {
            throw new \InvalidArgumentException('Comment cannot be empty.');
        }
        if (strlen($content) > 2000) {
            throw new \InvalidArgumentException('Comment must be at most 2000 characters.');
        }

        return $content;
    }
}
