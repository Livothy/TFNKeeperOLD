<?php

namespace App\Models;

use App\Models\Rank\Rank;

class RankReward extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'rank_id', 'data', 'is_active', 'reward_time', 'last_rewarded_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rank_rewards';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_rewarded_at' => 'datetime',
    ];

    /**
     * Validation rules for rank rewards.
     *
     * @var array
     */
    public static $rules = [
        'name'        => 'required|max:191',
        'rank_id'     => 'required|exists:ranks,id',
        'reward_time' => 'required|in:1,2,3',
    ];

    /**
     * Reward intervals.
     *
     * @var array
     */
    public static $intervals = [
        1 => 'Every Day',
        2 => 'Every Week (Mondays)',
        3 => 'Every Month (the 1st)',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the rank this reward is given to.
     */
    public function rank() {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include active rank rewards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the rewards in the format used by the loot select widget.
     *
     * @return array
     */
    public function getRewardsAttribute() {
        $rewards = [];
        if ($this->data) {
            foreach ($this->rewardAssets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_type' => $class,
                        'rewardable_id'   => $id,
                        'quantity'        => $asset['quantity'],
                    ];
                }
            }
        }

        return $rewards;
    }

    /**
     * Gets the rewards as an asset array, ready to be credited.
     *
     * @return array
     */
    public function getRewardAssetsAttribute() {
        return $this->data ? parseAssetData(json_decode($this->data, true)) : createAssetsArray();
    }

    /**
     * Gets the display name of the reward interval.
     *
     * @return string
     */
    public function getIntervalNameAttribute() {
        return self::$intervals[$this->reward_time] ?? 'Unknown';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks whether this reward should be given out today.
     *
     * @return bool
     */
    public function isDueToday() {
        $now = now();
        if ($this->last_rewarded_at && $this->last_rewarded_at->isSameDay($now)) {
            return false;
        }

        switch ($this->reward_time) {
            case 1:
                return true;
            case 2:
                return $now->isMonday();
            case 3:
                return $now->day == 1;
        }

        return false;
    }
}
