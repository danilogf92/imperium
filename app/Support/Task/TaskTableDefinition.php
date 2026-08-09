<?php

namespace App\Support\Task;

final class TaskTableDefinition
{
    public const PREFERENCE_KEY = 'tasks.table.visible_columns';

    public const COLUMN_OPTIONS = [
        'pda_code' => 'PDA code',
        'description' => 'Description',
        'qty' => 'Qty',
        'real_value' => 'Real value $',
        'global_price' => 'Global price $',
        'booked' => 'Booked $',
        'percentage' => 'Percentage',
        'supplier' => 'Supplier',
        'order_no' => 'Order no.',
        'actions' => 'Actions',
    ];

    public const DEFAULT_COLUMNS = [
        'pda_code', 'description', 'qty', 'real_value', 'global_price',
        'booked', 'percentage', 'supplier', 'order_no', 'actions',
    ];

    public const STATUSES = ['completed', 'progress', 'pending'];

    public const SORTABLE_COLUMNS = [
        'description', 'qty', 'real_value', 'global_price', 'booked',
        'percentage', 'supplier', 'order_no',
    ];
}
