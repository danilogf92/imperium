<?php

namespace App\Support\Project;

final class ProjectTableDefinition
{
    public const PREFERENCE_KEY = 'projects.table.visible_columns.v5';

    public const COLUMN_OPTIONS = [
        'id' => 'ID',
        'order' => 'Order',
        'plant' => 'Plant',
        'pda_code' => 'PDA code',
        'name' => 'Name',
        'forecast_start_year' => 'Forecast Start Year',
        'investments' => 'Investments',
        'state' => 'State',
        'budgeted_euros' => 'Budgeted Euros',
        'forecast_end_date' => 'Forecast End Date',
        'real_euros' => 'Real Euros',
        'rate' => 'Rate',
        'budgeted_dollars' => 'Budgeted Dollars',
        'real_dollars' => 'Real Dollars',
        'upload_pda' => 'Upload PDA',
        'links' => 'Links',
        'project_ideas' => 'Project ideas',
        'handover_certificate' => 'Project Handover Certificate',
        'classification' => 'Classification',
        'justification' => 'Justification',
        'creator' => 'Created By',
        'data_uploaded' => 'Data Uploaded',
        'approve_date' => 'Approved Date',
        'close_date' => 'Close Date',
        'actions' => 'Actions',
    ];

    public const DEFAULT_COLUMNS = [
        'order',
        'plant',
        'pda_code',
        'name',
        'forecast_start_year',
        'investments',
        'state',
        'budgeted_euros',
        'forecast_end_date',
        'real_euros',
        'rate',
        'actions',
    ];

    public const SORTABLE_COLUMNS = [
        'id',
        'order',
        'name',
        'pda_code',
        'rate',
        'state',
        'investments',
        'classification_of_investments',
        'justification',
        'forecast_start_date',
        'forecast_end_date',
        'data_uploaded',
        'quartile_date',
        'approve_date',
        'close_date',
        'file_name',
        'created_at',
        'updated_at',
        'budgeted_euros',
        'real_euros',
        'budgeted_dollars',
        'real_dollars',
    ];
}
