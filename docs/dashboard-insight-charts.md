# Dashboard insight charts

The optional **Portfolio insights** section on `/dashboard` contains four independent charts. All of them react to the active plant, year, status, classification, investment, justification and currency filters.

## Financial commitment flow

File: `resources/views/livewire/dashboard/insights/financial-flow-chart.blade.php`

Compares **Budgeted**, **Booked**, **Executed** and **Real value**. It shows how value moves from the planning baseline into commitment and execution.

Useful for detecting budgets with little purchasing commitment, booked amounts that are not progressing to execution, and differences between execution and the latest real-value estimate.

## Committed vs available budget

File: `resources/views/livewire/dashboard/insights/budget-availability-chart.blade.php`

Splits the budget into booked value and the remaining uncommitted balance. It gives a quick estimate of remaining purchasing capacity and helps identify plants or years with low commitment.

For visualization, committed value is capped at the budgeted total and available value never goes below zero. If booked exceeds budget, use the dashboard metrics to inspect the overrun amount.

## Project data coverage

File: `resources/views/livewire/dashboard/insights/data-coverage-chart.blade.php`

Compares projects that contain financial rows in `/data` with projects that have no financial data. A project without data cannot contribute reliable budget, booked, execution or real-value information.

Useful for measuring data completeness, locating missing information and explaining differences between the project count and the financially represented portfolio.

## Portfolio delivery stage

File: `resources/views/livewire/dashboard/insights/portfolio-stage-chart.blade.php`

Groups statuses into:

- **Pre-execution:** Capex and Planning.
- **Execution:** projects currently being delivered.
- **Finished:** completed projects.
- **Postponed:** deferred projects.

Useful for assessing portfolio maturity, monitoring finished projects and detecting a growing postponed backlog.

## Technical organization

The chart models are created in `app/Services/Dashboard/DashboardInsightChartService.php`.

The optional section is assembled in `resources/views/livewire/dashboard/partials/insight-charts.blade.php`. Each chart has its own Blade component under `resources/views/livewire/dashboard/insights/`.

The complete section is loaded from the Dashboard using:

```blade
@include('livewire.dashboard.partials.insight-charts')
```

Remove that include to discard the complete optional section. To discard only one chart, remove its individual include from `insight-charts.blade.php`; the remaining charts continue working.

## Operational charts: plants and suppliers

These charts are assembled in `resources/views/livewire/dashboard/partials/operational-charts.blade.php`. Each chart is independent under `resources/views/livewire/dashboard/operations/`.

### Projects by plant

Shows the number of filtered projects assigned to every plant. It helps compare workload, identify where the portfolio is concentrated and distinguish a plant with many small projects from one with fewer projects.

### Budget by plant

Shows budget allocation by plant in the selected currency. Read it together with **Projects by plant**: a plant may have few projects but a large share of capital, which implies a different risk and review priority.

### Top suppliers by booked value

Shows the ten suppliers with the largest booked amounts. It helps detect commitment concentration, dependency on key vendors and suppliers requiring closer commercial follow-up.

### Top suppliers by executed value

Shows the ten suppliers with the largest executed amounts. Comparing it with booked value helps identify suppliers whose commitments are progressing into execution and suppliers with a large pending gap.

Supplier charts are hidden when the filtered portfolio has no supplier value. To remove the complete operational block, delete this line from the Dashboard:

```blade
@include('livewire.dashboard.partials.operational-charts')
```
