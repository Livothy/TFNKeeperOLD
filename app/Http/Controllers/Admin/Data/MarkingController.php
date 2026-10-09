<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Marking\Marking;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Services\MarkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarkingController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Marking Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character markings & genetics.
    |
    */

    /**********************************************************************************************

        MARKINGS

    **********************************************************************************************/

    /**
     * Shows the marking index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getMarkingIndex(Request $request) {
        $query = Marking::query();
        $data = $request->only(['rarity_id', 'species_id', 'name']);
        if (isset($data['rarity_id']) && $data['rarity_id'] != 'none') {
            $query->where('rarity_id', $data['rarity_id']);
        }
        if (isset($data['species_id']) && $data['species_id'] != 'none') {
            $query->where('species_id', $data['species_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.markings.markings', [
            'markings'   => $query->paginate(20)->appends($request->query()),
            'rarities'   => ['none' => 'Any Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses'  => ['none' => 'Any Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create marking page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateMarking() {
        return view('admin.markings.create_edit_marking', [
            'marking'    => new Marking,
            'rarities'   => ['none' => 'Select a Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses'  => ['none' => 'No restriction'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit marking page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditMarking($id) {
        $marking = Marking::find($id);
        if (!$marking) {
            abort(404);
        }

        return view('admin.markings.create_edit_marking', [
            'marking'    => $marking,
            'rarities'   => ['none' => 'Select a Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses'  => ['none' => 'No restriction'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a marking.
     *
     * @param App\Services\MarkingService $service
     * @param int|null                    $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditMarking(Request $request, MarkingService $service, $id = null) {
        $id ? $request->validate(Marking::$updateRules) : $request->validate(Marking::$createRules);
        $data = $request->only([
            'name', 'slug', 'species_id', 'rarity_id', 'description', 'image', 'is_visible', 'recessive', 'dominant', 'short_description',
        ]);
        if ($id && $service->updateMarking(Marking::find($id), $data, Auth::user())) {
            flash('Marking updated successfully.')->success();
        } elseif (!$id && $marking = $service->createMarking($data, Auth::user())) {
            flash('Marking created successfully.')->success();

            return redirect()->to('admin/data/markings/edit/'.$marking->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the marking deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteMarking($id) {
        $marking = Marking::find($id);

        return view('admin.markings._delete_marking', [
            'marking' => $marking,
        ]);
    }

    /**
     * Deletes a marking.
     *
     * @param App\Services\MarkingService $service
     * @param int                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteMarking(Request $request, MarkingService $service, $id) {
        if ($id && $service->deleteMarking(Marking::find($id), Auth::user())) {
            flash('Marking deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/markings');
    }
}
