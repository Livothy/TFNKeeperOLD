<?php

namespace App\Http\Controllers;

use App\Models\Currency\Currency;
use App\Models\Item\ItemCategory;
use App\Models\Shop\UserShop;
use App\Models\Shop\UserShopLog;
use App\Models\Shop\UserShopStock;
use App\Models\User\UserItem;
use App\Services\UserShopManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserShopController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | User Shop Controller
    |--------------------------------------------------------------------------
    |
    | Handles viewing the user shop index, shops and purchasing from shops.
    |
    */

    /**
     * Shows the user shop index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request) {
        $query = UserShop::visible(Auth::user())->with('user')->withCount('visibleStock');

        if ($request->get('name')) {
            $query->where('name', 'LIKE', '%'.$request->get('name').'%');
        }

        switch ($request->get('sort')) {
            default:
                $query->inRandomOrder();
                break;
            case 'alpha':
                $query->orderBy('name');
                break;
            case 'alpha-reverse':
                $query->orderBy('name', 'DESC');
                break;
            case 'newest':
                $query->orderBy('id', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('id', 'ASC');
                break;
            case 'update':
                $query->orderBy('updated_at', 'DESC');
                break;
            case 'update-reverse':
                $query->orderBy('updated_at', 'ASC');
                break;
        }

        return view('home.user_shops.index_shop', [
            'shops' => $query->paginate(30)->appends($request->query()),
        ]);
    }

    /**
     * Shows a shop.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getShop($id) {
        $shop = UserShop::with('user')->find($id);
        if (!$shop || (!$shop->isOpen && !$shop->canManage(Auth::user()))) {
            abort(404);
        }

        $categories = ItemCategory::visible(Auth::user())->orderBy('sort', 'DESC')->get();
        $query = $shop->displayStock()->released()->where(function ($query) use ($categories) {
            $query->whereIn('item_category_id', $categories->pluck('id')->toArray())
                ->orWhereNull('item_category_id');
        });
        $items = count($categories) ? $query->orderByRaw('FIELD(item_category_id,'.implode(',', $categories->pluck('id')->toArray()).')')->orderBy('name')->get()->groupBy('item_category_id') : $query->orderBy('name')->get()->groupBy('item_category_id');

        return view('home.user_shops.shop', [
            'shop'       => $shop,
            'categories' => $categories->keyBy('id'),
            'items'      => $items,
            'currencies' => Currency::whereIn('id', UserShopStock::where('user_shop_id', $shop->id)->pluck('currency_id')->toArray())->get()->keyBy('id'),
        ]);
    }

    /**
     * Gets the shop stock modal.
     *
     * @param int $id
     * @param int $stockId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getShopStock($id, $stockId) {
        $shop = UserShop::with('user')->find($id);
        if (!$shop || !$shop->isOpen) {
            abort(404);
        }
        $stock = UserShopStock::with('item', 'currency')->where('id', $stockId)->where('user_shop_id', $shop->id)
            ->where('is_visible', 1)->where('quantity', '>', 0)->first();
        if ($stock && (!$stock->item || !$stock->item->is_released)) {
            $stock = null;
        }

        return view('home.user_shops._stock_modal', [
            'shop'      => $shop,
            'stock'     => $stock,
            'userOwned' => $stock ? UserItem::where('user_id', Auth::user()->id)->where('item_id', $stock->item_id)->where('count', '>', 0)->get() : collect(),
        ]);
    }

    /**
     * Buys an item from a shop.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postBuy(Request $request, UserShopManager $service) {
        $request->validate(UserShopLog::$createRules);
        if ($service->buyStock($request->only(['stock_id', 'user_shop_id', 'quantity', 'cost', 'currency_id']), Auth::user())) {
            flash('Successfully purchased item.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
