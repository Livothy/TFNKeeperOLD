<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAds;
use App\Services\UserAdsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAdsController extends Controller {
    /**
     * Shows the user ads index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.user_ads.user_ads', [
            'user_ads' => UserAds::orderBy('created_at', 'DESC')->paginate(20),
        ]);
    }

    /**
     * Shows the edit user ads page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditUserAds($id) {
        $user_ads = UserAds::find($id);
        if (!$user_ads) {
            abort(404);
        }

        return view('admin.user_ads.edit_user_ads', [
            'user_ads' => $user_ads,
        ]);
    }

    /**
     * Edits a user ad.
     *
     * @param App\Services\UserAdsService $service
     * @param int|null                    $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditUserAds(Request $request, UserAdsService $service, $id = null) {
        $id ? $request->validate(UserAds::$updateRules) : $request->validate(UserAds::$createRules);
        $data = $request->only([
            'text',
        ]);
        if ($id && $service->updateUserAds(UserAds::find($id), $data, Auth::user())) {
            flash('User advertisement has been updated successfully.')->success();
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

        return view('admin.user_ads._delete_user_ads', [
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
        if ($id && $service->deleteUserAds(UserAds::find($id))) {
            flash('User advertisement has been deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/user_ads');
    }
}
