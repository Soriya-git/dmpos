<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class DocumentNumber
{
    public static function make(string $modelClass, string $column, string $prefix): string
    {
        $year = now()->format('y');
        $start = $prefix.$year.'-';

        /** @var class-string<Model> $modelClass */
        $lastNumber = $modelClass::query()
            ->where($column, 'like', $start.'%')
            ->lockForUpdate()
            ->orderByDesc($column)
            ->value($column);

        $sequence = 1;

        if (is_string($lastNumber) && str_starts_with($lastNumber, $start)) {
            $sequence = ((int) substr($lastNumber, strlen($start))) + 1;
        }

        return $start.str_pad((string) $sequence, 7, '0', STR_PAD_LEFT);
    }
}
