<?php

namespace App\Support\Images;

use RuntimeException;

/**
 * تحويل صور PNG/JPG الثقيلة إلى WebP بعرض أقصى، مع نسخة صغيرة للجوال
 * ({name}-640.webp) تُستخدم في srcset. الأصل يبقى كما هو بجانبها.
 */
final class WebpOptimizer
{
    public const SMALL_WIDTH = 640;

    /**
     * @return string مسار WebP الرئيسي (نسبي لـ public) بنفس مجلد الأصل.
     */
    public static function optimize(string $relativePath, int $maxWidth = 1400, int $quality = 80): string
    {
        $source = public_path($relativePath);
        $image = self::load($source);

        $base = preg_replace('/\.[a-z0-9]+$/i', '', $relativePath);

        self::save($image, public_path($base.'.webp'), $maxWidth, $quality);
        self::save($image, public_path($base.'-'.self::SMALL_WIDTH.'.webp'), self::SMALL_WIDTH, $quality);

        unset($image); // imagedestroy() بلا أثر من PHP 8.0 ومهمل في 8.5

        return $base.'.webp';
    }

    private static function load(string $path): \GdImage
    {
        $type = @getimagesize($path)[2] ?? null;

        $image = match ($type) {
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => false,
        };

        if (! $image) {
            throw new RuntimeException("Unsupported or unreadable image: {$path}");
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    private static function save(\GdImage $image, string $destination, int $maxWidth, int $quality): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * $maxWidth / $width);
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
        } else {
            $resized = $image;
        }

        if (! imagewebp($resized, $destination, $quality)) {
            throw new RuntimeException("Could not write {$destination}");
        }

        if ($resized !== $image) {
            unset($resized); // imagedestroy() بلا أثر من PHP 8.0 ومهمل في 8.5
        }
    }
}
