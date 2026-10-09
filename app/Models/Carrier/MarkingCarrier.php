<?php

namespace App\Models\Carrier;

use App\Models\Model;

class MarkingCarrier extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'marking_id', 'carrier_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'marking_carriers';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'marking_id'          => 'required',
        'carrier_id'          => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'marking_id'          => 'required',
        'carrier_id'          => 'required',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort carriers in alphabetical order.
     *
     * @param \Illuminate\Datacarrier\Eloquent\Builder $query
     * @param bool                                     $reverse
     *
     * @return \Illuminate\Datacarrier\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort carriers by newest first.
     *
     * @param \Illuminate\Datacarrier\Eloquent\Builder $query
     *
     * @return \Illuminate\Datacarrier\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort carriers oldest first.
     *
     * @param \Illuminate\Datacarrier\Eloquent\Builder $query
     *
     * @return \Illuminate\Datacarrier\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/
}
