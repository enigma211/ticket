<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TicketTrackingCodeService
{
    public static function generateNext(): int
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $row = DB::selectOne("SELECT nextval('ticket_tracking_code_seq') AS v");
            return (int) $row->v;
        }

        DB::table('ticket_tracking_sequence')->insert([]);
        return (int) DB::getPdo()->lastInsertId();
    }
}


