<?php

namespace App\Services;

use App\Models\User\User;
use App\Models\UserAds;
use Illuminate\Support\Facades\DB;

class UserAdsService extends Service {
    /*
    |--------------------------------------------------------------------------
    | User Ads Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of user ads.
    |
    */

    /**
     * Creates a user ad.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\UserAds|bool
     */
    public function createUserAds($data, $user) {
        DB::beginTransaction();

        try {
            // TFN: one active ad per user (ads clear after a week, or the user can delete theirs)
            if (UserAds::where('user_id', $user->id)->exists()) {
                throw new \Exception('You already have an active ad. Delete it or wait for it to expire before posting another.');
            }
            $data['text'] = parse($data['text']);
            $data['user_id'] = $user->id;

            $user_ads = UserAds::create($data);

            return $this->commitReturn($user_ads);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a user ad.
     *
     * @param \App\Models\UserAds   $user_ads
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\UserAds|bool
     */
    public function updateUserAds($user_ads, $data, $user) {
        DB::beginTransaction();

        try {
            $data['text'] = parse($data['text']);
            // Keep the original poster; staff editing an ad must not take it over
            unset($data['user_id']);

            $user_ads->update($data);

            return $this->commitReturn($user_ads);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a user ad.
     *
     * @param \App\Models\UserAds $user_ads
     *
     * @return bool
     */
    public function deleteUserAds($user_ads) {
        DB::beginTransaction();

        try {
            $user_ads->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
