<?php

namespace App\Models\FetchQuest;

use App\Models\Model;

class FetchQuest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'questgiver_name', 'description', 'parsed_description', 'is_active', 'has_image', 'cooldown', 'fetch_item', 'fetch_category', 'exceptions', 'currency_id', 'extras',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fetch_quests';

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|between:3,100',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,100',
    ];

    /**********************************************************************************************

    RELATIONS

     **********************************************************************************************/

    /**
     * Get the user's items.
     */
    public function fetchItem()
    {
        return $this->belongsTo('App\Models\Item\Item', 'fetch_item');
    }

    /**
     * Get the user's items.
     */
    public function fetchCurrency()
    {
        return $this->belongsTo('App\Models\Currency\Currency', 'currency_id');
    }

    public function exceptions()
    {
        return $this->hasMany('App\Models\FetchQuest\FetchException');
    }

    public function rewards()
    {
        return $this->hasMany('App\Models\FetchQuest\FetchReward');
    }

    /**
     * Scope a query to show only visible features.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $withHidden = 0)
    {
        if ($withHidden) {
            return $query;
        }
        return $query->where('is_active', 1);
    }

    /**********************************************************************************************

    ACCESSORS

     **********************************************************************************************/

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/fetch-quests';
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
     * Get the extras attribute as an associative array.
     *
     * @return array
     */
    public function getExtrasAttribute()
    {
        if (!$this->id) {
            return null;
        }

        return json_decode($this->attributes['extras'], true);
    }

    /**
     * Creates a rewards string from an asset array.
     *
     * @param array $array
     *
     * @return string
     */
    public function fetchRewards()
    {
        if (!$this->rewards()) {
            return null;
        }

        $rewards = [];
        foreach ($this->rewards as $reward) {
            $name = $reward->reward->displayName;
            $rewards[] = $name;
        }
        return implode(', ', array_slice($rewards, 0, count($rewards) - 1)) . (count($rewards) > 2 ? ', and ' : ' and ') . end($rewards);
    }

}
