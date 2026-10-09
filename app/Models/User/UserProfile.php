<?php

namespace App\Models\User;

use App\Models\Model;
use App\Traits\Commentable;

class UserProfile extends Model {
    use Commentable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text', 'parsed_text', 'status_message', 'clear_status_on', 'status_set_on',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'clear_status_on' => 'datetime',
        'status_set_on'   => 'datetime',
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
     * Get the user this profile belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
