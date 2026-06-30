<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Vendor\Cms\Services\CdnMediaService;

class CdnMediaServiceTest extends TestCase
{
    public function test_it_uploads_media_successfully(): void
    {
        config(['app.url' => 'https://admin.goexploriabusiness.com']);
        Storage::fake('public');

        $service = new CdnMediaService();
        $file = UploadedFile::fake()->image('demo.png', 120, 120);

        $result = $service->upload($file, 'cms/media/11/gallery');

        $this->assertTrue($result['success']);
        $this->assertSame('https://admin.goexploriabusiness.com/storage/cms/media/11/gallery/demo.png', $result['url']);
        $this->assertSame('cms/media/11/gallery/demo.png', $result['path']);
        Storage::disk('public')->assertExists($result['path']);
    }
}
