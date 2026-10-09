<?php

namespace App\Models\Carrier;

use App\Models\Marking\Marking;
use App\Models\Model;

class Carrier extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'carriers';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'                => 'required|between:3,100',
        'description'         => 'nullable',
        'image'               => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'                => 'required|between:3,100',
        'description'         => 'nullable',
        'image'               => 'mimes:png',
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

    /**
     * Scope a query to show only visible carriers.
     *
     * @param \Illuminate\Datacarrier\Eloquent\Builder $query
     * @param mixed|null                               $user
     *
     * @return \Illuminate\Datacarrier\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-carrier">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/markings/carriers';
    }

    /**
     * Gets the key for the carrier.
     *
     * @return string
     */
    public function getBaseKeyName() {
        return str_replace(' ', '_', strtolower($this->name));
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->id.'-carrier.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->id) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('design-hub/carriers/'.$this->slug);
    }

    /**
     * Gets the URL for a masterlist search of characters in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('masterlist?carriers[]='.$this->name);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/carrier/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**
     * Gets the attached markings in a list.
     *
     * @return string
     */
    public function getAttachedMarkingsAttribute() {
        $attachments = MarkingCarrier::where('carrier_id', $this->id)->pluck('marking_id');
        $markings = Marking::whereIn('id', $attachments)->pluck('name', 'slug')->toArray();

        $row = [];

        foreach ($markings as $slug => $name) {
            $row[] = '<a href="'.url('design-hub/marking/'.$slug).'">'.$name.'</a>';
        }

        return implode(', ', $row);
    }
}
