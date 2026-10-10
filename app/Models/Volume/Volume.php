<?php

namespace App\Models\Volume;

use App\Models\Model;
use App\Models\User\UserVolume;
use Auth;

class Volume extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'has_image', 'description', 'parsed_description', 'is_visible', 'book_id', 'summary', 'is_global', 'authors', 'character_authors',
    ];

    protected $appends = ['image_url'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'volumes';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:volumes',
        'description' => 'nullable',
        'image' => 'mimes:png',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required',
        'description' => 'nullable',
        'image' => 'mimes:png',
    ];

    /**********************************************************************************************
    RELATIONS
     **********************************************************************************************/

    /**
     * Get the users who have this volume.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User\User', 'user_volumes')->withPivot('id');
    }

    /**
     * Get the prompts parent
     */
    public function book()
    {
        return $this->belongsTo('App\Models\Volume\Book', 'book_id');
    }

    /**********************************************************************************************
    SCOPES
     **********************************************************************************************/

    /**
     * Scope a query to sort items in alphabetical order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $reverse
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false)
    {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort items by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to show only visible volumes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null)
    {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************
    ACCESSORS
     **********************************************************************************************/

    /**
     * Gets the URL of the individual volume's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('world/' . __('volumes.library') . '/' . __('volumes.volume') . '/' . $this->id);
    }

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if (!$this->is_visible) {
            return '<i class="fas fa-eye-slash"></i> <a href="' . $this->idUrl . '" class="display-item">' . $this->volumePrefix() . $this->name . '</a>';
        }
        return '<a href="' . $this->idUrl . '" class="display-item">' . $this->volumePrefix() . $this->name . '</a>';

    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/volumes';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute()
    {
        return $this->id . '-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory . '/' . $this->imageFileName);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'volumes';
    }

    /**
     * global check for if any users have this volume
     *
     * @return bool
     */
    public function checkGlobal()
    {
        if (!$this->is_global) {
            return false;
        } elseif ($this->is_global && UserVolume::where('volume_id', $this->id)->exists()) {
            return true;
        }
        return false;
    }

    public function prevNextVolume($type)
    {
        if (!$this->book_id) {
            return null;
        }
        $query = Volume::visible(Auth::user() ?? null)->where('book_id', $this->book_id);

        if ($query->count()) {
            $query = $query->orderBy('sort', 'DESC')->get();

            if ($type == 'previous') {
                $vol = $query->where('sort', '<', $this->sort)->first();
            } else {
                $vol = $query->where('sort', '>', $this->sort)->last();
            }
        }

        if (!isset($vol)) {
            return null;
        }
        return $vol ?? null;
    }

    public function volumeName($user, $isAdmin = false)
    {
        if ($this->isUnlocked($user, $isAdmin)) {
            return $this->displayName;
        }
        return '<i>?????</i>';

    }

    public function volumeSummary($user, $isAdmin = false)
    {
        if ($this->isUnlocked($user, $isAdmin)) {
            return $this->summary;
        }
        return '<i>?????</i>';

    }

    public function volumeDesc($user, $isAdmin = false)
    {
        if ($this->isUnlocked($user, $isAdmin)) {
            return $this->parsed_description;
        }
        return '<i>?????</i>';

    }

    public function isUnlocked($user, $isAdmin = false)
    {
        if ($isAdmin || $user && $user->hasVolume($this->id) || $this->checkGlobal() || $this->book && $this->book->is_public) {
            return true;
        }
        return false;

    }

    /**
     * Get the chapter/number prefix
     *
     * @return string
     */
    public function volumePrefix()
    {
        if (!$this->book_id) {
            return null;
        }

        $num = $this->sort + 1;
        switch ($this->book->numeric_prefix) {
            case '0':
                $numeric = null;
                break;
            case '1':
                $numeric = $num;
                break;
            case '2':
                $numeric = $this->numberToRomanRepresentation($num);
                break;
        }
        if (is_null($numeric) && !$this->book->text_prefix) {
            return null;
        }

        if (!is_null($numeric) && $this->book->text_prefix) {
            //just getting a space between the prefices
            return $this->book->text_prefix . ' '. $numeric . ': ';
        }

        return $this->book->text_prefix . $numeric . ': ';
    }

    /**
     * Get a roman numeral for the sort number
     * https://stackoverflow.com/questions/14994941/numbers-to-roman-numbers-with-php
     *
     * @return string
     */
    public function numberToRomanRepresentation($number)
    {
        $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $returnValue = '';
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }
        return $returnValue;
    }
}
