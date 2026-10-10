<?php

namespace App\Models\Forms;

use App\Models\Model;

class SiteFormLike extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'answer_id', 'user_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_form_likes';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'answer_id' => 'required',
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'answer_id' => 'required',  
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
    public function answer() 
    {
        return $this->belongsTo('App\Models\Forms\SiteFormAnswer', 'answer_id');
    }

}
