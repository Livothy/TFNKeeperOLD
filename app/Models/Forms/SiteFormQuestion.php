<?php

namespace App\Models\Forms;

use App\Models\Model;

class SiteFormQuestion extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'form_id', 'question', 'has_options', 'is_mandatory', 'is_multichoice'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'site_form_questions';

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
    public function form() 
    {
        return $this->belongsTo('App\Models\Forms\SiteForm', 'form_id');
    }

    /**
     * Get the options related to this question.
     */
    public function options() 
    {
        return $this->hasMany('App\Models\Forms\SiteFormOption', 'question_id');
    }

    /**
     * Get the answers related to this question.
     */
    public function answers() 
    {
        return $this->hasMany('App\Models\Forms\SiteFormAnswer', 'question_id')->withCount('likes')->orderBy('likes_count', 'desc');
    }

    /**********************************************************************************************
    
        Other Functions

    **********************************************************************************************/
    
    /**
     * Get the total non null answers to a question.
     */
    public function totalAnswers() 
    {
        return $this->answers->filter(function ($value) {
            return $value->option_id != null || $value->answer != null;
        })->count();
    }

}
