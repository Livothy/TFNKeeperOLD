<?php

namespace App\Models\Marking;

use App\Models\Model;
use App\Models\Rarity;
use App\Models\Species\Species;
use Illuminate\Support\Facades\DB;

class Marking extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug', 'species_id', 'rarity_id', 'name', 'marking_image_id', 'description', 'short_description', 'is_visible', 'recessive', 'dominant',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'markings';
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'slug'                => 'required',
        'species_id'          => 'nullable',
        'subtype_id'          => 'nullable',
        'rarity_id'           => 'required|exists:rarities,id',
        'name'                => 'required|between:3,100',
        'description'         => 'nullable',
        'short_description'   => 'nullable',
        'is_visible'          => 'nullable',
        'recessive'           => 'nullable',
        'dominant'            => 'nullable',
        'image'               => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'slug'                => 'required',
        'species_id'          => 'nullable',
        'subtype_id'          => 'nullable',
        'rarity_id'           => 'required|exists:rarities,id',
        'name'                => 'required|between:3,100',
        'description'         => 'nullable',
        'short_description'   => 'nullable',
        'is_visible'          => 'nullable',
        'recessive'           => 'nullable',
        'dominant'            => 'nullable',
        'image'               => 'mimes:png',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the rarity of this marking.
     */
    public function rarity() {
        return $this->belongsTo(Rarity::class);
    }

    /**
     * Get the species the marking belongs to.
     */
    public function species() {
        return $this->belongsTo(Species::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort markings in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort markings in species order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortSpecies($query) {
        $ids = Species::orderBy('sort', 'DESC')->pluck('id')->toArray();

        return count($ids) ? $query->orderBy(DB::raw('FIELD(species_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort markings in rarity order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortRarity($query, $reverse = false) {
        $ids = Rarity::orderBy('sort', $reverse ? 'ASC' : 'DESC')->pluck('id')->toArray();

        return count($ids) ? $query->orderBy(DB::raw('FIELD(rarity_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort markings by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort markings oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to show only visible markings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
        return '<a href="'.$this->url.'" class="display-marking">'.$this->name.'</a>'.($this->rarity ? ' ('.$this->rarity->displayName.')' : '');
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/markings';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->slug.'-thumb.png';
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
        if (!$this->slug) {
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
        return url('design-hub/marking/'.$this->slug);
    }

    /**
     * Gets the URL for a masterlist search of characters in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('masterlist?markings[]='.$this->name);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/markings/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'edit_data';
    }

    /**********************************************************************************************

        Other Functions

    **********************************************************************************************/

    public static function getDropdownItems($withHidden = 0) {
        $visibleOnly = 1;
        if ($withHidden) {
            $visibleOnly = 0;
        }

        if (config('lorekeeper.extensions.organised_traits_dropdown')) {
            $marking_rarities = Rarity::whereIn('id', self::select('rarity_id')->distinct()->get())->get();
            $sorted_marking_rarities = [];
            foreach ($marking_rarities as $marking_rarity) {
                $markings = self::where('is_visible', 1)->where('rarity_id', $marking_rarity->id)->orderBy('name')->pluck('name', 'id')->toArray();

                $sorted_marking_rarities[$marking_rarity->id] = [
                    $marking_rarity->name => $markings,
                ];
            }

            return $sorted_marking_rarities;
        } else {
            return self::where('is_visible', '>=', $visibleOnly)->orderBy('name')->pluck('name', 'id')->toArray();
        }
    }
}
