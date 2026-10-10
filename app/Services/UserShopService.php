<?php

namespace App\Services;

use App\Models\Currency\Currency;
use App\Models\Shop\UserShop;
use App\Models\Shop\UserShopStock;
use App\Models\User\UserItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Settings;

class UserShopService extends Service {
    /**
     * Currencies that may be used to price user shop stock:
     * held by users and transferable between users.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function sellableCurrencies() {
        return Currency::where('is_user_owned', 1)->where('allow_user_to_user', 1);
    }

    /**********************************************************************************************

        SHOPS

    **********************************************************************************************/

    /**
     * Creates a new shop.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|UserShop
     */
    public function createShop($data, $user) {
        DB::beginTransaction();

        try {
            $limit = (int) Settings::get('user_shop_limit');
            if ($limit > 0 && UserShop::where('user_id', $user->id)->count() >= $limit) {
                throw new \Exception('You have already created the maximum number of shops.');
            }

            $data['user_id'] = $user->id;
            $data = $this->populateShopData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $shop = UserShop::create($data);

            if ($image) {
                $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);
            }

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a shop.
     *
     * @param UserShop              $shop
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|UserShop
     */
    public function updateShop($shop, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$shop || !$shop->canManage($user)) {
                throw new \Exception('Invalid shop selected.');
            }
            if (UserShop::where('name', $data['name'])->where('id', '!=', $shop->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateShopData($data, $shop);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $shop->update($data);

            if ($image) {
                $this->handleImage($image, $shop->shopImagePath, $shop->shopImageFileName);
            }

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a shop. The shop must be empty first.
     *
     * @param UserShop              $shop
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function deleteShop($shop, $user) {
        DB::beginTransaction();

        try {
            if (!$shop || !$shop->canManage($user)) {
                throw new \Exception('Invalid shop selected.');
            }
            if ($shop->stock()->where('quantity', '>', 0)->exists()) {
                throw new \Exception('This shop currently has items stocked. Please remove them and try again.');
            }

            $shop->stock()->delete();
            if ($shop->has_image && file_exists($shop->shopImagePath.'/'.$shop->shopImageFileName)) {
                $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName);
            }
            $shop->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Sorts the user's own shops.
     *
     * @param string                $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function sortShop($data, $user) {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', (string) $data));

            foreach ($sort as $key => $s) {
                UserShop::where('user_id', $user->id)->where('id', (int) $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        STOCK

    **********************************************************************************************/

    /**
     * Moves items from a user's inventory into one of their own shops.
     * New stock starts hidden until the owner sets a price.
     *
     * @param \App\Models\User\User $user
     * @param UserShop|null         $shop
     * @param array                 $data stack_id[] and stack_quantity[stack id]
     *
     * @return bool
     */
    public function stockShop($user, $shop, $data) {
        DB::beginTransaction();

        try {
            if (!$user->hasAlias) {
                throw new \Exception('You need to have a linked social media account before you can perform this action.');
            }
            // Only the owner can stock a shop, so staff can't push their own items into someone else's shop
            if (!$shop || $shop->user_id != $user->id) {
                throw new \Exception('Invalid shop selected.');
            }
            if (!isset($data['stack_id']) || !is_array($data['stack_id']) || !count($data['stack_id'])) {
                throw new \Exception('No items selected.');
            }

            foreach (array_unique($data['stack_id']) as $stackId) {
                $stack = UserItem::with('item')->where('id', (int) $stackId)->lockForUpdate()->first();
                if (!$stack || $stack->user_id != $user->id || !$stack->item) {
                    throw new \Exception('Invalid item selected.');
                }

                $quantity = $data['stack_quantity'][$stackId] ?? null;
                if (!is_numeric($quantity) || (int) $quantity != $quantity || (int) $quantity < 1) {
                    throw new \Exception('Invalid quantity selected for '.$stack->item->name.'.');
                }
                $quantity = (int) $quantity;
                if ($quantity > $stack->availableQuantity) {
                    throw new \Exception('Quantity to stock exceeds the available count of '.$stack->item->name.' (items held in trades, design updates or submissions can\'t be stocked).');
                }
                if (!$stack->isTransferrable) {
                    throw new \Exception($stack->item->name.' cannot be transferred, so it cannot be sold.');
                }
                if (!$stack->item->canUserSell) {
                    throw new \Exception($stack->item->name.' cannot be sold in user shops.');
                }

                $shopStock = UserShopStock::where('user_shop_id', $shop->id)->where('item_id', $stack->item_id)->where('stock_type', 'Item')->lockForUpdate()->first();
                if (!$shopStock) {
                    $shopStock = UserShopStock::create([
                        'user_shop_id' => $shop->id,
                        'item_id'      => $stack->item_id,
                        'currency_id'  => self::sellableCurrencies()->orderBy('sort_user', 'DESC')->value('id') ?? 0,
                        'cost'         => 0,
                        'quantity'     => 0,
                        'data'         => json_encode(['data' => '', 'notes' => '']),
                        'stock_type'   => 'Item',
                        'is_visible'   => 0,
                    ]);
                }

                $stack->count -= $quantity;
                $stack->save();
                $shopStock->quantity += $quantity;
                $shopStock->save();

                if (!(new InventoryManager)->createLog($user->id, 'User', $shop->id, 'Shop', $stack->id, 'User → Shop Transfer', '', $stack->item_id, $quantity)) {
                    throw new \Exception('Failed to create log.');
                }
            }

            $shop->update(['updated_at' => Carbon::now()]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Edits a shop's stock: price, currency, visibility, and withdrawing
     * items back to the shop owner's inventory.
     *
     * @param UserShop              $shop
     * @param array                 $data keyed by stock id: cost[], currency_id[], is_visible[], quantity[]
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function editStock($shop, $data, $user) {
        DB::beginTransaction();

        try {
            if (!$shop || !$shop->canManage($user)) {
                throw new \Exception('Invalid shop selected.');
            }
            if (!isset($data['stock_id']) || !is_array($data['stock_id'])) {
                throw new \Exception('No stock selected.');
            }
            $currencies = self::sellableCurrencies()->pluck('id')->toArray();

            foreach (array_unique($data['stock_id']) as $stockId) {
                $stock = UserShopStock::with('item')->where('user_shop_id', $shop->id)->where('id', (int) $stockId)->lockForUpdate()->first();
                if (!$stock) {
                    throw new \Exception('Invalid stock selected.');
                }
                $name = $stock->item ? $stock->item->name : 'an item';

                $cost = $data['cost'][$stockId] ?? null;
                if (!is_numeric($cost) || (int) $cost != $cost || (int) $cost < 0 || (int) $cost > self::MAX_COST) {
                    throw new \Exception('The cost of '.$name.' must be a whole number between 0 and '.self::MAX_COST.'.');
                }
                $currencyId = (int) ($data['currency_id'][$stockId] ?? 0);
                if (!in_array($currencyId, $currencies)) {
                    throw new \Exception('The currency selected for '.$name.' cannot be used in user shops.');
                }

                $stock->update([
                    'is_visible'  => isset($data['is_visible'][$stockId]) ? 1 : 0,
                    'cost'        => (int) $cost,
                    'currency_id' => $currencyId,
                ]);

                $quantity = $data['quantity'][$stockId] ?? 0;
                if (!is_numeric($quantity) || (int) $quantity != $quantity || (int) $quantity < 0 || (int) $quantity > $stock->quantity) {
                    throw new \Exception('Invalid quantity to remove for '.$name.'.');
                }
                if ((int) $quantity > 0) {
                    $this->withdrawStock($shop, $stock, (int) $quantity);
                }
            }

            $shop->update(['updated_at' => Carbon::now()]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Adds items to a user's inventory and logs it, like
     * InventoryManager::creditItem, but without re-releasing items
     * that staff have since hidden.
     *
     * @param UserShop              $sender
     * @param \App\Models\User\User $recipient
     * @param string                $type
     * @param array                 $data
     * @param int                   $itemId
     * @param int                   $quantity
     */
    public function creditUserItem($sender, $recipient, $type, $data, $itemId, $quantity) {
        $encoded = json_encode($data);
        $stack = UserItem::where('user_id', $recipient->id)->where('item_id', $itemId)->where('data', $encoded)->first();
        if (!$stack) {
            $stack = UserItem::create(['user_id' => $recipient->id, 'item_id' => $itemId, 'data' => $encoded]);
        }
        $stack->count += $quantity;
        $stack->save();

        if (!(new InventoryManager)->createLog($sender->id, $sender->logType, $recipient->id, 'User', null, $type, $data['data'], $itemId, $quantity)) {
            throw new \Exception('Failed to create log.');
        }
    }

    /**
     * Moves stock from a shop back to the shop owner's inventory.
     * Must be called inside a transaction with the stock row locked.
     *
     * @param UserShop      $shop
     * @param UserShopStock $stock
     * @param int           $quantity
     */
    private function withdrawStock($shop, $stock, $quantity) {
        $owner = $shop->user;
        if (!$owner) {
            throw new \Exception('This shop has no owner.');
        }

        $this->creditUserItem($shop, $owner, 'Shop → User Transfer', ['data' => '', 'notes' => ''], $stock->item_id, $quantity);

        $stock->quantity -= $quantity;
        if ($stock->quantity <= 0) {
            $stock->delete();
        } else {
            $stock->save();
        }
    }

    /**
     * Processes user input for creating/updating a shop.
     *
     * @param array         $data
     * @param UserShop|null $shop
     *
     * @return array
     */
    private function populateShopData($data, $shop = null) {
        $data['parsed_description'] = isset($data['description']) && $data['description'] ? parse($data['description']) : null;
        $data['is_active'] = isset($data['is_active']) && $data['is_active'] ? 1 : 0;

        if (isset($data['remove_image'])) {
            if ($shop && $shop->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                if (file_exists($shop->shopImagePath.'/'.$shop->shopImageFileName)) {
                    $this->deleteImage($shop->shopImagePath, $shop->shopImageFileName);
                }
            }
            unset($data['remove_image']);
        }

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | User Shop Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of user shops and moving items
    | between a user's inventory and their shops.
    |
    */

    /**
     * Highest price a single item can be listed for.
     */
    public const MAX_COST = 1000000;
}
