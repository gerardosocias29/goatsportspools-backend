<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert existing datetime values from US Central time to UTC
     */
    public function up(): void
    {
        // Convert game_datetime from US Central to UTC
        $games = DB::table('games')->whereNotNull('game_datetime')->get();
        foreach ($games as $game) {
            $utcTime = Carbon::parse($game->game_datetime, 'America/Chicago')->utc();
            DB::table('games')->where('id', $game->id)->update(['game_datetime' => $utcTime]);
        }

        // Convert close_datetime from US Central to UTC
        $pools = DB::table('squares_pools')->whereNotNull('close_datetime')->get();
        foreach ($pools as $pool) {
            $utcTime = Carbon::parse($pool->close_datetime, 'America/Chicago')->utc();
            DB::table('squares_pools')->where('id', $pool->id)->update(['close_datetime' => $utcTime]);
        }

        // Convert number_assign_datetime from US Central to UTC
        $poolsWithNumbers = DB::table('squares_pools')->whereNotNull('number_assign_datetime')->get();
        foreach ($poolsWithNumbers as $pool) {
            $utcTime = Carbon::parse($pool->number_assign_datetime, 'America/Chicago')->utc();
            DB::table('squares_pools')->where('id', $pool->id)->update(['number_assign_datetime' => $utcTime]);
        }
    }

    /**
     * Reverse the migrations.
     * Convert UTC back to US Central time (for rollback)
     */
    public function down(): void
    {
        // Convert game_datetime from UTC back to US Central
        $games = DB::table('games')->whereNotNull('game_datetime')->get();
        foreach ($games as $game) {
            $centralTime = Carbon::parse($game->game_datetime, 'UTC')->setTimezone('America/Chicago');
            DB::table('games')->where('id', $game->id)->update(['game_datetime' => $centralTime->format('Y-m-d H:i:s')]);
        }

        // Convert close_datetime from UTC back to US Central
        $pools = DB::table('squares_pools')->whereNotNull('close_datetime')->get();
        foreach ($pools as $pool) {
            $centralTime = Carbon::parse($pool->close_datetime, 'UTC')->setTimezone('America/Chicago');
            DB::table('squares_pools')->where('id', $pool->id)->update(['close_datetime' => $centralTime->format('Y-m-d H:i:s')]);
        }

        // Convert number_assign_datetime from UTC back to US Central
        $poolsWithNumbers = DB::table('squares_pools')->whereNotNull('number_assign_datetime')->get();
        foreach ($poolsWithNumbers as $pool) {
            $centralTime = Carbon::parse($pool->number_assign_datetime, 'UTC')->setTimezone('America/Chicago');
            DB::table('squares_pools')->where('id', $pool->id)->update(['number_assign_datetime' => $centralTime->format('Y-m-d H:i:s')]);
        }
    }
};
