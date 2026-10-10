<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Shop\UserShop;
use App\Models\Shop\UserShopStock;
use App\Models\User\UserItem;
use App\Services\UserShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserShopController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | User / Shop Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of user shops and shop stock.
    |
    */

    /**
     * Shows the user's own shops.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserIndex() {
        return view('home.user_shops.my_shops', [
            'shops' => UserShop::where('user_id', Auth::user()->id)->orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create shop page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateShop() {
        return view('home.user_shops.create_edit_shop', [
            'shop' => new UserShop,
        ]);
    }

    /**
     * Shows the edit shop page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditShop($id) {
        $shop = UserShop::find($id);
        if (!$shop || !$shop->canManage(Auth::user())) {
            abort(404);
        }

        return view('home.user_shops.create_edit_shop', [
            'shop'       => $shop,
            'currencies' => UserShopService::sellableCurrencies()->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
            'stocks'     => UserShopStock::with('item')->where('user_shop_id', $shop->id)->where('quantity', '>', 0)->orderBy('id', 'DESC')->paginate(30),
        ]);
    }

    /**
     * Creates or edits a shop.
     *
     * @param int|null $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditShop(Request $request, UserShopService $service, $id = null) {
        $id ? $request->validate(UserShop::$updateRules) : $request->validate(UserShop::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_active',
        ]);
        if ($id && $service->updateShop(UserShop::find($id), $data, Auth::user())) {
            flash('Shop updated successfully.')->success();
        } elseif (!$id && $shop = $service->createShop($data, Auth::user())) {
            flash('Shop created successfully.')->success();

            return redirect()->to('user-shops/edit/'.$shop->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the shop deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteShop($id) {
        $shop = UserShop::find($id);

        return view('home.user_shops._delete_shop', [
            'shop' => $shop && $shop->canManage(Auth::user()) ? $shop : null,
        ]);
    }

    /**
     * Deletes a shop.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteShop(Request $request, UserShopService $service, $id) {
        if ($service->deleteShop(UserShop::find($id), Auth::user())) {
            flash('Shop deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

            return redirect()->back();
        }

        return redirect()->to('user-shops');
    }

    /**
     * Sorts the user's shops.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortShop(Request $request, UserShopService $service) {
        if ($service->sortShop($request->get('sort'), Auth::user())) {
            flash('Shop order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Edits a shop's stock (price, currency, visibility, removal).
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditStock(Request $request, UserShopService $service, $id) {
        if ($service->editStock(UserShop::find($id), $request->only(['stock_id', 'quantity', 'is_visible', 'cost', 'currency_id']), Auth::user())) {
            flash('Stock edited successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the user's purchase history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPurchaseHistory() {
        return view('home.user_shops.purchase_history', [
            'logs' => Auth::user()->getUserShopLogs(0),
        ]);
    }

    /**
     * Shows a shop's sale history.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getShopHistory($id) {
        $shop = UserShop::find($id);
        if (!$shop || !$shop->canManage(Auth::user())) {
            abort(404);
        }

        return view('home.user_shops.sale_history', [
            'logs' => $shop->getShopLogs(0),
            'shop' => $shop,
        ]);
    }

    /**
     * Shows the item search page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItemSearch(Request $request) {
        $items = Item::whereIn('id', array_map('intval', (array) $request->get('item_ids')))->released()->get();
        $category = ItemCategory::visible(Auth::user())->find($request->get('item_category_id'));
        if ($category) {
            $items = $items->merge(Item::where('item_category_id', $category->id)->released()->get());
        }

        $shopItems = null;
        if ($items->count()) {
            $shopItems = UserShopStock::with('item', 'currency', 'shop.user')->whereIn('item_id', $items->pluck('id')->toArray())
                ->where('stock_type', 'Item')->where('is_visible', 1)->where('quantity', '>', 0)
                ->whereHas('shop', function ($query) {
                    $query->visible();
                })->orderBy('cost', 'ASC')->get()
                ->sortBy(function ($stock) {
                    return $stock->item->name;
                });
        }

        return view('home.user_shops.search_items', [
            'searched_items' => $items->count() ? $items : null,
            'items'          => Item::released()->orderBy('name')->pluck('name', 'id'),
            'shopItems'      => $shopItems,
            'categories'     => ItemCategory::visible(Auth::user())->orderBy('name')->pluck('name', 'id'),
            'category'       => $category,
        ]);
    }

    /**
     * Shows the quickstock page (stock a shop from the inventory).
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQuickstock() {
        return view('home.quickstock', [
            'user'        => Auth::user(),
            'item_filter' => Item::orderBy('name')->released()->get()->keyBy('id'),
            'inventory'   => UserItem::with('item')->whereNull('deleted_at')->where('count', '>', 0)->where('user_id', Auth::user()->id)->get(),
            'page'        => 'quickstock',
            'categories'  => ItemCategory::visible(Auth::user())->orderBy('sort', 'DESC')->get(),
            'shopOptions' => UserShop::where('user_id', Auth::user()->id)->orderBy('sort', 'DESC')->pluck('name', 'id'),
        ]);
    }

    /**
     * Moves items from the inventory into one of the user's shops.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postQuickstock(Request $request, UserShopService $service) {
        if ($service->stockShop(Auth::user(), UserShop::find($request->get('shop_id')), $request->only(['stack_id', 'stack_quantity']))) {
            flash('Items stocked successfully. New stock stays hidden until you set its price and make it visible.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
