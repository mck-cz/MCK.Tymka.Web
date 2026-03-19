<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Process and store an uploaded image with resizing and compression.
     *
     * @param UploadedFile $file
     * @param string $directory Storage subdirectory (e.g. 'albums/uuid', 'avatars')
     * @param int $maxWidth Max width in pixels
     * @param int $maxHeight Max height in pixels
     * @param int $quality JPEG quality (1-100)
     * @return string Stored file path relative to disk root
     */
    public static function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1920,
        int $maxHeight = 1920,
        int $quality = 80,
    ): string {
        $image = Image::read($file->getRealPath());

        // Scale down if larger than max dimensions, preserving aspect ratio
        $image->scaleDown(width: $maxWidth, height: $maxHeight);

        // Encode as JPEG for photos (significant size reduction)
        $encoded = $image->toJpeg($quality);

        $filename = uniqid() . '.jpg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
