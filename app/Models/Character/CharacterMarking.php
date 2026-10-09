<?php

namespace App\Models\Character;

use App\Models\Marking\Marking;
use App\Models\Model;

class CharacterMarking extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'marking_id', 'code', 'order', 'is_dominant', 'data', 'base_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_markings';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['marking'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the marking associated with this record.
     */
    public function marking() {
        return $this->belongsTo(Marking::class, 'marking_id');
    }
}
