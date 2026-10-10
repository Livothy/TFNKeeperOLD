<?php

namespace App\Models\Volume;

use App\Models\Model;
use App\Models\Volume\BookTag;
use Auth;

class Book extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'has_image', 'description', 'parsed_description', 'is_visible', 'summary', 'bookshelf_id', 'has_next', 'is_public', 'sort','numeric_prefix','text_prefix'
    ];

    protected $appends = ['image_url'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'books';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:books',
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
     * get the volumes attached to the book
     */
    public function volumes()
    {
        return $this->hasMany('App\Models\Volume\Volume')->where('book_id', $this->id)->visible(Auth::user() ?? null)->orderBy('sort', 'ASC');
    }

    /**
     * Get the book's bookshelf
     */
    public function bookshelf()
    {
        return $this->belongsTo('App\Models\Volume\Bookshelf', 'bookshelf_id');
    }

    /**
     * Get the authors of the book
     */
    public function authors()
    {
        return $this->hasMany('App\Models\Volume\BookAuthor', 'book_id');
    }

    /**
     * Get this page's tags.
     */
    public function tags()
    {
        return $this->hasMany('App\Models\Volume\BookTag');
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
     * Scope a query to show only visible books.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
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
     * Gets the URL of the individual book's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute()
    {
        return url('world/' . __('volumes.library') . '/' . __('volumes.book') . '/' . $this->id);
    }

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if (!$this->is_visible) {
            return '<i class="fas fa-eye-slash"></i> <a href="' . $this->idUrl . '" class="display-item">' . $this->name . '</a>';
        }
        return '<a href="' . $this->idUrl . '" class="display-item">' . $this->name . '</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/books';
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
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getNextImageFileNameAttribute()
    {
        return $this->id . '-next-image.png';
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getNextImageUrlAttribute()
    {
        if (!$this->has_next) {
            return 'https://placehold.co/100x50';
        }

        return asset($this->imageDirectory . '/' . $this->nextImageFileName);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'books';
    }

    /**
     * Gets all extant tags. Used for page editing.
     *
     * @return array
     */
    public function getAllTags()
    {
        $query = BookTag::pluck('tag')->unique();

        $tags = [];
        foreach ($query as $tag) {
            $tags[] = ['tag' => $tag];
        }

        return $tags;
    }

    /**
     * Get the page's tags for use by the tag entry field.
     *
     * @return string
     */
    public function getEntryTagsAttribute()
    {
        $tags = [];
        foreach ($this->tags()->pluck('tag') as $tag) {
            $tags[] = ['tag' => $tag];
        }

        return json_encode($tags);
    }

}
