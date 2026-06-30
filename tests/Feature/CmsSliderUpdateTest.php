<?php

namespace Tests\Feature;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vendor\Cms\Models\Media;
use Vendor\Cms\Models\Setting;

class CmsSliderUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected array $connectionsToTransact = ['mysql', 'cms'];

    public function test_setting_slider_can_be_updated_with_media_library_asset(): void
    {
        $user = User::factory()->create();

        $etablissement = Etablissement::query()->create([
            'name' => 'Slider test ' . Str::upper(Str::random(6)),
            'ville' => 'Paris',
            'user_id' => $user->id,
            'adresse' => '10 Rue du Test',
            'zip_code' => '75001',
            'phone' => '+33 1 00 00 00 00',
            'is_active' => true,
        ]);

        $media = Media::query()->create([
            'etablissement_id' => $etablissement->id,
            'user_id' => $user->id,
            'name' => 'Image de bibliothèque',
            'original_name' => 'hotel-cover.png',
            'filename' => 'hotel-cover.png',
            'path' => 'https://admin.goexploriabusiness.com/storage/media/test/hotel-cover.png',
            'size' => 2048,
            'mime_type' => 'image/png',
            'extension' => 'png',
            'type' => 'image',
            'folder' => 'slider',
            'is_public' => true,
        ]);

        $setting = Setting::query()->create([
            'etablissement_id' => $etablissement->id,
            'group' => 'slider',
            'key' => 'slider_item_test_' . Str::lower(Str::random(8)),
            'value' => [
                'type' => 'image',
                'url' => 'https://admin.goexploriabusiness.com/storage/sliders/old-slide.png',
                'video_url' => '',
                'title' => 'Ancien titre',
                'subtitle' => 'Ancien sous-titre',
                'button_text' => 'Ancien bouton',
                'button_link' => 'https://example.com/old',
                'is_active' => true,
            ],
            'type' => 'json',
            'order' => 1,
        ]);

        $response = $this->actingAs($user)->putJson(route('cms.admin.slider.update', [
            'etablissementId' => $etablissement->id,
            'id' => $setting->id,
        ]), [
            'type' => 'image',
            'source' => 'setting',
            'slide_source_kind' => 'setting',
            'asset_source' => 'media',
            'media_id' => $media->id,
            'title' => 'Nouveau titre',
            'subtitle' => 'Nouveau sous-titre',
            'button_text' => 'Reserver',
            'button_link' => 'https://example.com/book',
            'is_active' => true,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $setting->id,
                'source' => 'setting',
                'type' => 'image',
                'url' => $media->url,
                'asset_source' => 'media',
                'media_id' => $media->id,
                'title' => 'Nouveau titre',
                'subtitle' => 'Nouveau sous-titre',
                'button_text' => 'Reserver',
                'button_link' => 'https://example.com/book',
            ],
        ]);

        $updatedSetting = $setting->fresh();
        $updatedValue = json_decode((string) $updatedSetting->getRawOriginal('value'), true);

        $this->assertSame($media->url, $updatedValue['url'] ?? null);
        $this->assertSame('media', $updatedValue['asset_source'] ?? null);
        $this->assertSame($media->id, $updatedValue['media_id'] ?? null);
        $this->assertSame('Nouveau titre', $updatedValue['title'] ?? null);
        $this->assertSame('Nouveau sous-titre', $updatedValue['subtitle'] ?? null);
        $this->assertSame('Reserver', $updatedValue['button_text'] ?? null);
        $this->assertSame('https://example.com/book', $updatedValue['button_link'] ?? null);
    }
}
