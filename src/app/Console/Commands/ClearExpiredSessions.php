<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired sessions from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lifetime = config('session.lifetime', 120);
        $expiredCount = DB::table('sessions')
            ->where('last_activity', '<', now()->subMinutes($lifetime)->timestamp)
            ->delete();

        $this->info("Cleared {$expiredCount} expired session(s).");
    }
}