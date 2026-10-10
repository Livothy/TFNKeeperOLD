<?php

namespace App\Console\Commands;

use App\Services\RankRewardService;
use Illuminate\Console\Command;

class RewardRanks extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reward-ranks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gives out rank rewards that are due today.';

    /**
     * Execute the console command.
     */
    public function handle() {
        $count = (new RankRewardService)->distributeRankRewards();
        $this->info('Rank rewards given to '.$count.' user(s).');
    }
}
