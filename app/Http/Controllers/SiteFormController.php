<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Forms\SiteForm;
use App\Models\Forms\SiteFormAnswer;
use App\Services\SiteFormManager;

class SiteFormController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SiteForm Controller
    |--------------------------------------------------------------------------
    |
    | Displays form and poll posts.
    |
    */

    /**
     * Shows the form index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        //check notif
        if(Auth::check() && Auth::user()->is_polls_unread) Auth::user()->update(['is_polls_unread' => 0]);

        if (Auth::user() && Auth::user()->isStaff) {
            return view('forms.index', ['forms' => SiteForm::orderBy('updated_at', 'DESC')->paginate(10)]);
        } else {
            return view('forms.index', ['forms' => SiteForm::visible()->orderBy('updated_at', 'DESC')->paginate(10)]);
        }
    }

    /**
     * Shows a form post.
     *
     * @param  int          $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSiteForm(Request $request, $id)
    {
        $form = SiteForm::where('id', $id)->visible()->first();
        if (!$form) abort(404);
        return view('forms.site_form', [
            'form' => $form,
            'user' => Auth::user(),
            'action' => isset($request['action']) ? $request['action'] : null,
            'number' => isset($request['number']) ? $request['number'] : null,
        ]);
    }

    /**
     * Edit a form post.
     *
     * @param  int          $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editSiteForm(Request $request, $id)
    {
        $form = SiteForm::where('id', $id)->visible()->first();
        if (!$form) abort(404);
        return view('forms.site_form_edit', [
            'form' => $form,
            'user' => Auth::user(),
            'action' => isset($request['action']) ? $request['action'] : null,
            'number' => isset($request['number']) ? $request['number'] : null,
        ]);
    }


    /**
     * Posts a form and saves the response of the user.
     *
     * @param  int          $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postSiteForm(Request $request, SiteFormManager $service, $id)
    {
        $form = SiteForm::where('id', $id)->first();
        if (!$form) abort(404);
        if (!Auth::user()) abort(403);
        $rewardsString = $service->postSiteForm($form, $request->all(), Auth::user());
        if (isset($rewardsString) && $rewardsString !== false) {
            flash('Form posted successfully! ' . $rewardsString)->success();
            return redirect()->to('/forms/send/' . $form->id . '?action=edit&number=' . $request['submission_number']);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Likes an answer.
     *
     * @param  int $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postLikeAnswer(SiteFormManager $service, $id)
    {
        $answer = SiteFormAnswer::find($id);
        if (!$answer) abort(404);
        if (!Auth::user()) abort(403);

        if ($service->postLikeAnswer($answer, Auth::user())) {
            flash('You liked an answer!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Un-Likes an answer.
     *
     * @param  int $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postUnlikeAnswer(SiteFormManager $service, $id)
    {
        $answer = SiteFormAnswer::find($id);
        if (!$answer) abort(404);
        if (!Auth::user()) abort(403);

        if ($service->postUnlikeAnswer($answer, Auth::user())) {
            flash('You removed a like from an answer!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
