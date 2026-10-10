<?php

namespace App\Models\User;

use App\Models\Model;

class UserSettings extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'is_fto', 'submission_count', 'banned_at', 'ban_reason', 'birthday_setting',
        'deactivate_reason', 'deactivated_at',
        'allow_character_likes', 'border_settings', 'hol_plays', 'selected_character_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_settings';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'banned_at'      => 'datetime',
        'deactivated_at' => 'datetime',
        'border_settings' => 'array',
    ];

    /**
     * The primary key of the model.
     *
     * @var string
     */
    public $primaryKey = 'user_id';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this set of settings belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the character the user has selected, if it is still visible and theirs.
     */
    public function selectedCharacter() {
        return $this->belongsTo('App\Models\Character\Character', 'selected_character_id')->visible()->where('user_id', $this->user_id);
    }
}
