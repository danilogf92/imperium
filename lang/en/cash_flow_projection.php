<?php

return [
    'title' => 'Cash flow: Planning, Real and Projected',
    'subtitle' => 'Accumulated difference spread across each year’s remaining months',
    'planned' => 'Planning',
    'actual' => 'Real',
    'projected' => 'Projected',
    'series' => 'Series',
    'calculation' => 'Projection calculation by year',
    'year' => 'Year',
    'closed_months' => 'Closed months',
    'closed_planned' => 'Planning in closed months',
    'closed_actual' => 'Real in closed months',
    'difference' => 'Difference (Planning − Real)',
    'remaining_months' => 'Remaining months',
    'per_month' => 'Difference per month',
    'explanation' => 'Cutoff: start of :month in each displayed year. Planning and Real from earlier months are compared, then the difference is spread from the current month through December. Real is grouped by document date; current-month data is shown but does not affect the accumulated difference. Active filters and currency apply.',
    'formula' => 'Projected = monthly Planning + (accumulated Planning − accumulated Real) / remaining months. Each year is calculated separately; the current calendar month is used as the cutoff in every displayed year. Months without Real = 0; records without document date are excluded. Projections are rounded to 2 decimals without changing saved milestones.',
];
