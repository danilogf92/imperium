<?php

namespace App\Support\Dashboard;

final readonly class DashboardFilters
{
    public function __construct(
        public array $companies = [],
        public array $years = [],
        public array $states = [],
        public array $classifications = [],
        public array $investments = [],
        public array $justifications = [],
        public string $currency = 'euro',
    ) {}

    public function cacheData(): array
    {
        return [
            'companies' => $this->companies,
            'years' => $this->years,
            'states' => $this->states,
            'classifications' => $this->classifications,
            'investments' => $this->investments,
            'justifications' => $this->justifications,
            'currency' => $this->currency,
        ];
    }
}
