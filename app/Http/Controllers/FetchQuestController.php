<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Services\FetchQuestService;
use Auth;
use Illuminate\Http\Request;
use App\Models\FetchQuest\FetchQuest;

class FetchQuestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Fetch Quest Controller
    |--------------------------------------------------------------------------
    |
    | Does... things
    |
     */

    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        $fetches = FetchQuest::active()->get();

        return view('fetchquests.fetch', [
            'fetches' => $fetches,
        ]);
    }

    /**
     * show the fetch modal
     *
     * @param mixed      $pet_id
     * @param mixed|null $id
     */
    public function getFetchQuest($id) {
        $fetch = FetchQuest::active()->find($id);
        if (!$fetch) {
            abort(404);
        }

        return view('fetchquests._submit_fetch', [
            'fetch'     => $fetch,
        ]);
    }

    /**
     * Completes a fetch quest
     *
     * @param  integer  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postFetchQuest(Request $request, FetchQuestService $service, $id)
    {
        if ($service->completeFetchQuest($request->only(['stack_id', 'stack_quantity']), Auth::user(), $id)) {
            flash('Fetch quest handed in succesfully!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }
}
