<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vendor\Etablissement\Services\EtablissementImportService;

class EtablissementImportSlugTest extends TestCase
{
    use DatabaseTransactions;

    public function test_import_process_generates_slug_from_name(): void
    {
        Storage::fake('local');

        $province = Province::query()->whereNotNull('country_id')->firstOrFail();
        $country = Country::query()->findOrFail($province->country_id);
        $activity = Activity::query()->firstOrFail();
        $name = 'Restaurant Démo Import ' . Str::upper(Str::random(6));
        $expectedSlug = Str::slug($name);

        $csv = implode("\n", [
            'Société,Adresse,Ville,Code.Postal,Email,Site Internet,Tel.n°1',
            sprintf('"%s","15 Rue des Fleurs","Montréal","H2X 1Y4","demo-import@example.com","https://example.test","514-555-1122"', $name),
        ]);

        $file = UploadedFile::fake()->createWithContent('etablissements.csv', $csv);

        $service = app(EtablissementImportService::class);
        $session = $service->createSession($file, [
            'continent_id' => $country->continent_id,
            'country_id' => $country->id,
            'province_id' => $province->id,
            'region_id' => null,
            'ville_id' => null,
            'secteur_id' => null,
            'primary_activity_id' => $activity->id,
            'other_activity_label' => null,
            'selected_labels' => [
                'continent' => null,
                'country' => $country->name,
                'province' => $province->name,
                'region' => null,
                'ville' => null,
                'secteur' => null,
            ],
        ]);

        $result = $service->processSession($session['session_id'], 50);

        $this->assertTrue($result['done']);
        $this->assertDatabaseHas('etablissements', [
            'name' => $name,
            'slug' => $expectedSlug,
            'country_id' => $country->id,
            'province_id' => $province->id,
        ]);
    }
}
