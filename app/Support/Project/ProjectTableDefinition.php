<?php

namespace App\Support\Project;

final class ProjectTableDefinition
{
    public const PREFERENCE_KEY = 'projects.table.visible_columns.v6';

    public const COLUMN_OPTIONS = [
        'id' => 'ID',
        'order' => 'Order',
        'plant' => 'Plant',
        'pda_code' => 'PDA code',
        'name' => 'Name',

        'forecast_start_year' => 'Forecast Start Year',
        'forecast_end_date' => 'Forecast End Date',

        'investments' => 'Investments',
        'state' => 'State',

        /*
         * Financial information - EUR
         */
        'budgeted_euros' => 'Budgeted Euros',
        'real_euros' => 'Real Euros',
        'executed_euros' => 'Executed Euros',
        'booked_euros' => 'Booked Euros',

        /*
         * Exchange rate
         */
        'rate' => 'Rate',

        /*
         * Financial information - USD
         */
        'budgeted_dollars' => 'Budgeted Dollars',
        'real_dollars' => 'Real Dollars',
        'executed_dollars' => 'Executed Dollars',
        'booked' => 'Booked Dollars',

        /*
         * Project information
         */
        'upload_pda' => 'Upload PDA',
        'links' => 'Links',
        'project_ideas' => 'Project ideas',
        'handover_certificate' => 'Project Handover Certificate',
        'classification' => 'Classification',
        'justification' => 'Justification',
        'creator' => 'Created By',
        'data_uploaded' => 'Data Uploaded',

        /*
         * Dates
         */
        'approve_date' => 'Approved Date',
        'close_date' => 'Close Date',

        /*
         * Actions
         */
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
        'real_euros',
        'executed_euros',
        'booked_euros',

        'forecast_end_date',

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

        /*
         * Financial information - EUR
         */
        'budgeted_euros',
        'real_euros',
        'executed_euros',
        'booked_euros',

        /*
         * Financial information - USD
         */
        'budgeted_dollars',
        'real_dollars',
        'executed_dollars',
        'booked',
    ];
}
