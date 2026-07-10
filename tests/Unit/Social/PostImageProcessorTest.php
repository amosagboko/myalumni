<?php

namespace Tests\Unit\Social;

use App\Services\Social\PostImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostImageProcessorTest extends TestCase
{
    public function test_it_creates_display_and_thumb_variants(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.jpg', 2400, 1800);
        $processor = app(PostImageProcessor::class);

        $result = $processor->process($file);

        $this->assertSame('image', $result['media_type']);
        $this->assertNotEmpty($result['media_path']);
        $this->assertNotEmpty($result['thumb_path']);
        $this->assertStringEndsWith('_display.jpg', $result['media_path']);
        $this->assertStringEndsWith('_thumb.jpg', $result['thumb_path']);
        $this->assertLessThanOrEqual(1920, $result['width']);
        $this->assertLessThanOrEqual(1920, $result['height']);

        Storage::disk('public')->assertExists($result['media_path']);
        Storage::disk('public')->assertExists($result['thumb_path']);
    }
}
