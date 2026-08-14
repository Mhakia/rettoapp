<?php

namespace App\Console\Commands;

use App\Jobs\SendInstitutionBulletinsJob;
use App\Models\Institution;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;

class SendBulletins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulletins:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue guardian bulletins for every institution whose frequency is due today';

    public function handle(): void
    {
        $today = now();

        Institution::where('bulletin_frequency', '!=', 'disabled')->each(function (Institution $institution) use ($today) {
            [$due, $periodStart, $periodEnd] = $this->period($institution->bulletin_frequency, $today);

            if (! $due) {
                return;
            }

            SendInstitutionBulletinsJob::dispatch($institution, $periodStart, $periodEnd);
        });
    }

    /**
     * @return array{0: bool, 1: CarbonInterface, 2: CarbonInterface}
     */
    protected function period(string $frequency, CarbonInterface $today): array
    {
        return match ($frequency) {
            'weekly' => [$today->isMonday(), $today->copy()->subWeek()->startOfDay(), $today->copy()->subDay()->endOfDay()],
            'biweekly' => [$today->isMonday() && $today->weekOfYear % 2 === 0, $today->copy()->subWeeks(2)->startOfDay(), $today->copy()->subDay()->endOfDay()],
            'monthly' => [$today->day === 1, $today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            default => [false, $today, $today],
        };
    }
}
