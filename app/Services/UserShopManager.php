<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Shop\UserShop;
use App\Models\Shop\UserShopLog;
use App\Models\Shop\UserShopStock;
use Illuminate\Support\Facades\DB;

class UserShopManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | User Shop Manager
    |--------------------------------------------------------------------------
    |
    | Handles purchasing of items from user shops.
    |
    */

    /**
     * Buys an item from a user shop.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool|UserShop
     */
    public function buyStock($data, $user) {
        DB::beginTransaction();

        try {
            $quantity = $data['quantity'] ?? null;
            if (!is_numeric($quantity) || (int) $quantity != $quantity || (int) $quantity < 1) {
                throw new \Exception('Invalid quantity selected.');
            }
            $quantity = (int) $quantity;

            if (!$user->hasAlias) {
                throw new \Exception('You need to have a linked social media account before you can perform this action.');
            }

            // Check that the shop exists, is open and its owner is in good standing
            $shop = UserShop::with('user')->find((int) ($data['user_shop_id'] ?? 0));
            if (!$shop || !$shop->isOpen) {
                throw new \Exception('Invalid shop selected.');
            }
            if ($shop->user_id == $user->id) {
                throw new \Exception("You can't buy from a shop that you own!");
            }

            // Check that the stock exists, belongs to the shop and is on sale. Lock it so two buyers can't take the same stock.
            $shopStock = UserShopStock::where('id', (int) ($data['stock_id'] ?? 0))->where('user_shop_id', $shop->id)
                ->where('stock_type', 'Item')->where('is_visible', 1)->lockForUpdate()->first();
            if (!$shopStock || !$shopStock->item || !$shopStock->item->is_released) {
                throw new \Exception('Invalid item selected.');
            }
            if (!$shopStock->isTransferrable || !$shopStock->item->canUserSell) {
                throw new \Exception('This item can no longer be sold in user shops.');
            }
            if ($shopStock->quantity < $quantity) {
                throw new \Exception('There is insufficient stock to fulfill your request.');
            }

            // The currency must still be one players can trade with each other
            $currency = UserShopService::sellableCurrencies()->where('id', $shopStock->currency_id)->first();
            if (!$currency) {
                throw new \Exception('This item is priced in a currency that cannot be used in user shops.');
            }

            // Refuse if the owner changed the price after the buyer opened the item
            $cost = (int) $shopStock->cost;
            if (isset($data['cost']) && ((int) $data['cost'] != $cost || (int) ($data['currency_id'] ?? 0) != $currency->id)) {
                throw new \Exception('The price of this item has changed. Please check the new price and try again.');
            }
            $totalCost = $cost * $quantity;

            if ($totalCost > 0 && !(new CurrencyManager)->debitCurrency($user, null, 'User Shop Purchase', 'Purchased '.$quantity.' '.$shopStock->item->name.' from '.$shop->displayName, $currency, $totalCost)) {
                throw new \Exception('Not enough '.$currency->name.' to make this purchase.');
            }

            $shopStock->quantity -= $quantity;
            if ($shopStock->quantity <= 0) {
                $shopStock->delete();
            } else {
                $shopStock->save();
            }

            $shopLog = UserShopLog::create([
                'user_shop_id' => $shop->id,
                'user_id'      => $user->id,
                'currency_id'  => $currency->id,
                'cost'         => $cost,
                'item_id'      => $shopStock->item_id,
                'quantity'     => $quantity,
            ]);

            // Give the buyer the item
            (new UserShopService)->creditUserItem($shop, $user, 'Shop Purchase', [
                'data'  => 'Purchased from '.$shop->displayName.' by '.$user->displayName.' for '.$totalCost.' '.e($currency->name).'.',
                'notes' => 'Purchased '.format_date($shopLog->created_at),
            ], $shopStock->item_id, $quantity);

            // Pay the shop owner
            if ($totalCost > 0 && !(new CurrencyManager)->creditCurrency($user, $shop->user, 'User Shop Sale', 'Sold '.$quantity.' '.$shopStock->item->name.' in '.$shop->displayName, $currency, $totalCost)) {
                throw new \Exception('Failed to credit currency.');
            }

            Notifications::create('USER_SHOP_ITEM_SOLD', $shop->user, [
                'shop_id'           => $shop->id,
                'shop_name'         => e($shop->name),
                'item_name'         => e($shopStock->item->name),
                'item_quantity'     => $quantity,
                'currency_name'     => e($currency->name),
                'currency_quantity' => $totalCost,
            ]);

            return $this->commitReturn($shop);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
