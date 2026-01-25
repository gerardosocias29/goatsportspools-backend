<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix close_datetime that was double-converted (was already UTC, then converted again)
     * Revert: UTC -> US Central (undo the wrong conversion)
     */
    public function up(): void
    {
        // Revert close_datetime from UTC back to what it was (undo the previous migration's conversion)
        // The previous migration treated it as US Central and converted to UTC
        // But it was already UTC, so we need to reverse that conversion
        $pools = DB::table('squares_pools')->whereNotNull('close_datetime')->get();
        foreach ($pools as $pool) {
            // Reverse the conversion: treat current value as UTC, convert back to US Central time
            // This undoes: Carbon::parse($value, 'America/Chicago')->utc()
            $originalTime = Carbon::parse($pool->close_datetime, 'UTC')->setTimezone('America/Chicago');
            DB::table('squares_pools')->where('id', $pool->id)->update([
                'close_datetime' => $originalTime->format('Y-m-d H:i:s')
            ]);
        }

        // Same for number_assign_datetime
        $poolsWithNumbers = DB::table('squares_pools')->whereNotNull('number_assign_datetime')->get();
        foreach ($poolsWithNumbers as $pool) {
            $originalTime = Carbon::parse($pool->number_assign_datetime, 'UTC')->setTimezone('America/Chicago');
            DB::table('squares_pools')->where('id', $pool->id)->update([
                'number_assign_datetime' => $originalTime->format('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-apply the conversion if needed
        $pools = DB::table('squares_pools')->whereNotNull('close_datetime')->get();
        foreach ($pools as $pool) {
            $utcTime = Carbon::parse($pool->close_datetime, 'America/Chicago')->utc();
            DB::table('squares_pools')->where('id', $pool->id)->update(['close_datetime' => $utcTime]);
        }
    }
};
