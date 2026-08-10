<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ImageOptimizer
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(GdDriver::class);
    }

    /**
     * Resize without cropping or upscaling, then save as a clear, compressed WebP image.
     */
    public function store(
        UploadedFile $file,
        string $relativeDirectory,
        string $prefix,
        int $maxWidth,
        int $maxHeight,
        int $quality = 84,
    ): string {
        $directory = public_path(trim($relativeDirectory, '/\\'));

        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Could not create the image directory.');
        }

        $filename = $prefix.'_'.now()->format('YmdHisv').'_'.Str::random(12).'.webp';
        $image = $this->manager->decodePath($file->getRealPath());

        // scaleDown keeps the original aspect ratio and never enlarges a small image.
        $image->scaleDown(width: $maxWidth, height: $maxHeight);
        $image->encodeUsingFormat(Format::WEBP, quality: $quality)
            ->save($directory.DIRECTORY_SEPARATOR.$filename);

        return $filename;
    }

    public function delete(?string $filename, string $relativeDirectory): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path(trim($relativeDirectory, '/\\').DIRECTORY_SEPARATOR.basename($filename));
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
