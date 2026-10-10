<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Currency\Currency;
use App\Models\FetchQuest\FetchQuest;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;
use App\Services\FetchQuestService;
use Auth;
use Illuminate\Http\Request;

class FetchQuestController extends Controller
{

    /**********************************************************************************************

    FETCH QUESTS

     **********************************************************************************************/

    /**
     * Shows the fetchquest index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFetchQuestIndex()
    {
        $query = FetchQuest::query();
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%' . $data['name'] . '%');
        }

        return view('admin.fetch_quests.fetch_quests', [
            'fetchquests' => $query->paginate(20),
        ]);
    }

    /**
     * Shows the create fetchquest page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFetchQuest()
    {
        return view('admin.fetch_quests.create_edit_fetch_quest', [
            'fetchquest' => new FetchQuest,
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'categories' => ItemCategory::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
            'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'fetchCategories' => ['none' => 'No Category'] + ItemCategory::orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit fetchquest page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFetchQuest($id)
    {
        $fetchquest = FetchQuest::find($id);
        if (!$fetchquest) {
            abort(404);
        }

        return view('admin.fetch_quests.create_edit_fetch_quest', [
            'fetchquest' => $fetchquest,
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'categories' => ItemCategory::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('sort_user', 'DESC')->pluck('name', 'id'),
            'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
            'fetchCategories' => ['none' => 'No Category'] + ItemCategory::orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an fetchquest.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  App\Services\FetchQuestService  $service
     * @param  int|null                    $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFetchQuest(Request $request, FetchQuestService $service, $id = null)
    {
        $id ? $request->validate(FetchQuest::$updateRules) : $request->validate(FetchQuest::$createRules);
        $data = $request->only([
            'name', 'questgiver_name', 'description', 'is_active', 'image', 'has_image', 'cooldown', 'fetch_item', 'fetch_category',
            'exception_type', 'exception_id', 'currency_id', 'reward_min_min', 'reward_min_max', 'reward_max_min', 'reward_max_max', 'rewardable_type', 'rewardable_id', 'quantity',
        ]);
        if ($id && $service->updateFetchQuest(FetchQuest::find($id), $data, Auth::user())) {
            flash('Fetch Quest updated successfully.')->success();
        } else if (!$id && $fetchquest = $service->createFetchQuest($data, Auth::user())) {
            flash('Fetch Quest created successfully.')->success();
            return redirect()->to('admin/data/fetch-quests/edit/' . $fetchquest->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Gets the fetchquest deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteFetchQuest($id)
    {
        $fetchquest = FetchQuest::find($id);
        return view('admin.fetch_quests._delete_fetch_quest', [
            'fetchquest' => $fetchquest,
        ]);
    }

    /**
     * Deletes a fetchquest.
     *
     * @param  \Illuminate\Http\Request    $request
     * @param  App\Services\FetchQuestService  $service
     * @param  int                         $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFetchQuest(Request $request, FetchQuestService $service, $id)
    {
        if ($id && $service->deleteFetchQuest(FetchQuest::find($id))) {
            flash('FetchQuest deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->to('admin/data/fetch-quests');
    }

}
