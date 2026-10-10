<?php

namespace App\Models\FetchQuest;

use App\Models\Model;

class FetchLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fetch_id', 'user_id', 'item_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fetch_log';

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
        'fetch_id' => 'required',
    ];

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the user who purchased the item.
     */
    public function user() 
    {
        return $this->belongsTo('App\Models\User\User');
    }
    
    /**
     * Get the purchased item.
     */
    public function item() 
    {
        return $this->belongsTo('App\Models\Item\Item');
    }

    /**
     * Get the shop the item was purchased from.
     */
    public function fetch() 
    {
        return $this->belongsTo('App\Models\FetchQuest\FetchQuest');
    }


}
