<?php

namespace Vendor\LocationDataEngine\DTO;

readonly class WebsiteEnrichmentData
{
    public function __construct(
        public array $emails,
        public array $socialLinks,
        public array $bookingLinks,
        public ?string $contactPage,
        public array $raw,
    ) {
    }

    public function toArray(): array
    {
        return [
            'emails' => array_values(array_unique($this->emails)),
            'social_links' => $this->socialLinks,
            'booking_links' => array_values(array_unique($this->bookingLinks)),
            'contact_page' => $this->contactPage,
            'raw' => $this->raw,
        ];
    }
}
