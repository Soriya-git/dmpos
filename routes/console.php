<?php

use App\Models\Branch;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

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

Artisan::command('print-agent:token {branch}', function (int $branch): int {
    $model = Branch::query()->find($branch);

    if (! $model) {
        $this->error('Branch not found.');

        return 1;
    }

    $token = Str::random(64);
    $model->forceFill(['print_agent_token' => $token])->save();

    $this->warn('Store this token securely. It will not be displayed again by this command.');
    $this->line($token);

    return 0;
})->purpose('Generate or rotate the local print-agent token for a branch');
