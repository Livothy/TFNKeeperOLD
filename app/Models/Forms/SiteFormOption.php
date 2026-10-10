<?php

namespace App\Models\Forms;

use App\Models\Model;

class SiteFormOption extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_id', 'option'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_form_options';

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
        'question' => 'required',
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'question' => 'required',
    ];

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the form this question belongs to.
     */
    public function question() 
    {
        return $this->belongsTo('App\Models\Forms\SiteQuestion', 'question_id');
    }

    /**
     * Get the answers related to this question.
     */
    public function answers() 
    {
        return $this->hasMany('App\Models\Forms\SiteFormAnswer', 'option_id');
    }

}
