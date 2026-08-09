<?php

namespace App\Support\Data;

final class DataTableDefinition
{
    public const PREFERENCE_KEY = 'data.table.visible_columns.v2';

    public const COLUMN_OPTIONS = [
        'id' => 'ID',
        'area' => 'Area',
        'group_1' => 'Group 1',
        'group_2' => 'Group 2',
        'description' => 'Description',
        'general_classification' => 'Classification',
        'item_type' => 'Item type',
        'unit' => 'Unit',
        'qty' => 'Qty',
        'unit_price' => 'Unit price',
        'global_price' => 'Budgeted $',
        'global_price_euros' => 'Budgeted €',
        'stage' => 'Stage',
        'real_value' => 'Real $',
        'real_value_euros' => 'Real €',
        'percentage' => 'Percentage',
        'executed_dollars' => 'Executed $',
        'executed_euros' => 'Executed €',
        'booked' => 'Booked $',
        'booked_euros' => 'Booked €',
        'supplier' => 'Supplier',
        'code' => 'Code',
        'order_no' => 'Order no.',
        'order_year' => 'Order year',
        'input_num' => 'Input no.',
        'observations' => 'Observations',
        'actions' => 'Actions',
    ];

    public const DEFAULT_COLUMNS = [
        'id',
        'area',
        'group_1',
        'description',
        'general_classification',
        'qty',
        'global_price_euros',
        'stage',
        'real_value_euros',
        'executed_euros',
        'supplier',
        'order_no',
        'order_year',
        'observations',
        'actions',
    ];

    public const NUMERIC_COLUMNS = [
        'qty',
        'unit_price',
        'global_price',
        'global_price_euros',
        'real_value',
        'real_value_euros',
        'percentage',
        'executed_dollars',
        'executed_euros',
        'booked',
        'booked_euros',
        'order_year',
    ];

    public const DOLLAR_TO_EURO_COLUMNS = [
        'global_price' => 'global_price_euros',
        'real_value' => 'real_value_euros',
        'executed_dollars' => 'executed_euros',
        'booked' => 'booked_euros',
    ];

    public const FILTER_COLUMNS = [
        'areaFilter' => 'area',
        'classificationFilter' => 'general_classification',
        'itemTypeFilter' => 'item_type',
        'stageFilter' => 'stage',
        'supplierFilter' => 'supplier',
        'orderYearFilter' => 'order_year',
    ];

    public const SEARCH_COLUMNS = [
        'area',
        'group_1',
        'group_2',
        'description',
        'general_classification',
        'item_type',
        'stage',
        'supplier',
        'code',
        'order_no',
        'order_year',
        'input_num',
        'observations',
    ];

    public const LINKED_CURRENCY_COLUMNS = [
        'global_price',
        'global_price_euros',
        'real_value',
        'real_value_euros',
        'executed_dollars',
        'executed_euros',
        'booked',
        'booked_euros',
    ];

    public const DERIVED_EURO_COLUMNS = [
        'global_price_euros',
        'real_value_euros',
        'executed_euros',
        'booked_euros',
    ];
}
