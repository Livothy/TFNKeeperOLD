<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Base\Base;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Base Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character bases & genetics.
    |
    */

    /**********************************************************************************************

        MARKINGS

    **********************************************************************************************/

    /**
     * Shows the base index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBasesIndex(Request $request) {
        $query = Base::query();
        $data = $request->only(['rarity_id', 'species_id', 'name']);

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.bases.bases', [
            'bases'   => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create base page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBase() {
        return view('admin.bases.create_edit_base', [
            'base'    => new Base,
        ]);
    }

    /**
     * Shows the edit base page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBase($id) {
        $base = Base::find($id);
        if (!$base) {
            abort(404);
        }

        return view('admin.bases.create_edit_base', [
            'base'    => $base,
        ]);
    }

    /**
     * Creates or edits a base.
     *
     * @param App\Services\BaseService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBase(Request $request, BaseService $service, $id = null) {
        $id ? $request->validate(Base::$updateRules) : $request->validate(Base::$createRules);
        $data = $request->only([
            'name', 'image', 'code', 'is_visible',
        ]);

        if ($id && $service->updateBase(Base::find($id), $data, Auth::user())) {
            flash('Base updated successfully.')->success();
        } elseif (!$id && $base = $service->createBase($data, Auth::user())) {
            flash('Base created successfully.')->success();

            return redirect()->to('admin/data/base/edit/'.$base->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the base deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBase($id) {
        $base = Base::find($id);

        return view('admin.bases._delete_base', [
            'base' => $base,
        ]);
    }

    /**
     * Deletes a base.
     *
     * @param App\Services\BaseService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBase(Request $request, BaseService $service, $id) {
        if ($id && $service->deleteBase(Base::find($id), Auth::user())) {
            flash('Base deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/bases');
    }
}
