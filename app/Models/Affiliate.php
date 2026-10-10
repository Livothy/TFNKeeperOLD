<?php

namespace App\Models;
use App\Models\Model;
use App\Models\User\User;

use App\Events\CommentCreated;
use App\Events\CommentUpdated;
use App\Events\CommentDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Affiliate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id', 'user_id', 'name', 'description',
        'is_featured', 'url', 'image_url', 'staff_comment',
        'status', 'guest_name', 'message', 'slug',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'affiliates';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;
    
    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:affiliates|between:2,25',
        'url'         => ['required', 'max:100', 'url', 'regex:#^https?://#i'],
        'image_url'   => ['nullable', 'max:255', 'url', 'regex:#^https?://#i'],
        'description' => 'nullable|max:100',
        'message'     => 'nullable|max:1000',
        'guest_name'  => 'nullable|max:50',
    ];
    
    /**
     * Validation rules for submission updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'          => 'required|between:2,25',
        'url'           => ['required', 'max:100', 'url', 'regex:#^https?://#i'],
        'image_url'     => ['nullable', 'max:255', 'url', 'regex:#^https?://#i'],
        'description'   => 'nullable|max:100',
        'is_featured'   => 'nullable|boolean',
        'staff_comment' => 'nullable|max:1000',
    ];


    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
   




    
    /**********************************************************************************************
    
        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include pending submissions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Pending');
    }
    /**
     * Scope a query to only include pending submissions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query, $num)
    {
        if($num == 0) return $query->where('is_featured', 0);
        else return $query->where('is_featured', 1);
    }


    /**
     * Scope a query to sort submissions oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort submissions by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    
    /**********************************************************************************************
    
        ACCESSORS

    **********************************************************************************************/

    /**
     * Determine who submitted the affiliate.
     */
    public function getSubmitterAttribute()
    {
        $user = $this->user_id ? User::find($this->user_id) : null;
        if($user) return $user->displayName;
        return e($this->guest_name ?? 'a guest');
    }
    
    /**
     * Determine who submitted the affiliate.
     */
    public function getStaffAttribute()
    {
        $staff = $this->staff_id ? User::find($this->staff_id) : null;
        if($staff) return $staff->displayName;
        return "a staff member";
    }
    
    /**
     * Determine who submitted the affiliate.
     */
    public function getIconAttribute()
    {
        // Everything here can come from a public application, so escape it all
        $title = '<strong>'.e($this->name).'</strong>'.($this->description ? ' <br> '.e(strip_tags($this->description)) : '');
        return '<a href="'.e($this->url).'" target="_blank" rel="noopener noreferrer nofollow"><img src="'.e($this->image_url ?: asset('images/affiliate.png')).'" data-toggle="tooltip" data-html="true" title="'.e($title).'" alt="'.e($this->name).'" class="m-1 avatar" referrerpolicy="no-referrer" /></a>';
    }

    public function getStatusUrlAttribute()
    {
        return url('/affiliates/status/').'/'.$this->slug;
    }



}
