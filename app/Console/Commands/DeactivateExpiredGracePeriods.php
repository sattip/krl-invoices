<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class DeactivateExpiredGracePeriods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate subscriptions with expired grace periods';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCount = Subscription::where('is_manual', true)
            ->whereNotNull('grace_period_end')
            ->where('grace_period_end', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'canceled']);

        $this->info("Deactivated {$expiredCount} expired grace period subscription(s).");

        return Command::SUCCESS;
    }
}
