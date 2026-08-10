<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

final class ProjectOrderSort
{
    public static function apply(Builder $query): Builder
    {
        $wrappedColumn = $query->getQuery()->getGrammar()->wrap('order');
        $castType = in_array(
            $query->getConnection()->getDriverName(),
            ['mysql', 'mariadb'],
            true
        ) ? 'UNSIGNED' : 'INTEGER';

        return $query
            ->orderByRaw("CASE WHEN {$wrappedColumn} IS NULL OR {$wrappedColumn} = '' THEN 1 ELSE 0 END")
            ->orderByRaw("CAST({$wrappedColumn} AS {$castType}) ASC")
            ->orderBy('order');
    }
}
