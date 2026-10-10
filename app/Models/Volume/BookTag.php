<?php

namespace App\Models\Volume;

use App\Models\Model;
use App\Models\Volume\Book;

class BookTag extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'book_id', 'tag',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'book_tags';


    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the book this tag belongs to.
     */
    public function book() {
        return $this->belongsTo('App\Models\Volume\Book');
    }

     /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'">'.$this->tag.'</a>';
    }


    public function getUrlAttribute() {
        return url('world/' . __('volumes.library') . '?tags[]=' . $this->tag);
    }


}