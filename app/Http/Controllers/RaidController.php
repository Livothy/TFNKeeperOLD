<?php

namespace App\Http\Controllers;

use App\Models\Raid\Raid;
use App\Models\Raid\RaidBoss;
use App\Services\RaidManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class RaidController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Raid Controller
    |--------------------------------------------------------------------------
    |
    | Displays information about the raids, as well as all their relevant
    | functions.
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->middleware(function ($request, $next) {
            $visibleRaids = Raid::where('is_visible', 1)->orderBy('id', 'DESC')->get();
            $currentRaid = $visibleRaids->filter(function ($raid) {
                return $raid->isActive;
            });

            View::share('currentRaid', $currentRaid->first() ?? null);

            return $next($request);
        });
    }

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request) {
        $query = Raid::query();
        $data = $request->only(['name', 'sort']);

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
                default:
                    $query->sortNewest();
                    break;
            }
        } else {
            $query->sortNewest();
        }

        if (!Auth::check() || !Auth::user()->hasPower('manage_raids')) {
            $query->where('is_visible', 1);
        }

        return view('raids.index', [
            'raids' => $query->paginate(10)->appends($request->query()),
        ]);
    }

    /**
     * Shows a specific raid's page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRaid($id) {
        $raid = Raid::find($id);
        if (!$raid) {
            abort(404);
        }
        if (!$raid->is_visible && (!Auth::check() || Auth::check() && !Auth::user()->hasPower('manage_raids'))) {
            abort(404);
        }

        return view('raids.raid', [
            'raid' => $raid,
            'boss' => $raid->bosses->count() ? $raid->bosses->first() : null,
        ]);
    }

    /**
     * Shows the current ongoing raid.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCurrentRaid() {
        return view('raids.current_raid');
    }

    /**
     * Shows the raid bosses index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBosses(Request $request) {
        $query = RaidBoss::query();
        $data = $request->only(['name', 'sort']);

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }
        if (isset($data['sort'])) {
            switch ($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortNewest(true);
                    break;
                default:
                    $query->sortNewest();
                    break;
            }
        } else {
            $query->sortNewest();
        }

        if (!Auth::check() || !Auth::user()->hasPower('manage_raids')) {
            $query->where('is_visible', 1);
        }

        return view('raids.bosses', [
            'bosses' => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the raid boss individual page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBoss($id) {
        $boss = RaidBoss::find($id);
        if (!$boss) {
            abort(404);
        }
        if ((!$boss->is_visible || !$boss->raid->is_visible) && (!Auth::check() || Auth::check() && !Auth::user()->hasPower('manage_raids'))) {
            abort(404);
        }

        return view('raids.boss', [
            'boss' => $boss,
        ]);
    }

    /**
     * Attacks the current raid.
     *
     * @param mixed $id
     * @param mixed $bossId
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postAttackRaid(Request $request, RaidManager $service, $id, $bossId) {
        $raid = Raid::find($id);
        $boss = RaidBoss::find($bossId);
        if (!$raid || !$raid->is_visible) {
            abort(404);
        }
        if (!$boss) {
            abort(404);
        }
        if ($boss->raid_id != $raid->id) {
            abort(404);
        }

        if ($service->attackBoss($raid, $boss, Auth::user())) {
            flash('Successfully attacked the '.__('raids.boss').'!')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Shows the leaderboard of users and their damage dealt
     * for a specific raid.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserLeaderboard($id) {
        $raid = Raid::find($id);
        if (!$raid) {
            abort(404);
        }
        if (!$raid->is_visible && (!Auth::check() || Auth::check() && !Auth::user()->hasPower('manage_raids'))) {
            abort(404);
        }
        $query = $raid->damageLogs()->selectRaw('SUM(damage) as total_damage,user_id')->groupBy('user_id')->get();

        return view('raids.user_leaderboard', [
            'raid'    => $raid,
            'entries' => $query->sortByDesc('total_damage'),
        ]);
    }
}
