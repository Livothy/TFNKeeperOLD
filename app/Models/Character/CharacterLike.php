<?php

namespace App\Models\Character;

use App\Models\Model;

class CharacterLike extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'user_id', 'liked_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_likes';

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    public $dates = ['liked_at'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Character associated with the like.
     */
    public function character() {
        return $this->belongsTo('App\Models\Character\Character', 'character_id');
    }

    /**
     * User associated with the like.
     */
    public function user() {
        return $this->belongsTo('App\Models\User\User', 'user_id');
    }

    /**********************************************************************************************

        ATTRIBUTES

    **********************************************************************************************/
}
