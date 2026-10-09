<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\UserAds;
use App\Services\UserAdsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAdsController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | User Ads Controller
    |--------------------------------------------------------------------------
    |
    | Displays user ads by users.
    |
    */

    /**
     * Shows the user ads index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserAdsIndex() {
        return view('user_ads.index', [
            'user_ads' => UserAds::orderBy('updated_at', 'DESC')->paginate(10),
        ]);
    }

    /**
     * Shows a user ads ad.
     *
     * @param int         $id
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserAds($id, $slug = null) {
        $user_ads = UserAds::where('id', $id)->first();
        if (!$user_ads) {
            abort(404);
        }

        return view('user_ads.user_ads', ['user_ads' => $user_ads]);
    }

    /**
     * Shows the create user ads page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateUserAds() {
        return view('user_ads.create_user_ads', [
            'user_ads' => new UserAds,
        ]);
    }

    /**
     * Creates a user ads page.
     *
     * @param App\Services\UserAdsService $service
     * @param int|null                    $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditUserAds(Request $request, UserAdsService $service, $id = null) {
        $request->validate(UserAds::$createRules);

        $data = $request->only(['text']);

        $data['text'] = strip_tags($data['text']);

        if ($service->createUserAds($data, Auth::user())) {
            flash('Your ad has been created successfully.')->success();

            return redirect()->to('user_ads');
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the user ads deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteUserAds($id) {
        $user_ads = UserAds::find($id);
        if (!$user_ads) {
            abort(404);
        }

        if (!(Auth::user()->hasPower('manage_user_ads') || Auth::user()->id == $user_ads->user_id)) {
            abort(403);
        }

        return view('user_ads._delete_user_ads', [
            'user_ads' => $user_ads,
        ]);
    }

    /**
     * Deletes a user ad.
     *
     * @param App\Services\UserAdsService $service
     * @param int                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteUserAds(Request $request, UserAdsService $service, $id) {
        $user_ads = UserAds::find($id);
        if (!$user_ads) {
            abort(404);
        }

        if (!(Auth::user()->hasPower('manage_user_ads') || Auth::user()->id == $user_ads->user_id)) {
            abort(403);
        }

        if ($service->deleteUserAds($user_ads)) {
            flash('User advertisement has been deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('user_ads');
    }
}
