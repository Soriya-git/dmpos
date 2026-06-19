<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('migrate:fresh --seed --force')->daily();

Artisan::command('print-jobs:prune {--months=4}', function (): int {
    $months = max(1, (int) $this->option('months'));
    $cutoff = now()->subMonths($months);

    $deleted = DB::table('print_jobs')
        ->where('created_at', '<', $cutoff)
        ->delete();

    $this->info("Deleted {$deleted} print job log(s) older than {$months} month(s).");

    return 0;
})->purpose('Delete old print job logs');

Schedule::command('print-jobs:prune --months=4')->daily();
