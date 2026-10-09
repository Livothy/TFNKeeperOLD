<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Carrier\Carrier;
use App\Models\Carrier\MarkingCarrier;
use App\Models\Marking\Marking;
use App\Services\CarrierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarrierController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Carrier Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character Carriers.
    |
    */

    /**********************************************************************************************

        CARRUERS

    **********************************************************************************************/

    /**
     * Shows the Carrier index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCarriersIndex(Request $request) {
        $query = Carrier::query();
        $data = $request->only(['name', 'description']);

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.carriers.carriers', [
            'carriers'   => $query->paginate(20)->appends($request->query()),
            'markings'   => Marking::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the create Carrier page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateCarrier() {
        return view('admin.carriers.create_edit_carrier', [
            'carrier'    => new Carrier,
            'markings'   => Marking::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit Carrier page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCarrier($id) {
        $carrier = Carrier::find($id);
        if (!$carrier) {
            abort(404);
        }

        $active_markings = MarkingCarrier::where('carrier_id', $id)->pluck('marking_id')->toArray();

        return view('admin.carriers.create_edit_carrier', [
            'carrier'         => $carrier,
            'active_markings' => Marking::whereIn('id', $active_markings)->pluck('id')->toArray(),
            'markings'        => Marking::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a Carrier.
     *
     * @param App\Services\CarrierService $service
     * @param int|null                    $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditCarrier(Request $request, CarrierService $service, $id = null) {
        $id ? $request->validate(Carrier::$updateRules) : $request->validate(Carrier::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'attached_markings',
        ]);

        if ($id && $service->updateCarrier(Carrier::find($id), $data, Auth::user())) {
            flash('Carrier updated successfully.')->success();
        } elseif (!$id && $carrier = $service->createCarrier($data, Auth::user())) {
            flash('Carrier created successfully.')->success();

            return redirect()->to('admin/data/carrier/edit/'.$carrier->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the Carrier deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteCarrier($id) {
        $carrier = Carrier::find($id);

        return view('admin.carriers._delete_carrier', [
            'carrier' => $carrier,
        ]);
    }

    /**
     * Deletes a Carrier.
     *
     * @param App\Services\CarrierService $service
     * @param int                         $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteCarrier(Request $request, CarrierService $service, $id) {
        if ($id && $service->deleteCarrier(Carrier::find($id), Auth::user())) {
            flash('Carrier deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/carriers');
    }
}
