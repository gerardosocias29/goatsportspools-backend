<?php

namespace App\Console\Commands;

use App\Models\SquaresPool;
use App\Models\SquaresPoolSquare;
use App\Services\PoolEmailService;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssignScheduledNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squares:assign-scheduled-numbers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-assign numbers for Type B pools at their scheduled time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // Find Type B pools ready for number assignment
        $pools = SquaresPool::where('pool_type', 'B')
            ->where('numbers_assigned', false)
            ->whereNotNull('number_assign_datetime')
            ->where('number_assign_datetime', '<=', $now)
            ->get();

        if ($pools->isEmpty()) {
            $this->info('No pools ready for number assignment');
            return 0;
        }

        $assignedCount = 0;

        foreach ($pools as $pool) {
            DB::beginTransaction();
            try {
                // Generate random numbers 0-9
                $xNumbers = collect(range(0, 9))->shuffle()->values()->toArray();
                $yNumbers = collect(range(0, 9))->shuffle()->values()->toArray();

                // Update pool with numbers (keep pool status unchanged - CloseExpiredPools handles closing)
                $pool->update([
                    'x_numbers' => $xNumbers,
                    'y_numbers' => $yNumbers,
                    'numbers_assigned' => true,
                    'modify_user_id' => 1, // System user
                    'modify_date' => now()->toDateString(),
                ]);

                // Update all squares with their assigned numbers
                $squares = SquaresPoolSquare::where('pool_id', $pool->id)->get();
                foreach ($squares as $square) {
                    $square->update([
                        'x_number' => $xNumbers[$square->x_coordinate],
                        'y_number' => $yNumbers[$square->y_coordinate],
                        'modify_user_id' => 1,
                        'modify_date' => now()->toDateString(),
                    ]);
                }

                DB::commit();

                $this->info("Assigned numbers for pool #{$pool->pool_number} - {$pool->pool_name}");
                $assignedCount++;

                // Send emails to all players with their assigned numbers
                $emailService = new PoolEmailService();
                $emailResult = $emailService->sendNumbersAssignedEmails($pool, $xNumbers, $yNumbers);

                if ($emailResult['sent'] > 0) {
                    $this->info("  → Sent {$emailResult['sent']} email(s) to players");
                }
                if ($emailResult['failed'] > 0) {
                    $this->warn("  → Failed to send {$emailResult['failed']} email(s)");
                }

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to assign numbers for pool #{$pool->pool_number}: {$e->getMessage()}");
            }
        }

        $this->info("Total pools assigned: {$assignedCount}");

        return 0;
    }
}
