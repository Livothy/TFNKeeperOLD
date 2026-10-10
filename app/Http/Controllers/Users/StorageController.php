<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;

use DB;
use Auth;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\User\UserStorage;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Services\InventoryManager;
use App\Services\StorageManager;


use App\Http\Controllers\Controller;

class StorageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Storage Controller
    |--------------------------------------------------------------------------
    |
    | Handles storage of items and other objects.
    |
    */

    /**
     * Show the safety deposit box.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $user = Auth::user();

        $query = UserStorage::where('user_id',$user->id);

        $sort = $request->only(['sort']);
        switch(isset($sort['sort']) ? $sort['sort'] : null) {
            default: case 'newest':
                $query->orderBy('created_at', 'DESC');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'ASC');
                break;
        }

        $sum = $query->sum('count');

        if($query->count()) {
            $query = $query->get()->groupBy('storable_type')->transform(function($item, $k) {
            return $item->groupBy('storable_id');
            })->first();
        }

        return view('home.storage', [
            'storages'  => $query->paginate(30),
            'sum'   => $sum,
        ]);
    }


    /**
     * Withdraws a stack from the storage
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\StorageManager    $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postWithdraw(Request $request, StorageManager $service)
    {
        $data = $request->only(['remove', 'remove_one', 'remove_all']);
        if($service->withdrawStack(Auth::user(), $data)) {
            flash('Storage object(s) retrieved successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

}
