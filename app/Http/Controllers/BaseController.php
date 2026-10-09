<?php

namespace App\Http\Controllers;

use App\Models\Base\Base;
use Illuminate\Http\Request;

class BaseController extends Controller {
    public function getBasesPageView() {
        return view('designhub.basespage');
    }

    public function getBasePage(Request $request) {
        return view('designhub.basespage', [
            'bases'      => Base::where('is_visible', 1)->get(),
        ]);
    }
}
