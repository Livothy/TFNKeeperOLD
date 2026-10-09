<?php

namespace App\Models\User;

use App\Models\Model;
use App\Traits\Commentable;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPage extends Model {
    use Commentable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'key', 'text', 'parsed_text', 'is_visible', 'show_on_profile', 'can_comment', 'logged_in_only',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_pages';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'title' => 'required|between:2,100',
        'key'   => 'required|between:2,100',
        'text'  => 'nullable',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'title' => 'required|between:2,100',
        'key'   => 'required|between:2,100',
        'text'  => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user this page belongs to.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Display this page's title as a link.
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'">'.$this->title.'</a>';
    }

    /**
     * Get the URL of this page.
     */
    public function getUrlAttribute() {
        return url('user/'.$this->user->name.'/page/'.$this->key);
    }

    /**
     * Gets the edit URL.
     *
     * @return string
     */
    public function getEditUrlAttribute() {
        return url('account/user-pages/edit/'.$this->id);
    }
}
