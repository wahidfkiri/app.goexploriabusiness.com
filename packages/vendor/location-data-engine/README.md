# Location Data Engine

Reusable Laravel package to scan businesses, places, locations, destinations and listings from Google Places API.

## Features

- Region scan dashboard
- Google Places text search, nearby search and place details
- Queue jobs and progress tracking
- Website enrichment for emails and social links
- Image download pipeline
- AJAX admin interface
- CSV and Excel exports

## Routes

- `/admin/location-data-engine`
- `/admin/location-data-engine/results`
- `/admin/location-data-engine/logs`

## Command

```bash
php artisan locations:scan --country=Canada --province=Quebec --category=hotels --with-enrichment --with-images
```
