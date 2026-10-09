<?php

namespace App\Models\Raid;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Model;
use App\Models\Raffle\Raffle;

class RaidReward extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'raid_id', 'rewardable_type', 'rewardable_id', 'quantity', 'damage_required',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raid_rewards';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'rewardable_type'        => 'required',
        'rewardable_id'          => 'required',
        'quantity'               => 'required|integer|min:1',
        'damage_required'        => 'required|integer',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'rewardable_type'        => 'required',
        'rewardable_id'          => 'required',
        'quantity'               => 'required|integer|min:1',
        'damage_required'        => 'required|integer',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the reward attached to the raid reward.
     */
    public function reward() {
        switch ($this->rewardable_type) {
            case 'Item':
                return $this->belongsTo(Item::class, 'rewardable_id');
                break;
            case 'Currency':
                return $this->belongsTo(Currency::class, 'rewardable_id');
                break;
            case 'LootTable':
                return $this->belongsTo(LootTable::class, 'rewardable_id');
                break;
            case 'Raffle':
                return $this->belongsTo(Raffle::class, 'rewardable_id');
                break;
        }

        return null;
    }
}
