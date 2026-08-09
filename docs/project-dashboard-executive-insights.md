# Project dashboard executive insights

The individual project dashboard includes a modular **Project health and delivery** section.

## Project health

The traffic light summarizes conditions requiring attention:

- **Red:** postponed project, booked or real value above budget, or an expired forecast end date while the project is not finished.
- **Amber:** missing financial data, weekly activities or planning milestones.
- **Green:** none of the monitored conditions is active.

This indicator is a management aid, not a replacement for project review.

## Financial variance

- **Available:** Budgeted minus Booked.
- **Budget - Real:** Budgeted minus Real Value.
- **Booked rate:** Booked divided by Budgeted.
- **Execution rate:** Executed divided by Budgeted.

Negative available or real variance values indicate that the corresponding value exceeded the budget.

## Planned vs financial progress

**Planned progress** is the sum of milestone percentages scheduled up to the current month. **Financial progress** is Executed divided by Budgeted.

Planned progress is a schedule proxy; it does not certify actual physical completion. A large difference between both values should trigger a project review.

## Milestones and weekly activities

The dashboard shows the next four milestones and every activity registered for the current and following ISO weeks.

## Data quality

The completeness score checks supplier, order number, area, general classification and unit price across every project-data row. The issue counters show missing values by field.

## Modular files

The calculations are isolated in `app/Services/Project/ProjectExecutiveInsightService.php`.

The section is assembled by `resources/views/livewire/project/partials/executive-insights.blade.php`, while every card is stored independently under `resources/views/livewire/project/insights/`.
