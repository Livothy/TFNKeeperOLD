<?php

namespace App\Http\Controllers;

use App\Models\Carrier\Carrier;
use App\Models\Carrier\MarkingCarrier;
use App\Models\Marking\Marking;

class MarkingController extends Controller {
    public function getMarkingPage($slug) {
        $marking = Marking::where('slug', $slug)->where('is_visible', 1)->first();
        if (!$marking) {
            abort(404);
        }

        $carriers = MarkingCarrier::where('marking_id', $marking->id)->pluck('carrier_id')->toArray();
        $active_carriers = Carrier::whereIn('id', $carriers)->get();

        return view('designhub.marking', ['marking' => $marking], ['carriers' => $active_carriers]);
    }
}
