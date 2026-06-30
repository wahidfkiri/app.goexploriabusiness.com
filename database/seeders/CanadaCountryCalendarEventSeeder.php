<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Vendor\Etablissement\Models\CountryCalendarEvent;

class CanadaCountryCalendarEventSeeder extends Seeder
{
    public function run(): void
    {
        $canada = Country::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('iso2', 'CA')
                    ->orWhere('code', 'CAN')
                    ->orWhere('name', 'Canada');
            })
            ->first();

        if (!$canada) {
            $this->command?->warn('Canada introuvable. Verifiez la table countries avant de lancer ce seeder.');

            return;
        }

        $events = [
            [
                'slug' => 'jour-de-l-an',
                'name' => "Jour de l'An",
                'event_type' => 'holiday',
                'description' => "Jour ferie federal du Nouvel An au Canada.",
                'recurrence_type' => 'fixed_date',
                'month' => 1,
                'day' => 1,
                'color' => '#dc2626',
            ],
            [
                'slug' => 'vendredi-saint',
                'name' => 'Vendredi saint',
                'event_type' => 'holiday',
                'description' => 'Jour ferie calcule deux jours avant Paques.',
                'recurrence_type' => 'easter_offset',
                'offset_days' => -2,
                'color' => '#be123c',
            ],
            [
                'slug' => 'fete-de-la-reine',
                'name' => 'Fete de la Reine',
                'event_type' => 'holiday',
                'description' => 'Lundi precedant ou correspondant au 24 mai.',
                'recurrence_type' => 'weekday_on_or_before',
                'month' => 5,
                'day' => 24,
                'weekday' => 1,
                'color' => '#7c3aed',
            ],
            [
                'slug' => 'fete-du-canada',
                'name' => 'Fete du Canada',
                'event_type' => 'holiday',
                'description' => 'Jour de celebration nationale du Canada.',
                'recurrence_type' => 'fixed_date',
                'month' => 7,
                'day' => 1,
                'color' => '#2563eb',
            ],
            [
                'slug' => 'fete-du-travail',
                'name' => 'Fete du Travail',
                'event_type' => 'holiday',
                'description' => 'Premier lundi de septembre.',
                'recurrence_type' => 'nth_weekday',
                'month' => 9,
                'weekday' => 1,
                'nth_occurrence' => 1,
                'color' => '#0f766e',
            ],
            [
                'slug' => 'verite-et-reconciliation',
                'name' => 'Journee nationale de la verite et de la reconciliation',
                'event_type' => 'commemoration',
                'description' => 'Journee de memoire et de reconnaissance observee le 30 septembre.',
                'recurrence_type' => 'fixed_date',
                'month' => 9,
                'day' => 30,
                'color' => '#b45309',
            ],
            [
                'slug' => 'action-de-grace',
                'name' => 'Action de grace',
                'event_type' => 'holiday',
                'description' => "Deuxieme lundi d'octobre.",
                'recurrence_type' => 'nth_weekday',
                'month' => 10,
                'weekday' => 1,
                'nth_occurrence' => 2,
                'color' => '#d97706',
            ],
            [
                'slug' => 'jour-du-souvenir',
                'name' => 'Jour du Souvenir',
                'event_type' => 'commemoration',
                'description' => 'Journee de commemoration observee le 11 novembre.',
                'recurrence_type' => 'fixed_date',
                'month' => 11,
                'day' => 11,
                'color' => '#1d4ed8',
            ],
            [
                'slug' => 'noel',
                'name' => 'Noel',
                'event_type' => 'holiday',
                'description' => 'Jour ferie de Noel.',
                'recurrence_type' => 'fixed_date',
                'month' => 12,
                'day' => 25,
                'color' => '#15803d',
            ],
            [
                'slug' => 'lendemain-de-noel',
                'name' => 'Lendemain de Noel',
                'event_type' => 'holiday',
                'description' => 'Jour ferie observe le 26 decembre.',
                'recurrence_type' => 'fixed_date',
                'month' => 12,
                'day' => 26,
                'color' => '#0f766e',
            ],
        ];

        foreach ($events as $event) {
            CountryCalendarEvent::query()->updateOrCreate(
                [
                    'country_id' => $canada->id,
                    'slug' => $event['slug'],
                ],
                array_merge($event, [
                    'country_id' => $canada->id,
                    'is_all_day' => true,
                    'is_active' => true,
                    'duration_days' => 1,
                ])
            );
        }

        $this->command?->info('Seeder CanadaCountryCalendarEventSeeder execute pour le pays Canada.');
    }
}
