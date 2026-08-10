<?php

/*
|--------------------------------------------------------------------------
| Dashboard chart data sources
|--------------------------------------------------------------------------
|
| Este archivo define de dónde sale cada eje y cada serie del dashboard.
| Los años seleccionados en los filtros SIEMPRE se aplican usando
| projects.forecast_start_date; aquí se elige cómo agrupar, por mes, los
| proyectos que ya pertenecen a ese conjunto filtrado.
|
| Valores financieros disponibles:
| - budgeted: global_price (USD) / global_price_euros (EUR)
| - booked:   booked (USD) / booked_euros (EUR)
| - executed: executed (USD) / executed_euros (EUR)
|
*/

return [
    /* Etiquetas del eje X compartido por las gráficas mensuales. */
    'months' => [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ],

    'cumulative_projects_budget' => [
        // Eje X: mes de forecast_start_date.
        'projects_date_column' => 'projects.forecast_start_date',
        // Eje Y izquierdo: porcentaje acumulado de proyectos.
        'projects_series_label' => 'Cumulative projects',
        // Eje Y derecho: presupuesto acumulado en la moneda seleccionada.
        'budget_date_column' => 'projects.forecast_start_date',
        'budget_value' => 'budgeted',
        'budget_series_label' => 'Cumulative budget',
    ],

    'cumulative_approved_budget' => [
        // projectsByApprovalMonth: cantidad de proyectos por mes de approve_date.
        'projects_date_column' => 'projects.approve_date',
        'projects_states' => ['Execution', 'Finished'],
        'projects_series_label' => 'Cumulative approved projects',
        // approvedBudgetByMonth: presupuesto por mes de approve_date.
        'budget_date_column' => 'projects.approve_date',
        'budget_value' => 'budgeted',
        'budget_states' => ['Execution', 'Finished'],
        'budget_series_label' => 'Cumulative approved budget',
    ],

    'forecast_start_vs_approved' => [
        // Eje X: meses. Eje Y: porcentaje acumulado de proyectos (0-100%).
        'planned_date_column' => 'projects.forecast_start_date',
        'planned_series_label' => 'Planned % (Start date)',
        'actual_date_column' => 'projects.approve_date',
        'actual_states' => ['Execution', 'Finished'],
        'actual_series_label' => 'Actual % (Approved date)',
        'y_axis_min' => 0,
        'y_axis_max' => 100,
    ],

    'forecast_end_vs_close' => [
        // Eje X: meses. Eje Y: porcentaje acumulado de proyectos (0-100%).
        'forecast_date_column' => 'projects.forecast_end_date',
        'forecast_series_label' => 'Forecast End Date %',
        'close_date_column' => 'projects.close_date',
        'close_series_label' => 'Close Date %',
        'y_axis_min' => 0,
        'y_axis_max' => 100,
    ],
];
