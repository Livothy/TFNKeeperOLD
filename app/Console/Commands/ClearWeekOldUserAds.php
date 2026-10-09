<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearWeekOldUserAds extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-week-old-user-ads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears 7 day ads to clear up database.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('*********************');
        $this->info('* REMOVING WEEK OLD USER ADS *');
        $this->info('*********************'."\n");

        /*
         * delete the 7 day old ads.
         * make sure that in kernel it stays to daily() else the ad will carry over to the next week
         */
        DB::table('user_ads')->where('created_at', '<=', now()->subDays(7))->delete();

        $this->line("\nWeek old ads have been successfully deleted.");
    }
}
