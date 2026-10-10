<?php

namespace App\Models\Shop;

use App\Models\Model;

class UserShop extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'user_id', 'sort', 'has_image', 'description', 'parsed_description', 'is_active', 'updated_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_shops';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'        => 'required|unique:user_shops|between:3,25',
        'description' => 'nullable|max:10000',
        'image'       => 'nullable|mimes:png|max:2048',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'        => 'required|between:3,25',
        'description' => 'nullable|max:10000',
        'image'       => 'nullable|mimes:png|max:2048',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the shop stock.
     */
    public function stock() {
        return $this->hasMany(UserShopStock::class, 'user_shop_id')->orderBy('id', 'DESC');
    }

    /**
     * Get the shop stock (visible & non 0 quantity for public display).
     */
    public function visibleStock() {
        return $this->hasMany(UserShopStock::class, 'user_shop_id')->where('quantity', '>', 0)->where('is_visible', 1);
    }

    /**
     * Get the user who owns the shop.
     */
    public function user() {
        return $this->belongsTo('App\Models\User\User', 'user_id');
    }

    /**
     * Get the shop stock as items for display purposes.
     */
    public function displayStock() {
        return $this->belongsToMany('App\Models\Item\Item', 'user_shop_stock')->where('stock_type', 'Item')->withPivot('item_id', 'currency_id', 'cost', 'quantity', 'id', 'is_visible')->wherePivot('quantity', '>', 0)->wherePivot('is_visible', 1);
    }

    /**
     * Get the purchase logs for this shop.
     */
    public function buyers() {
        return $this->hasMany(UserShopLog::class, 'user_shop_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to show only active shops of users in good standing.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_inventories')) {
            return $query;
        }

        return $query->where('is_active', 1)->whereRelation('user', 'is_banned', 0)->whereRelation('user', 'is_deactivated', 0);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Gets the shop's log type for log creation.
     *
     * @return string
     */
    public function getLogTypeAttribute() {
        return 'Shop';
    }

    /**
     * Whether regular users can currently see and buy from this shop.
     *
     * @return bool
     */
    public function getIsOpenAttribute() {
        return $this->is_active && $this->user && !$this->user->is_banned && !$this->user->is_deactivated;
    }

    /**
     * Displays the shop's name, linked to its purchase page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return (!$this->is_active ? '<i class="fas fa-eye-slash mr-1"></i>' : '').'<a href="'.$this->url.'" class="display-shop">'.e($this->name).'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/usershops';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getShopImageFileNameAttribute() {
        return $this->id.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getShopImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getShopImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->shopImageFileName);
    }

    /**
     * Gets the URL of the shop's page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('user-shops/shop/'.$this->id);
    }

    /**
     * Gets the URL to edit the shop.
     *
     * @return string
     */
    public function getEditUrlAttribute() {
        return url('user-shops/edit/'.$this->id);
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Get the shop's sale logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getShopLogs($limit = 10) {
        $query = UserShopLog::where('user_shop_id', $this->id)->with('user')->with('item')->with('currency')->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        }

        return $query->paginate(30);
    }

    /**
     * Whether the given user may manage (edit, restock, delete) this shop.
     * Staff with the edit_inventories power can moderate any shop.
     *
     * @param \App\Models\User\User|null $user
     *
     * @return bool
     */
    public function canManage($user) {
        return $user && ($this->user_id == $user->id || $user->hasPower('edit_inventories'));
    }
}
