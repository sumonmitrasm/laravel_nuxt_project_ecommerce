<?php

namespace Tests\Unit;

use App\Support\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    public function test_it_proportionally_resizes_and_converts_an_image_to_webp(): void
    {
        $optimizer = app(ImageOptimizer::class);
        $filename = $optimizer->store(
            UploadedFile::fake()->image('large-product.jpg', 2400, 1600),
            'admin/test-optimized-images',
            'product',
            1200,
            1200,
            84,
        );

        $path = public_path('admin/test-optimized-images/'.$filename);

        try {
            $this->assertStringEndsWith('.webp', $filename);
            $this->assertFileExists($path);

            [$width, $height] = getimagesize($path);
            $this->assertSame(1200, $width);
            $this->assertSame(800, $height);
            $this->assertSame('image/webp', mime_content_type($path));
        } finally {
            $optimizer->delete($filename, 'admin/test-optimized-images');
            @rmdir(public_path('admin/test-optimized-images'));
        }
    }
}
