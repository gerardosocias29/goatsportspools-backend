<?php

namespace App\Console\Commands;

use App\Models\SquaresPool;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CloseExpiredPools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squares:close-expired-pools';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close pools that have reached their close_datetime';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Find pools that should be closed
        $pools = SquaresPool::where('pool_status', 'open')
            ->whereNotNull('close_datetime')
            ->where('close_datetime', '<=', $now)
            ->get();

        if ($pools->isEmpty()) {
            $this->info('No pools to close');
            return 0;
        }

        $closedCount = 0;

        foreach ($pools as $pool) {
            $pool->update([
                'pool_status' => 'closed',
                'modify_user_id' => 1, // System user
                'modify_date' => now()->toDateString(),
            ]);

            $this->info("Closed pool #{$pool->pool_number} - {$pool->pool_name}");
            $closedCount++;
        }

        $this->info("Total pools closed: {$closedCount}");

        return 0;
    }
}
