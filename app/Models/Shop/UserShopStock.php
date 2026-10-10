<?php

namespace App\Models\Shop;

use App\Models\Model;

class UserShopStock extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_shop_id', 'item_id', 'currency_id', 'cost', 'quantity', 'data', 'stock_type', 'is_visible',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_shop_stock';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the item being stocked.
     */
    public function item() {
        return $this->belongsTo('App\Models\Item\Item');
    }

    /**
     * Get the shop that holds this item.
     */
    public function shop() {
        return $this->belongsTo(UserShop::class, 'user_shop_id');
    }

    /**
     * Get the currency the item must be purchased with.
     */
    public function currency() {
        return $this->belongsTo('App\Models\Currency\Currency');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scopes visible stock.
     *
     * @param mixed $query
     */
    public function scopeActive($query) {
        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute() {
        return json_decode($this->attributes['data'] ?? '', true) ?? [];
    }

    /**
     * Checks if the stack is transferrable.
     *
     * @return bool
     */
    public function getIsTransferrableAttribute() {
        return !isset($this->data['disallow_transfer']) && $this->item && $this->item->allow_transfer;
    }

    /**
     * Makes the cost an integer for display.
     *
     * @return int
     */
    public function getDisplayCostAttribute() {
        return (int) $this->cost;
    }
}
