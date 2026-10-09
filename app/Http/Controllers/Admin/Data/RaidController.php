<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Raid\Raid;
use App\Models\Raid\RaidBoss;
use App\Models\Raid\RaidBossImage;
use App\Services\RaidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RaidController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Raid Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of raids.
    |
    */

    /**********************************************************************************************

        RAIDS

    **********************************************************************************************/

    /**
     * Shows the raid category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaidIndex(Request $request) {
        $query = Raid::orderBy('id', 'DESC');
        $data = $request->only(['name']);
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.raids.raids', [
            'raids'    => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create raid page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateRaid() {
        return view('admin.raids.create_edit_raid', [
            'raid'     => new Raid,
        ]);
    }

    /**
     * Shows the edit raid page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditRaid($id) {
        $raid = Raid::find($id);
        if (!$raid) {
            abort(404);
        }

        return view('admin.raids.create_edit_raid', [
            'raid'     => $raid,
        ]);
    }

    /**
     * Creates or edits a raid.
     *
     * @param App\Services\RaidService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditRaid(Request $request, RaidService $service, $id = null) {
        $id ? $request->validate(Raid::$updateRules) : $request->validate(Raid::$createRules);
        $data = $request->only([
            'name', 'description', 'start_at', 'end_at', 'is_visible', 'rewardable_type', 'rewardable_id', 'quantity', 'damage_required',
            'image', 'remove_image', 'damage_type', 'damage_id', 'damage_quantity', 'damage_base', 'damage_max', 'continue_raid',
        ]);
        if ($id && $service->updateRaid(Raid::find($id), $data, Auth::user())) {
            flash(ucfirst(__('raids.raid')).' updated successfully.')->success();
        } elseif (!$id && $raid = $service->createRaid($data, Auth::user())) {
            flash(ucfirst(__('raids.raid')).' created successfully.')->success();

            return redirect()->to('admin/data/'.__('raids.raids').'/edit/'.$raid->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the raid deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteRaid($id) {
        $raid = Raid::find($id);

        return view('admin.raids._delete_raid', [
            'raid' => $raid,
        ]);
    }

    /**
     * Deletes a raid.
     *
     * @param App\Services\RaidService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteRaid(Request $request, RaidService $service, $id) {
        if ($id && $service->deleteRaid(Raid::find($id))) {
            flash(ucfirst(__('raids.raid')).' deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/'.__('raids.raids'));
    }

    /**
     * Gets the raid manual start modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStartRaid($id) {
        $raid = Raid::find($id);

        return view('admin.raids._start_raid', [
            'raid' => $raid,
        ]);
    }

    /**
     * Starts a raid.
     *
     * @param App\Services\RaidService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postStartRaid(Request $request, RaidService $service, $id) {
        if ($id && $service->startRaid(Raid::find($id))) {
            flash(ucfirst(__('raids.raid')).' began successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the raid manual end modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEndRaid($id) {
        $raid = Raid::find($id);

        return view('admin.raids._end_raid', [
            'raid' => $raid,
        ]);
    }

    /**
     * Ends a raid.
     *
     * @param App\Services\RaidService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEndRaid(Request $request, RaidService $service, $id) {
        if ($id && $service->endRaid(Raid::find($id))) {
            flash(ucfirst(__('raids.raid')).' ended successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the raid reward distribution modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRewardRaid($id) {
        $raid = Raid::find($id);

        return view('admin.raids._reward_raid', [
            'raid' => $raid,
        ]);
    }

    /**
     * Rewards a raid.
     *
     * @param App\Services\RaidService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRewardRaid(Request $request, RaidService $service, $id) {
        if ($id && $service->rewardRaid(Raid::find($id))) {
            flash(ucfirst(__('raids.raid')).' participants rewarded successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        RAID BOSSES

    **********************************************************************************************/

    /**
     * Shows the raid category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaidBossIndex(Request $request) {
        $query = RaidBoss::orderBy('id', 'DESC');
        $data = $request->only(['name', 'raid_id']);
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['raid_id']) && $data['raid_id'] != 'any') {
            $query->where('raid_id', $data['raid_id']);
        }

        return view('admin.raids.raid_bosses', [
            'bosses'    => $query->paginate(20)->appends($request->query()),
            'raids'     => Raid::orderBy('id', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create raid page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateRaidBoss($id) {
        $raid = Raid::find($id);
        if (!$raid) {
            abort(404);
        }

        return view('admin.raids.create_edit_raid_boss', [
            'raidBoss'     => new RaidBoss,
            'raid'         => $raid,
        ]);
    }

    /**
     * Shows the edit raid page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditRaidBoss($id) {
        $raidBoss = RaidBoss::find($id);
        if (!$raidBoss) {
            abort(404);
        }
        $bossImages = $raidBoss->images->count() ? $raidBoss->images()->get() : null;
        if ($bossImages) {
            $bossImages = $bossImages->sortByDesc('thresholdCalc');
        }

        return view('admin.raids.create_edit_raid_boss', [
            'raidBoss'     => $raidBoss,
            'bossImages'   => $bossImages,
        ]);
    }

    /**
     * Creates or edits a raid.
     *
     * @param App\Services\RaidService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateRaidBoss(Request $request, RaidService $service, $id) {
        $request->validate(RaidBoss::$createRules);
        $data = $request->only([
            'name', 'raid_id', 'description', 'is_visible', 'health',
        ]);
        if ($boss = $service->createRaidBoss($data, Auth::user())) {
            flash(ucfirst(__('raids.raid')).' '.__('raids.boss').' created successfully.')->success();

            return redirect()->to('admin/data/'.__('raids.raid').'-'.__('raids.bosses').'/edit/'.$boss->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Creates or edits a raid.
     *
     * @param App\Services\RaidService $service
     * @param int|null                 $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditRaidBoss(Request $request, RaidService $service, $id) {
        $request->validate(RaidBoss::$updateRules);
        $data = $request->only([
            'name', 'raid_id', 'description', 'is_visible', 'health', 'threshold_type', 'threshold_amount', 'threshold_bar_color', 'threshold_text_color',
        ]);
        if ($id && $service->updateRaidBoss(RaidBoss::find($id), $data, Auth::user())) {
            flash(ucfirst(__('raids.raid')).' '.__('raids.boss').' updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the raid deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteRaidBoss($id) {
        $raidBoss = RaidBoss::find($id);

        return view('admin.raids._delete_raid_boss', [
            'raidBoss' => $raidBoss,
        ]);
    }

    /**
     * Deletes a raid.
     *
     * @param App\Services\RaidService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteRaidBoss(Request $request, RaidService $service, $id) {
        if ($id && $service->deleteRaidBoss(RaidBoss::find($id))) {
            flash(ucfirst(__('raids.raid')).' '.__('raids.boss').' deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/'.__('raids.raid').'-'.__('raids.bosses').'');
    }

    /**
     * Gets the raid deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateRaidBossImage($id) {
        $raidBoss = RaidBoss::find($id);

        return view('admin.raids._create_edit_raid_boss_image', [
            'raidBoss'      => $raidBoss,
            'bossImage'     => new RaidBossImage,
        ]);
    }

    /**
     * Gets the raid deletion modal.
     *
     * @param int   $id
     * @param mixed $imageId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditRaidBossImage($id, $imageId) {
        $bossImage = RaidBossImage::find($imageId);

        return view('admin.raids._create_edit_raid_boss_image', [
            'raidBoss'      => $bossImage->boss,
            'bossImage'     => $bossImage,
        ]);
    }

    /**
     * Creates or edits a raid.
     *
     * @param App\Services\RaidService $service
     * @param int|null                 $id
     * @param mixed|null               $imageId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditRaidBossImage(Request $request, RaidService $service, $id, $imageId = null) {
        $imageId ? $request->validate(RaidBossImage::$updateRules) : $request->validate(RaidBossImage::$createRules);
        $data = $request->only([
            'image', 'threshold_type', 'health_threshold',
        ]);
        $raidBoss = RaidBoss::find($id);

        if ($imageId && $service->updateRaidBossImage($raidBoss, $data, Auth::user(), RaidBossImage::find($imageId))) {
            flash(ucfirst(__('raids.raid')).' '.__('raids.boss').' image updated successfully.')->success();
        } elseif (!$imageId && $service->createRaidBossImage($raidBoss, $data, Auth::user())) {
            flash(ucfirst(__('raids.raid')).' '.__('raids.boss').' image created successfully.')->success();

            return redirect()->to('admin/data/'.__('raids.raid').'-'.__('raids.bosses').'/edit/'.$raidBoss->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the boss image deletion modal.
     *
     * @param int   $id
     * @param mixed $imageId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteRaidBossImage($id, $imageId) {
        $raidBoss = RaidBoss::find($id);
        $bossImage = RaidBossImage::find($imageId);
        if ($raidBoss->id != $bossImage->raid_boss_id) {
            abort(404);
        }

        return view('admin.raids._delete_raid_boss_image', [
            'raidBoss'  => $raidBoss,
            'bossImage' => $bossImage,
        ]);
    }

    /**
     * Deletes a boss image.
     *
     * @param App\Services\RaidService $service
     * @param int                      $id
     * @param mixed                    $imageId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteRaidBossImage(Request $request, RaidService $service, $id, $imageId) {
        $raidBoss = RaidBoss::find($id);

        if ($id && $service->deleteRaidBossImage($raidBoss, RaidBossImage::find($imageId))) {
            flash(ucfirst(__('raids.raid')).' '.__('raids.boss').' image deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/'.__('raids.raid').'-'.__('raids.bosses').'/edit/'.$raidBoss->id);
    }
}
