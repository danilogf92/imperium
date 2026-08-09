<?php

namespace App\Livewire\Data\Concerns;

use App\Support\Data\DataTableDefinition;

trait ConvertsDataCurrencies
{
    public function updatedEditData(
        mixed $value,
        string $field
    ): void {
        $euroField =
            DataTableDefinition::DOLLAR_TO_EURO_COLUMNS[$field]
            ?? null;

        if ($euroField === null || ! is_numeric($value)) {
            return;
        }

        $rate = (float) $this->project->rate;

        if ($rate <= 0) {
            $this->addError(
                "editData.{$field}",
                'The project rate must be greater than zero to convert currencies.'
            );

            return;
        }

        $this->resetValidation(
            "editData.{$field}"
        );

        $this->editData[$euroField] = round(
            (float) $value / $rate,
            2
        );
    }

    private function initializeBookedCalculator(): void
    {
        $multiplier = max(
            (float) (
                $this->project->company?->multiplier
                ?? 1
            ),
            0
        );

        $this->bookedMultiplier = number_format(
            $multiplier,
            6,
            '.',
            ''
        );

        $this->bookedBase = '0.00';
    }

    private function synchronizeEuroValues(): bool
    {
        $rate = (float) $this->project->rate;

        $hasDollarValue = collect(
            array_keys(
                DataTableDefinition::DOLLAR_TO_EURO_COLUMNS
            )
        )->contains(
            fn (string $field) =>
                abs(
                    (float) (
                        $this->editData[$field]
                        ?? 0
                    )
                ) > 0
        );

        if ($rate <= 0 && $hasDollarValue) {
            $this->addError(
                'editData.global_price',
                'The project rate must be greater than zero to convert dollars to euros.'
            );

            return false;
        }

        foreach (
            DataTableDefinition::DOLLAR_TO_EURO_COLUMNS
            as $dollarField => $euroField
        ) {
            $this->editData[$euroField] =
                $rate > 0
                    ? round(
                        (float) (
                            $this->editData[$dollarField]
                            ?? 0
                        ) / $rate,
                        2
                    )
                    : 0;
        }

        return true;
    }
}
