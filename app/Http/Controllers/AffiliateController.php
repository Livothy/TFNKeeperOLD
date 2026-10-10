<?php

namespace App\Http\Controllers;

use Auth;
use Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\SitePage;
use App\Models\Affiliate;
use App\Services\AffiliateManager;

class AffiliateController extends Controller
{
    
    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('home.affiliates', [
            'open' => intval(Settings::get('affiliates_open')),
            'affiliates' => Affiliate::where('status','Accepted')->featured(0)->get(),
            'featured' => Affiliate::where('status','Accepted')->featured(1)->get(),
        ]);
    }
    
    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getApply()
    {

        return view('home.affiliates_apply', [
            'open' => intval(Settings::get('affiliates_open')),
            'affiliates' => Affiliate::where('status','Accepted')->featured(0)->get(),
            'featured' => Affiliate::where('status','Accepted')->featured(1)->get(),
        ]);
    }
    
    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postApply(Request $request, AffiliateManager $service)
    {
        if(!intval(Settings::get('affiliates_open'))) {
            flash('Affiliate applications are currently closed.')->error();
            return redirect()->to('affiliates');
        }
        $request->validate(array_merge(Affiliate::$createRules, Auth::check() ? [] : ['guest_name' => 'required|max:50']));
        do {
            $slug = \Illuminate\Support\Str::random(16);
        } while(Affiliate::where('slug', $slug)->exists());
        if($service->createAffiliate($request->only(['name','url','image_url','description','message','guest_name']), Auth::user(), $slug)) {
            flash('Affiliate request submitted successfully. Please bookmark this page.')->success();
            return redirect()->to('affiliates/status/'.$slug);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back()->withInput();
    }
    
    /**
     * Shows the status of an affiliate request.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStatus($slug)
    {
        $affiliate = Affiliate::where('slug', $slug)->first();
        if(!$affiliate) abort(404);

        return view('home.affiliates_check', [
            'affiliate' => $affiliate
        ]);
    }
    

}
