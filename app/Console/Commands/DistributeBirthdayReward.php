<?php

namespace App\Console\Commands;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Item\Item;
use App\Models\User\User;
use App\Services\InventoryManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DistributeBirthdayReward extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'distribute-birthday-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grants the birthday item to every user whose birthday is this month.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $item = Item::find(Settings::get('birthday_item'));
        if (!$item) {
            $this->info('Birthday rewards are off (site setting birthday_item is 0 or not a valid item).');

            return;
        }

        $logType = 'Birthday Reward';
        $now = Carbon::now();

        // Users with a birthday this month who share at least some of it, in good standing.
        // Granted on the first of the month so no exact day is revealed.
        $users = User::whereMonth('birthday', $now->month)->where('is_banned', 0)->where('is_deactivated', 0)
            ->whereRelation('settings', 'birthday_setting', '>', 0)->get();

        $count = 0;
        foreach ($users as $user) {
            // Skip anyone already rewarded this month, so a second run can't pay twice
            $already = DB::table('items_log')->where('log_type', $logType)->where('recipient_type', 'User')->where('recipient_id', $user->id)
                ->whereYear('created_at', $now->year)->whereMonth('created_at', $now->month)->exists();
            if ($already) {
                continue;
            }

            if (!(new InventoryManager)->creditItem(null, $user, $logType, [
                'data'  => 'Happy Birthday, '.$user->name.'!',
                'notes' => null,
            ], $item, 1)) {
                $this->error('Failed to grant the birthday reward to '.$user->name.'.');
                continue;
            }

            Notifications::create('BIRTHDAY_REWARDED', $user, [
                'user_name' => $user->name,
            ]);
            $count++;
        }

        $this->info('Birthday rewards granted to '.$count.' user(s).');
    }
}
