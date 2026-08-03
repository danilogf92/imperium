<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'project_id',
        'area',
        'group_1',
        'group_2',
        'description',
        'general_classification',
        'item_type',
        'unit',
        'qty',
        'unit_price',
        'global_price',
        'stage',
        'real_value',
        'percentage',
        'executed_dollars',
        'executed_euros',
        'supplier',
        'code',
        'order_no',
        'input_num',
        'observations',
        'booked',
        'global_price_euros',
        'real_value_euros',
        'booked_euros',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'global_price' => 'decimal:2',
        'real_value' => 'decimal:2',
        'percentage' => 'decimal:2',
        'executed_dollars' => 'decimal:2',
        'executed_euros' => 'decimal:2',
        'booked' => 'decimal:2',
        'global_price_euros' => 'decimal:2',
        'real_value_euros' => 'decimal:2',
        'booked_euros' => 'decimal:2',

        'real_value_changed_at' => 'datetime',
        'percentage_changed_at' => 'datetime',
        'executed_changed_at' => 'datetime',
        'booked_changed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (Data $data): void {
            /*
             * Real value:
             * actualiza la fecha si cambia dólares o euros.
             */
            if ($data->isDirty([
                'real_value',
                'real_value_euros',
            ])) {
                $data->real_value_changed_at = now();
            }

            /*
             * Percentage.
             */
            if ($data->isDirty('percentage')) {
                $data->percentage_changed_at = now();
            }

            /*
             * Executed:
             * actualiza la misma fecha si cambia dólares o euros.
             */
            if ($data->isDirty([
                'executed_dollars',
                'executed_euros',
            ])) {
                $data->executed_changed_at = now();
            }

            /*
             * Booked:
             * actualiza la misma fecha si cambia dólares o euros.
             */
            if ($data->isDirty([
                'booked',
                'booked_euros',
            ])) {
                $data->booked_changed_at = now();
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
