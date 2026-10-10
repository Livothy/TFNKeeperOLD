<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\RankReward;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RankRewardService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Rank Reward Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation, editing and distribution of rank rewards.
    |
    */

    /**
     * Creates a rank reward.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\RankReward|bool
     */
    public function createRankReward($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $rankReward = RankReward::create($data);

            if (!$this->logAdminAction($user, 'Created Rank Reward', 'Created rank reward '.$rankReward->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($rankReward);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a rank reward.
     *
     * @param \App\Models\RankReward $rankReward
     * @param array                  $data
     * @param \App\Models\User\User  $user
     *
     * @return \App\Models\RankReward|bool
     */
    public function updateRankReward($rankReward, $data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $rankReward->update($data);

            if (!$this->logAdminAction($user, 'Updated Rank Reward', 'Updated rank reward '.$rankReward->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn($rankReward);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a rank reward.
     *
     * @param \App\Models\RankReward $rankReward
     * @param \App\Models\User\User  $user
     *
     * @return bool
     */
    public function deleteRankReward($rankReward, $user) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($user, 'Deleted Rank Reward', 'Deleted rank reward '.$rankReward->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            $rankReward->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Gives out every active rank reward that is due today.
     * Each user is credited in their own transaction, so one failure doesn't block the others.
     *
     * @return int the number of users rewarded
     */
    public function distributeRankRewards() {
        $count = 0;

        foreach (RankReward::active()->with('rank')->get() as $rankReward) {
            if (!$rankReward->isDueToday()) {
                continue;
            }

            // Mark the reward as given first, so a second run on the same day can't pay out twice
            $rankReward->update(['last_rewarded_at' => now()]);

            $users = User::visible()->where('rank_id', $rankReward->rank_id)->get();
            foreach ($users as $user) {
                if ($this->grantRankReward($rankReward, $user)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Credits a rank reward to a single user and notifies them.
     *
     * @param \App\Models\RankReward $rankReward
     * @param \App\Models\User\User  $user
     *
     * @return bool
     */
    private function grantRankReward($rankReward, $user) {
        DB::beginTransaction();

        try {
            $assets = fillUserAssets($rankReward->rewardAssets, null, $user, 'Rank Reward', [
                'data' => 'Reward for being part of the '.$rankReward->rank->name.' rank',
            ]);
            if ($assets === false) {
                throw new \Exception('Failed to credit rewards.');
            }

            Notifications::create('RANK_REWARD', $user, [
                'rank_name' => $rankReward->rank->name,
                'rewards'   => createRewardsString($assets),
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            Log::error('Rank reward '.$rankReward->id.' failed for user '.$user->id.': '.$e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Validates the form data and encodes the rewards for storage.
     *
     * @param array $data
     *
     * @return array
     */
    private function populateData($data) {
        if (!isset($data['rewardable_type'])) {
            throw new \Exception('Please add at least one reward.');
        }

        $types = [
            'Item'      => 'App\Models\Item\Item',
            'Currency'  => 'App\Models\Currency\Currency',
            'Award'     => 'App\Models\Award\Award',
            'LootTable' => 'App\Models\Loot\LootTable',
            'Raffle'    => 'App\Models\Raffle\Raffle',
        ];

        // The data is stored as an asset table, json_encode()d.
        $assets = createAssetsArray();
        foreach ($data['rewardable_type'] as $key => $type) {
            if (!isset($types[$type])) {
                throw new \Exception('Invalid reward type.');
            }
            $asset = $types[$type]::find($data['rewardable_id'][$key] ?? null);
            if (!$asset) {
                throw new \Exception('One of the selected rewards could not be found.');
            }
            $quantity = (int) ($data['quantity'][$key] ?? 0);
            if ($quantity < 1) {
                throw new \Exception('Reward quantities must be at least 1.');
            }
            addAsset($assets, $asset, $quantity);
        }

        return [
            'name'        => $data['name'],
            'rank_id'     => $data['rank_id'],
            'reward_time' => $data['reward_time'],
            'is_active'   => isset($data['is_active']) && $data['is_active'] ? 1 : 0,
            'data'        => json_encode(getDataReadyAssets($assets)),
        ];
    }
}
