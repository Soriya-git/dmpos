<?php

namespace App\Support;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DocumentNumber
{
    public static function make(string $modelClass, string $column, string $prefix, Branch|int|null $branch = null): string
    {
        return self::next($modelClass, $column, $prefix, $branch);
    }

    public static function next(string $modelClass, string $column, string $prefix, Branch|int|null $branch = null): string
    {
        $resolvedBranch = self::branch($branch);
        $year = (int) now()->format('Y');
        $documentType = strtoupper($prefix);

        return DB::transaction(function () use ($resolvedBranch, $year, $documentType): string {
            DB::table('document_sequences')->insertOrIgnore([
                'company_id' => $resolvedBranch->company_id,
                'branch_id' => $resolvedBranch->id,
                'document_type' => $documentType,
                'year' => $year,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('document_sequences')
                ->where('company_id', $resolvedBranch->company_id)
                ->where('branch_id', $resolvedBranch->id)
                ->where('document_type', $documentType)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                throw new RuntimeException('Unable to allocate the document sequence.');
            }

            $nextNumber = (int) $sequence->last_number + 1;

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'last_number' => $nextNumber,
                    'updated_at' => now(),
                ]);

            return self::format($documentType, $resolvedBranch, $year, $nextNumber);
        });
    }

    public static function preview(string $modelClass, string $column, string $prefix, Branch|int|null $branch = null): string
    {
        $resolvedBranch = self::branch($branch);
        $year = (int) now()->format('Y');
        $documentType = strtoupper($prefix);
        $lastNumber = DB::table('document_sequences')
            ->where('company_id', $resolvedBranch->company_id)
            ->where('branch_id', $resolvedBranch->id)
            ->where('document_type', $documentType)
            ->where('year', $year)
            ->value('last_number');

        return self::format($documentType, $resolvedBranch, $year, (int) $lastNumber + 1);
    }

    private static function branch(Branch|int|null $branch): Branch
    {
        if ($branch instanceof Branch) {
            return $branch;
        }

        $branchId = $branch;

        if (! $branchId && app()->bound('request')) {
            $branchId = request()->user()?->branch_id;
        }

        $resolvedBranch = $branchId
            ? Branch::query()->find($branchId)
            : Branch::query()->orderBy('id')->first();

        if (! $resolvedBranch) {
            throw new RuntimeException('A branch is required to generate a document number.');
        }

        return $resolvedBranch;
    }

    private static function format(string $documentType, Branch $branch, int $year, int $sequence): string
    {
        $branchCode = trim(
            strtoupper(preg_replace('/[^A-Z0-9-]+/i', '-', (string) $branch->code)),
            '-'
        ) ?: 'BR'.$branch->id;

        return sprintf(
            '%s/%s/%s-%s',
            $documentType,
            $branchCode,
            substr((string) $year, -2),
            str_pad((string) $sequence, 7, '0', STR_PAD_LEFT)
        );
    }
}
