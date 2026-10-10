<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rank\Rank;
use App\Models\RankReward;
use App\Services\RankRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RankRewardController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Rank Reward Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of rank rewards.
    |
    */

    /**
     * Shows the rank reward index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex() {
        return view('admin.rank_rewards.rank_rewards', [
            'rankRewards' => RankReward::with('rank')->orderBy('name')->paginate(30),
        ]);
    }

    /**
     * Shows the create rank reward page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateRankReward() {
        return view('admin.rank_rewards.create_edit_rank_reward', [
            'rankReward' => new RankReward,
            'ranks'      => Rank::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit rank reward page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditRankReward($id) {
        $rankReward = RankReward::find($id);
        if (!$rankReward) {
            abort(404);
        }

        return view('admin.rank_rewards.create_edit_rank_reward', [
            'rankReward' => $rankReward,
            'ranks'      => Rank::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a rank reward.
     *
     * @param App\Services\RankRewardService $service
     * @param int|null                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditRankReward(Request $request, RankRewardService $service, $id = null) {
        $request->validate(RankReward::$rules);
        $data = $request->only(['name', 'rank_id', 'is_active', 'reward_time', 'rewardable_type', 'rewardable_id', 'quantity']);

        if ($id) {
            $rankReward = RankReward::find($id);
            if (!$rankReward) {
                abort(404);
            }
            if ($service->updateRankReward($rankReward, $data, Auth::user())) {
                flash('Rank reward updated successfully.')->success();
            } else {
                foreach ($service->errors()->getMessages()['error'] as $error) {
                    flash($error)->error();
                }
            }
        } elseif ($rankReward = $service->createRankReward($data, Auth::user())) {
            flash('Rank reward created successfully.')->success();

            return redirect()->to('admin/rank-rewards/edit/'.$rankReward->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back()->withInput();
    }

    /**
     * Gets the rank reward deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteRankReward($id) {
        return view('admin.rank_rewards._delete_rank_reward', [
            'rankReward' => RankReward::find($id),
        ]);
    }

    /**
     * Deletes a rank reward.
     *
     * @param App\Services\RankRewardService $service
     * @param int                            $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteRankReward(Request $request, RankRewardService $service, $id) {
        $rankReward = RankReward::find($id);
        if (!$rankReward) {
            abort(404);
        }

        if ($service->deleteRankReward($rankReward, Auth::user())) {
            flash('Rank reward deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/rank-rewards');
    }
}
