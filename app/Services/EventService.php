<?php

namespace App\Services;

use App\Facades\Settings;
use App\Models\EventTeam;
use App\Models\User\UserCurrency;
use Illuminate\Support\Facades\DB;

class EventService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Event Service
    |--------------------------------------------------------------------------
    |
    | Handles functions relating to events.
    |
    */

    /**
     * Tracks event currency credited to a user: adds it to the global
     * tally (held by the admin_user account) and to the user's team.
     * Called from CurrencyManager::creditCurrency.
     *
     * @param \App\Models\User\User         $recipient
     * @param \App\Models\Currency\Currency $currency
     * @param int                           $quantity
     */
    public static function trackEventCurrency($recipient, $currency, $quantity) {
        $eventCurrency = (int) Settings::get('event_currency');
        if (!$eventCurrency || $currency->id != $eventCurrency || $quantity <= 0) {
            return;
        }
        $adminId = (int) Settings::get('admin_user');

        if (Settings::get('event_global_score') && $adminId && $recipient->id != $adminId) {
            $record = UserCurrency::where('user_id', $adminId)->where('currency_id', $currency->id)->first();
            if ($record) {
                DB::table('user_currencies')->where('user_id', $adminId)->where('currency_id', $currency->id)->increment('quantity', $quantity);
            } else {
                UserCurrency::create(['user_id' => $adminId, 'currency_id' => $currency->id, 'quantity' => $quantity]);
            }
        }

        // Players who haven't joined a team still get their currency; it just isn't added to a team score
        if (Settings::get('event_teams') && $recipient->settings && $recipient->settings->team_id) {
            EventTeam::where('id', $recipient->settings->team_id)->increment('score', $quantity);
        }
    }

    /**
     * Resets every user's event currency and all team scores to 0.
     *
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function clearEventCurrency($user) {
        DB::beginTransaction();

        try {
            $eventCurrency = (int) Settings::get('event_currency');
            if (!$eventCurrency) {
                throw new \Exception('No event currency is set.');
            }
            if (!UserCurrency::where('currency_id', $eventCurrency)->exists()) {
                throw new \Exception('No event currency exists to be cleared!');
            }

            UserCurrency::where('currency_id', $eventCurrency)->update(['quantity' => 0]);
            EventTeam::where('score', '>', 0)->update(['score' => 0]);

            if (!$this->logAdminAction($user, 'Cleared Event Currency', 'Reset all event currency and team scores')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates, updates and removes event teams.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function updateTeams($data, $user) {
        DB::beginTransaction();

        try {
            // This must run even with no names given, so all teams can be removed
            $names = (array) ($data['name'] ?? []);

            foreach (EventTeam::all() as $team) {
                if (!array_key_exists($team->id, $names)) {
                    if (Settings::get('event_teams')) {
                        throw new \Exception('Teams cannot be deleted while teams are enabled/an event is ongoing!');
                    }
                    if ($team->has_image && file_exists($team->imagePath.'/'.$team->imageFileName)) {
                        $this->deleteImage($team->imagePath, $team->imageFileName);
                    }
                    $team->members()->update(['team_id' => null]);
                    $team->delete();
                }
            }

            foreach ($names as $key => $teamName) {
                if (!$teamName) {
                    throw new \Exception('Team name is required.');
                }
                if (EventTeam::where('name', $teamName)->where('id', '!=', $key)->exists()) {
                    throw new \Exception('A team with this name already exists.');
                }

                $team = EventTeam::find($key);
                if ($team) {
                    $score = $data['score'][$key] ?? $team->score;
                    if (!is_numeric($score) || (int) $score != $score || (int) $score < 0) {
                        throw new \Exception('Team scores must be whole numbers of 0 or more.');
                    }
                    $team->update(['name' => $teamName, 'score' => (int) $score]);
                } else {
                    $team = EventTeam::create(['name' => $teamName]);
                }

                if (isset($data['image'][$key]) && $data['image'][$key]) {
                    $team->update(['has_image' => 1]);
                    $this->handleImage($data['image'][$key], $team->imagePath, $team->imageFileName);
                } elseif (isset($data['remove_image'][$key]) && $data['remove_image'][$key] && $team->has_image) {
                    $team->update(['has_image' => 0]);
                    if (file_exists($team->imagePath.'/'.$team->imageFileName)) {
                        $this->deleteImage($team->imagePath, $team->imageFileName);
                    }
                }
            }

            if (!$this->logAdminAction($user, 'Updated Event Teams', 'Updated event teams')) {
                throw new \Exception('Failed to log admin action.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Joins a team for the user, if teams are enabled and they have none.
     *
     * @param \App\Models\User\User $user
     * @param int                   $teamId
     *
     * @return bool
     */
    public function joinTeam($user, $teamId) {
        DB::beginTransaction();

        try {
            if (!Settings::get('event_teams')) {
                throw new \Exception('Teams are not currently open.');
            }
            $team = EventTeam::find($teamId);
            if (!$team) {
                throw new \Exception('Invalid team selected.');
            }
            if ($user->settings->team_id) {
                throw new \Exception('You are already part of a team!');
            }

            $user->settings->update(['team_id' => $team->id]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
