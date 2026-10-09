<?php

namespace App\Console\Commands;

use App\Services\RaidService;
use Illuminate\Console\Command;

class CheckRaids extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-raids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any raids to update.';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        //
        (new RaidService)->updateQueue();
    }
}
