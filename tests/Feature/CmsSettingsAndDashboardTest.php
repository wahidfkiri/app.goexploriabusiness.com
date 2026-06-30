<?php

namespace Tests\Feature;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vendor\Cms\Models\Setting;

class CmsSettingsAndDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected array $connectionsToTransact = ['mysql', 'cms'];

    public function test_cms_settings_store_contact_email_under_general_email_key(): void
    {
        $user = User::factory()->create();

        $etablissement = Etablissement::query()->create([
            'name' => 'Config test ' . Str::upper(Str::random(6)),
            'slug' => 'config-test-' . Str::lower(Str::random(6)),
            'ville' => 'Paris',
            'user_id' => $user->id,
            'adresse' => '10 Rue du Test',
            'zip_code' => '75001',
            'phone' => '+33 1 00 00 00 00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('cms.admin.settings.update', [
            'etablissementId' => $etablissement->id,
        ]), [
            'email' => 'contact@example.com',
            'notification_email' => 'notify@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertSame(
            'contact@example.com',
            Setting::query()
                ->where('etablissement_id', $etablissement->id)
                ->where('group', 'general')
                ->where('key', 'email')
                ->value('value')
        );

        $this->assertSame(
            'notify@example.com',
            Setting::query()
                ->where('etablissement_id', $etablissement->id)
                ->where('group', 'general')
                ->where('key', 'notification_email')
                ->value('value')
        );

        $this->assertNull(
            Setting::query()
                ->where('etablissement_id', $etablissement->id)
                ->where('group', 'general')
                ->where('key', 'contact_email')
                ->first()
        );
    }

    public function test_legacy_dashboard_url_redirects_to_slugged_dashboard_url(): void
    {
        $user = User::factory()->create();

        $etablissement = Etablissement::query()->create([
            'name' => 'Hotel du Centre',
            'slug' => 'hotel-du-centre',
            'ville' => 'Lyon',
            'user_id' => $user->id,
            'adresse' => '25 Rue Centrale',
            'zip_code' => '69001',
            'phone' => '+33 4 00 00 00 00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('cms.admin.dashboard.legacy', [
            'etablissementId' => $etablissement->id,
            'section' => 'media',
        ]));

        $response->assertRedirect(route('cms.admin.dashboard', [
            'etablissementId' => $etablissement->id,
            'slug' => 'hotel-du-centre',
            'section' => 'media',
        ]));
    }
}