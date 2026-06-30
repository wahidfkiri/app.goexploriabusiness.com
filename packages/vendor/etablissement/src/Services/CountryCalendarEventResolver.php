<?php

namespace Vendor\Etablissement\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Vendor\Etablissement\Models\CountryCalendarEvent;

class CountryCalendarEventResolver
{
    public function resolveForCountry(int $countryId, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd): Collection
    {
        $events = CountryCalendarEvent::query()
            ->with('country:id,name')
            ->active()
            ->where('country_id', $countryId)
            ->orderBy('name')
            ->get();

        if ($events->isEmpty()) {
            return collect();
        }

        $years = range($rangeStart->year, $rangeEnd->year);

        return $events
            ->flatMap(function (CountryCalendarEvent $event) use ($years, $rangeStart, $rangeEnd) {
                if ($event->recurrence_type === 'one_time') {
                    $occurrence = $this->resolveOneTimeOccurrence($event);

                    return $occurrence && $this->overlapsRange($occurrence['starts_at'], $occurrence['ends_at'], $rangeStart, $rangeEnd)
                        ? [$occurrence]
                        : [];
                }

                return collect($years)
                    ->map(fn (int $year) => $this->resolveRecurringOccurrence($event, $year))
                    ->filter(fn (?array $occurrence) => $occurrence !== null)
                    ->filter(fn (array $occurrence) => $this->overlapsRange($occurrence['starts_at'], $occurrence['ends_at'], $rangeStart, $rangeEnd))
                    ->values();
            })
            ->sortBy(fn (array $occurrence) => $occurrence['starts_at']->format('Y-m-d H:i:s'))
            ->values();
    }

    private function resolveOneTimeOccurrence(CountryCalendarEvent $event): ?array
    {
        if (!$event->specific_start_date) {
            return null;
        }

        $start = CarbonImmutable::parse($event->specific_start_date)->startOfDay();
        $end = $event->specific_end_date
            ? CarbonImmutable::parse($event->specific_end_date)->endOfDay()
            : $this->resolveEndDate($start, $event->duration_days, true);

        return $this->buildOccurrence($event, $start, $end);
    }

    private function resolveRecurringOccurrence(CountryCalendarEvent $event, int $year): ?array
    {
        $start = match ($event->recurrence_type) {
            'fixed_date' => $this->resolveFixedDate($event, $year),
            'nth_weekday' => $this->resolveNthWeekday($event, $year),
            'weekday_on_or_before' => $this->resolveWeekdayOnOrBefore($event, $year),
            'easter_offset' => $this->resolveEasterOffset($event, $year),
            default => null,
        };

        if (!$start) {
            return null;
        }

        $end = $this->resolveEndDate($start, $event->duration_days, (bool) $event->is_all_day);

        return $this->buildOccurrence($event, $start, $end);
    }

    private function resolveFixedDate(CountryCalendarEvent $event, int $year): ?CarbonImmutable
    {
        if (!$event->month || !$event->day) {
            return null;
        }

        return CarbonImmutable::create($year, $event->month, $event->day, 0, 0, 0);
    }

    private function resolveNthWeekday(CountryCalendarEvent $event, int $year): ?CarbonImmutable
    {
        if (!$event->month || !$event->weekday || !$event->nth_occurrence) {
            return null;
        }

        $date = CarbonImmutable::create($year, $event->month, 1, 0, 0, 0);

        while ($date->dayOfWeekIso !== (int) $event->weekday) {
            $date = $date->addDay();
        }

        $date = $date->addWeeks(((int) $event->nth_occurrence) - 1);

        return $date->month === (int) $event->month ? $date : null;
    }

    private function resolveWeekdayOnOrBefore(CountryCalendarEvent $event, int $year): ?CarbonImmutable
    {
        if (!$event->month || !$event->day || !$event->weekday) {
            return null;
        }

        $date = CarbonImmutable::create($year, $event->month, $event->day, 0, 0, 0);

        while ($date->dayOfWeekIso !== (int) $event->weekday) {
            $date = $date->subDay();
        }

        return $date;
    }

    private function resolveEasterOffset(CountryCalendarEvent $event, int $year): ?CarbonImmutable
    {
        if ($event->offset_days === null) {
            return null;
        }

        $easterSunday = CarbonImmutable::create($year, 3, 21, 0, 0, 0)
            ->addDays(easter_days($year));

        return $easterSunday->addDays((int) $event->offset_days);
    }

    private function resolveEndDate(CarbonImmutable $start, ?int $durationDays, bool $allDay): CarbonImmutable
    {
        $duration = max(1, (int) ($durationDays ?: 1));
        $end = $start->addDays($duration - 1);

        return $allDay ? $end->endOfDay() : $end->addHour();
    }

    private function buildOccurrence(CountryCalendarEvent $event, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $slug = $event->slug ?: Str::slug($event->name);

        return [
            'id' => sprintf('country-event-%d-%s-%d', $event->id, $slug, $start->year),
            'country_calendar_event_id' => $event->id,
            'country_id' => $event->country_id,
            'country_name' => $event->country?->name,
            'name' => $event->name,
            'title' => $event->name,
            'description' => $event->description,
            'event_type' => $event->event_type,
            'event_type_label' => $event->event_type_label,
            'recurrence_type' => $event->recurrence_type,
            'source_type' => 'country_calendar_event',
            'starts_at' => $start,
            'ends_at' => $end,
            'all_day' => (bool) $event->is_all_day,
            'color' => $event->resolved_color,
        ];
    }

    private function overlapsRange(
        CarbonImmutable $eventStart,
        CarbonImmutable $eventEnd,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd
    ): bool {
        return $eventStart->lessThanOrEqualTo($rangeEnd) && $eventEnd->greaterThanOrEqualTo($rangeStart);
    }
}
