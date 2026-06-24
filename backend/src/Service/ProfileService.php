<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfileService
{
    private const MAX_SIZE = 256;
    private const QUALITY = 80;
    private const AVATAR_DIR = 'uploads/avatars';
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(private string $uploadsDir) {}

    public function processAvatar(UploadedFile $file, int $userId): string
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new \InvalidArgumentException('Type de fichier non supporté. Utilisez JPEG, PNG, WebP ou GIF.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Le fichier ne doit pas dépasser 5 Mo.');
        }

        $source = @imagecreatefromstring(file_get_contents($file->getPathname()));
        if (!$source) {
            throw new \RuntimeException('Impossible de lire l\'image.');
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        $ratio = min(self::MAX_SIZE / $origW, self::MAX_SIZE / $origH, 1.0);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($source);

        $dir = $this->uploadsDir . '/' . self::AVATAR_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $userId . '.webp';
        $destPath = $dir . '/' . $filename;

        imagewebp($resized, $destPath, self::QUALITY);
        imagedestroy($resized);

        return self::AVATAR_DIR . '/' . $filename;
    }

    public function deleteAvatar(int $userId): void
    {
        $path = $this->uploadsDir . '/' . self::AVATAR_DIR . '/' . $userId . '.webp';
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
