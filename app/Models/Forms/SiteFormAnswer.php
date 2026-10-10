<?php

namespace App\Models\Forms;

use App\Models\Model;

class SiteFormAnswer extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_id', 'question_id', 'option_id', 'user_id', 'answer', 'submission_number'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_form_answers';

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
        'form_id' => 'required',
        'question_id' => 'required'
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'form_id' => 'required',
        'question_id' => 'required'    
    ];

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the user who sent in this answer.
     */
    public function user() 
    {
        return $this->belongsTo('App\Models\User\User');
    }


    /**
     * Get the form this answer belongs to.
     */
    public function form() 
    {
        return $this->belongsTo('App\Models\Forms\SiteForm', 'form_id');
    }

    /**
     * Get the question this answer belongs to.
     */
    public function question() 
    {
        return $this->belongsTo('App\Models\Forms\SiteQuestion', 'question_id');
    }

    /**
     * Get the option this answer picked (if not free text answer)
     */
    public function option() 
    {
        return $this->belongsTo('App\Models\Forms\SiteFormOption', 'option_id');
    }

    /**
     * Get the likes to this answer 
     */
    public function likes() 
    {
        return $this->hasMany('App\Models\Forms\SiteFormLike', 'answer_id');
    }

    /**********************************************************************************************
    
        MISC

    **********************************************************************************************/
    
    /**
     * Get whether user has liked this answer already or not
     */
    public function hasLiked($user) 
    {
        $likes = $this->likes()->where('user_id', $user->id);
        return $likes->count() > 0;
    }


}
