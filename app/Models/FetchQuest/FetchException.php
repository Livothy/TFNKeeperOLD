<?php

namespace App\Models\FetchQuest;

use App;
use Config;
use App\Models\Model;

class FetchException extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fetch_quest_id', 'exception_type', 'exception_id'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fetch_exceptions';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the reward attached to the loot entry.
     */
    public function exception() 
    {
        switch ($this->exception_type)
        {
            case 'Item':
                return $this->belongsTo('App\Models\Item\Item', 'exception_id');
            case 'ItemCategory':
                return $this->belongsTo('App\Models\Item\ItemCategory', 'exception_id');
        }
        return null;
    }
}