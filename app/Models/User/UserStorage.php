<?php

namespace App\Models\User;

use App\Models\Model;

class UserStorage extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'count', 'storable_id', 'storable_type', 'storer_id', 'storer_type', 'data',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_storage';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the stack.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }
    /**
     * Get the item associated with this item stack.
     * Specifically vague to allow for extensions. eg. Pet or Currency
     */
    public function storable()
    {
        return $this->belongsTo($this->storable_type, 'storable_id');  // Should return null if nonexisting
    }

    /**
     * Get the inventory spot who owns the stack.
     * Specifically vague to allow for extensions. eg. UserPet or UserCurrency
     */
    public function storer()
    {
        return $this->belongsTo($this->storer_type, 'storer_type');  // Should return null if nonexisting
    }


    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getImageUrlAttribute()
    {
        switch($this->storable_type){
            default: case 'App\Models\Item\Item': return $this->storable->imageUrl;
        }
    }

    /**
     * Get the name of the object
     *
     * @return array
     */
    public function getNameAttribute()
    {
        switch($this->storable_type){
            default: case 'App\Models\Item\Item': return $this->storable->name;
        }
    }

    /**
     * Get the name of the object
     *
     * @return array
     */
    public function getDisplayNameAttribute()
    {
        switch($this->storable_type){
            default: case 'App\Models\Item\Item': return $this->storable->displayName;
        }
    }

    public function storageDetails($storable){

        $types = [
            'UserItem'      => [    'App\Models\Item\Item',         'item_id'       ],
            'UserCurrency'  => [    'App\Models\Currency\Currency', 'currency_id'   ],
        ];

        $storage = $types[class_basename($storable)];

        $id_name = $storage[1];

        $type = isset($storage) ? $storage[0] : null;

        $id = $storable->$id_name;

        return [ 'type' => $type, 'id' => $id];
    }

    /**
     * Gets the stack's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        switch($this->storage_type) {
            default: case "App/Models/User/UserItem":       return 'user_items';
            case "App/Models/User/UserCurrency":            return 'user_currencies';

            // Claymores + Companions
            case "App/Models/User/UserPet":                 return 'user_pets';
            case "App/Models/User/UserGear":                return 'user_gears';
            case "App/Models/User/UserWeapons":             return 'user_weapons';
        }

    }
}
