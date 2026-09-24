<?php

namespace Tests\Unit;

use App\Services\Resume\CashFlowForecast;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class CashFlowForecastTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_annual_projection_matches_the_example_without_moving_the_plan(): void
    {
        CarbonImmutable::setTestNow('2026-09-23');
        $periods = collect(range(1, 12))->mapWithKeys(fn ($month) => [sprintf('2026-%02d', $month) => 0.0]);
        $planned = $periods->keys()->combine([80000, 400000, 100000, 60000, 100000, 80000, 55000, 10000, 30000, 20000, 65000, 140000]);
        $actual = $periods->keys()->take(8)->combine([57037.34, 279513.65, 99364.40, 80096.40, 105652.57, 88171.52, 56318.03, 1505]);
        $result = (new CashFlowForecast)->annualProjection($planned, $actual, $periods);

        $this->assertSame(885000.0, $result['summaries'][0]['closed_planned']);
        $this->assertSame(767658.91, $result['summaries'][0]['closed_actual']);
        $this->assertSame(117341.09, $result['summaries'][0]['difference']);
        $this->assertSame(4, $result['summaries'][0]['remaining_months']);
        $this->assertEqualsWithDelta(29335.2725, $result['summaries'][0]['per_month'], 0.000001);
        $this->assertSame(array_fill(0, 8, null), array_slice(array_column($result['rows'], 'projected'), 0, 8));
        $this->assertSame([59335.27, 49335.27, 94335.27, 169335.27], array_slice(array_column($result['rows'], 'projected'), 8));
        $this->assertSame(0.0, $result['rows'][8]['actual']);
        $this->assertSame(30000, $planned->get('2026-09'));

        // Partial current-month spending must not change the closed-month adjustment.
        $actual->put('2026-09', 1000);
        $this->assertSame($result['summaries'], (new CashFlowForecast)->annualProjection($planned, $actual, $periods)['summaries']);
    }

    public function test_september_distributes_positive_and_negative_differences_over_all_four_remaining_months(): void
    {
        CarbonImmutable::setTestNow('2026-09-23');
        $periods = collect(range(1, 12))->mapWithKeys(fn ($month) => [sprintf('2026-%02d', $month) => 0.0]);
        $planned = collect(['2026-01' => 70000, '2026-08' => 50000, '2026-09' => 1000, '2026-10' => 2000, '2026-11' => 3000, '2026-12' => 4000]);
        $originalPlan = $planned->all();

        foreach ([100000 => 5000.0, 140000 => -5000.0] as $closedActual => $adjustment) {
            $actual = collect(['2026-08' => $closedActual, '2026-09' => 999999]);
            $result = (new CashFlowForecast)->annualProjection($planned, $actual, $periods);
            $summary = $result['summaries'][0];

            $this->assertSame(120000.0, $summary['closed_planned']);
            $this->assertSame((float) $closedActual, $summary['closed_actual']);
            $this->assertSame(120000.0 - $closedActual, $summary['difference']);
            $this->assertSame(4, $summary['remaining_months']);
            $this->assertSame($adjustment, $summary['per_month']);
            $this->assertSame(array_fill(0, 8, null), array_slice(array_column($result['rows'], 'projected'), 0, 8));
            $this->assertSame(
                [1000 + $adjustment, 2000 + $adjustment, 3000 + $adjustment, 4000 + $adjustment],
                array_slice(array_column($result['rows'], 'projected'), 8)
            );
            $this->assertSame($originalPlan, $planned->all());
        }
    }

    public function test_each_year_uses_the_same_month_cutoff_without_mixing_values(): void
    {
        CarbonImmutable::setTestNow('2026-12-15');
        $periods = collect();
        foreach ([2025, 2026, 2027] as $year) {
            foreach (range(1, 12) as $month) {
                $periods->put(sprintf('%d-%02d', $year, $month), 0.0);
            }
        }
        $result = (new CashFlowForecast)->annualProjection(
            collect(['2025-01' => 900, '2026-01' => 100, '2026-12' => 50, '2027-01' => 200]),
            collect(['2026-01' => 180]),
            $periods
        );
        $this->assertSame(1, $result['summaries'][0]['remaining_months']);
        $this->assertSame(900.0, $result['summaries'][0]['per_month']);
        $this->assertSame(900.0, $result['rows'][11]['projected']);
        $this->assertSame(-80.0, $result['summaries'][1]['per_month']);
        $this->assertSame(-30.0, $result['rows'][23]['projected']);
        $this->assertNull($result['rows'][24]['projected']);
        $this->assertSame(200.0, $result['rows'][35]['projected']);
        $this->assertSame(1, $result['summaries'][2]['remaining_months']);
        $this->assertSame(['rows' => [], 'summaries' => []], (new CashFlowForecast)->annualProjection(collect(), collect(), collect()));
    }
}
