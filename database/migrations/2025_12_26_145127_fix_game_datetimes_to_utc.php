<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix game_datetime values that were stored as US Central time without UTC conversion.
     * Treat current values as Central Time and convert to proper UTC.
     */
    public function up(): void
    {
        // Convert game_datetime from US Central to UTC
        // Current values are stored as local Central time (e.g., "19:00" for 7pm CT)
        // Need to convert to UTC (e.g., "01:00" next day for 7pm CT)
        $games = DB::table('games')->whereNotNull('game_datetime')->get();
        foreach ($games as $game) {
            $utcTime = Carbon::parse($game->game_datetime, 'America/Chicago')->utc();
            DB::table('games')->where('id', $game->id)->update(['game_datetime' => $utcTime]);
        }
    }

    /**
     * Reverse the migrations.
     * Convert UTC back to US Central time (for rollback)
     */
    public function down(): void
    {
        $games = DB::table('games')->whereNotNull('game_datetime')->get();
        foreach ($games as $game) {
            $centralTime = Carbon::parse($game->game_datetime, 'UTC')->setTimezone('America/Chicago');
            DB::table('games')->where('id', $game->id)->update(['game_datetime' => $centralTime->format('Y-m-d H:i:s')]);
        }
    }
};
