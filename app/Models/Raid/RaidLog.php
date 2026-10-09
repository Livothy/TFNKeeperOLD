<?php

namespace App\Models\Raid;

use App\Models\Model;
use App\Models\User\User;

class RaidLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'raid_id', 'log', 'log_type', 'data', 'damage',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raids_log';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who initiated the logged action.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the raid who received the logged action.
     */
    public function raid() {
        return $this->belongsTo(Raid::class, 'raid_id');
    }
}
