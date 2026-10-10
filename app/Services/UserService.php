<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Facades\Settings;
use App\Models\Border\Border;
use Auth;
use App\Models\User\StaffProfile;
use App\Models\Character\CharacterDesignUpdate;
use App\Models\Character\CharacterTransfer;
use App\Models\Gallery\GallerySubmission;
use App\Models\Invitation;
use App\Models\Rank\Rank;
use App\Models\Submission\Submission;
use App\Models\Trade;
use App\Models\User\User;
use App\Models\User\UserPage;
use App\Models\User\UserUpdateLog;
use App\Models\WorldExpansion\Faction;
use App\Models\WorldExpansion\FactionRankMember;
use App\Models\WorldExpansion\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class UserService extends Service {
    /*
    |--------------------------------------------------------------------------
    | User Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of users.
    |
    */

    /**
     * Create a user.
     *
     * @param array $data
     *
     * @return User
     */
    public function createUser($data) {
        // If the rank is not given, create a user with the lowest existing rank.
        if (!isset($data['rank_id'])) {
            $data['rank_id'] = Rank::orderBy('sort')->first()->id;
        }

        // Make birthday into format we can store
        $formatDate = Carbon::parse($data['dob']);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'] ?? null,
            'rank_id'   => $data['rank_id'],
            'password'  => isset($data['password']) ? Hash::make($data['password']) : null,
            'birthday'  => $formatDate,
            'has_alias' => $data['has_alias'] ?? false,
            // Verify the email if we're logging them in with their social
            'email_verified_at' => (!isset($data['password']) && !isset($data['email'])) ? now() : null,
        ]);
        $user->settings()->create([
            'user_id' => $user->id,
        ]);
        $user->profile()->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param mixed $socialite
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data, $socialite = false) {
        return Validator::make($data, [
            'name'      => ['required', 'string', 'min:3', 'max:25', 'alpha_dash', 'unique:users'],
            'email'     => ($socialite ? [] : ['required']) + ['string', 'email', 'max:255', 'unique:users'],
            'agreement' => ['required', 'accepted'],
            'password'  => ($socialite ? [] : ['required']) + ['string', 'min:8', 'confirmed'],
            'dob'       => [
                'required', function ($attribute, $value, $fail) {
                    $formatDate = Carbon::createFromFormat('Y-m-d', $value);
                    $now = Carbon::now();
                    if ($formatDate->diffInYears($now) < 13) {
                        $fail('You must be 13 or older to access this site.');
                    }
                },
            ],
            'code'                 => ['string', function ($attribute, $value, $fail) {
                if (!Settings::get('is_registration_open')) {
                    if (!$value) {
                        $fail('An invitation code is required to register an account.');
                    }
                    $invitation = Invitation::where('code', $value)->whereNull('recipient_id')->first();
                    if (!$invitation) {
                        $fail('Invalid code entered.');
                    }
                }
            },
            ],
        ] + (config('app.env') == 'production' && config('lorekeeper.extensions.use_recaptcha') ? [
            'g-recaptcha-response' => 'required|recaptchav3:register,0.5',
        ] : []));
    }

    /**
     * Updates a user. Used in modifying the admin user on the command line.
     *
     * @param array $data
     *
     * @return User
     */
    public function updateUser($data) {
        $user = User::find($data['id']);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        if ($user) {
            $user->update($data);
        }

        return $user;
    }

    /**
     * Updates a user. Used in modifying the admin user on the command line.
     *
     * @param mixed $id
     * @param mixed $user
     *
     * @return User
     */
    public function updateLocation($id, $user) {
        DB::beginTransaction();

        try {
            $location = Location::find($id);
            if (!$location) {
                throw new \Exception('Not a valid location.');
            }
            if (!$location->is_user_home) {
                throw new \Exception('Not a location a user can have as their home.');
            }

            $limit = Settings::get('WE_change_timelimit');

            if ($user->canChangeLocation) {
                $user->home_id = $id;
                $user->home_changed = Carbon::now();
                $user->save();
            } else {
                throw new \Exception("You can't change your location yet!");
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a user. Used in modifying the admin user on the command line.
     *
     * @param mixed $id
     * @param mixed $user
     *
     * @return User
     */
    public function updateFaction($id, $user, $staffOverride = false) {
        DB::beginTransaction();

        try {
            if ($user->faction) {
                $old = $user->faction;
            }
            $faction = Faction::find($id);
            if ($id == 0) {
                $id = null;
            } elseif (!$faction) {
                throw new \Exception('Not a valid faction.');
            } elseif (!$faction->is_user_faction) {
                throw new \Exception('Not a faction a user can join.');
            }

            $limit = Settings::get('WE_change_timelimit');

            if ($staffOverride || $user->canChangeFaction) {
                $user->faction_id = $id;
                $user->faction_changed = Carbon::now();
                $user->save();
            } else {
                throw new \Exception("You can't change your faction yet!");
            }

            // Reset standing/remove from closed rank
            if (($id == null) || (isset($old) && $faction->id != $old->id)) {
                $standing = $user->getCurrencies(true)->where('id', Settings::get('WE_faction_currency'))->first();
                if ($standing && $standing->quantity > 0) {
                    if (!$debit = (new CurrencyManager)->debitCurrency($user, null, 'Changed Factions', null, $standing, $standing->quantity)) {
                        throw new \Exception('Failed to reset standing.');
                    }
                }

                if (FactionRankMember::where('member_type', 'user')->where('member_id', $user->id)->first()) {
                    FactionRankMember::where('member_type', 'user')->where('member_id', $user->id)->first()->delete();
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates the user's password.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function updatePassword($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($user->password) && !Hash::check($data['old_password'], $user->password)) {
                throw new \Exception('Please enter your old password.');
            }
            if (Hash::make($data['new_password']) == $user->password) {
                throw new \Exception('Please enter a different password.');
            }

            $user->password = Hash::make($data['new_password']);
            $user->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates the user's email and resends a verification email.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool
     */
    public function updateEmail($data, $user) {
        $user->email = $data['email'];
        $user->email_verified_at = null;
        $user->save();

        $user->sendEmailVerificationNotification();

        return true;
    }

    /**
     * Updates user's birthday.
     *
     * @param mixed $data
     * @param mixed $user
     */
    public function updateBirthday($data, $user) {
        DB::beginTransaction();

        try {
            $user->birthday = $data;
            $user->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates user's birthday setting.
     *
     * @param mixed $data
     * @param mixed $user
     */
    public function updateBirthdayVisibilitySetting($data, $user) {
        DB::beginTransaction();

        try {
            $user->settings->birthday_setting = $data;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Confirms a user's two-factor auth.
     *
     * @param string           $code
     * @param array            $data
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function confirmTwoFactor($code, $data, $user) {
        DB::beginTransaction();

        try {
            if (app(TwoFactorAuthenticationProvider::class)->verify(decrypt($data['two_factor_secret']), $code['code'])) {
                $user->forceFill([
                    'two_factor_secret'         => $data['two_factor_secret'],
                    'two_factor_recovery_codes' => $data['two_factor_recovery_codes'],
                ])->save();
            } else {
                throw new \Exception('Provided code was invalid.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Disables a user's two-factor auth.
     *
     * @param string           $code
     * @param \App\Models\User $user
     *
     * @return bool
     */
    public function disableTwoFactor($code, $user) {
        DB::beginTransaction();

        try {
            if (app(TwoFactorAuthenticationProvider::class)->verify(decrypt($user->two_factor_secret), $code['code'])) {
                $user->forceFill([
                    'two_factor_secret'         => null,
                    'two_factor_recovery_codes' => null,
                ])->save();
            } else {
                throw new \Exception('Provided code was invalid.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates the user's avatar.
     *
     * @param User  $user
     * @param mixed $avatar
     *
     * @return bool
     */
    public function updateAvatar($avatar, $user) {
        DB::beginTransaction();

        try {
            if (!$avatar) {
                throw new \Exception('Please upload a file.');
            }
            $filename = $user->id.'.'.$avatar->getClientOriginalExtension();

            if ($user->avatar != 'default.jpg') {
                $file = 'images/avatars/'.$user->avatar;
                // $destinationPath = 'uploads/' . $id . '/';

                if (File::exists($file)) {
                    if (!unlink($file)) {
                        throw new \Exception('Failed to unlink old avatar.');
                    }
                }
            }

            // Checks if uploaded file is a GIF
            if ($avatar->getClientOriginalExtension() == 'gif') {
                if (!$avatar->move(public_path('images/avatars'), $filename)) {
                    throw new \Exception('Failed to move file.');
                }
            } else {
                if (!Image::make($avatar)->resize(150, 150)->save(public_path('images/avatars/'.$filename))) {
                    throw new \Exception('Failed to process avatar.');
                }
            }

            $user->avatar = $filename;
            $user->save();

            return $this->commitReturn($avatar);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }


    /**
     * Updates or creates a user's staff profile.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|StaffProfile
     */
    public function updateStaffProfile($data, $user) {
        DB::beginTransaction();

        try {
            if (!$user->isStaff) {
                throw new \Exception('You must be a current staff member to update a staff profile.');
            }

            // Look the profile up by its owner (the branch used find($user->id), i.e. the profile's own id)
            $staffProfile = StaffProfile::firstOrNew(['user_id' => $user->id]);
            $staffProfile->text = $data['text'] ?? null;
            $staffProfile->save();

            return $this->commitReturn($staffProfile);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates or creates a user's staff contact links.
     *
     * @param array $data
     * @param User  $user
     *
     * @return bool|StaffProfile
     */
    public function updateStaffLinks($data, $user) {
        DB::beginTransaction();

        try {
            if (!$user->isStaff) {
                throw new \Exception('You must be a current staff member to update your staff links.');
            }

            $sites = [];
            $urls = [];
            foreach ($data['url'] ?? [] as $key => $url) {
                $url = trim((string) $url);
                if ($url === '') {
                    continue;
                }
                // Only real web links: blocks javascript:, data: and similar URLs in the public team page
                if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
                    throw new \Exception('Links must be full web addresses starting with http:// or https://.');
                }
                $sites[] = trim((string) ($data['site'][$key] ?? ''));
                $urls[] = $url;
            }

            $staffProfile = StaffProfile::firstOrNew(['user_id' => $user->id]);
            $staffProfile->contacts = count($urls) ? json_encode(['site' => $sites, 'url' => $urls]) : null;
            $staffProfile->save();

            return $this->commitReturn($staffProfile);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
    /**
     * Updates a user's username.
     *
     * @param string $username
     * @param User   $user
     *
     * @return bool
     */
    public function updateUsername($username, $user) {
        DB::beginTransaction();

        try {
            if (!config('lorekeeper.settings.allow_username_changes')) {
                throw new \Exception('Username changes are currently disabled.');
            }
            if (!$username) {
                throw new \Exception('Please enter a username.');
            }
            if (strlen($username) < 3 || strlen($username) > 25) {
                throw new \Exception('Username must be between 3 and 25 characters.');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                throw new \Exception('Username must only contain letters, numbers, and underscores.');
            }
            if ($username == $user->name) {
                throw new \Exception('Username cannot be the same as your current username.');
            }
            if (User::where('name', $username)->where('id', '!=', $user->id)->first()) {
                throw new \Exception('Username already taken.');
            }
            // check if there is a cooldown
            if (config('lorekeeper.settings.username_change_cooldown')) {
                // these logs are different to the ones in the admin panel
                // different type
                $last_change = UserUpdateLog::where('user_id', $user->id)->where('type', 'Username Change')->orderBy('created_at', 'desc')->first();
                if ($last_change && $last_change->created_at->diffInDays(Carbon::now()) < config('lorekeeper.settings.username_change_cooldown')) {
                    throw new \Exception('You must wait '
                        .config('lorekeeper.settings.username_change_cooldown') - $last_change->created_at->diffInDays(Carbon::now()).
                    ' days before changing your username again.');
                }
            }

            // create log
            UserUpdateLog::create([
                'staff_id' => null,
                'user_id'  => $user->id,
                'data'     => json_encode(['old_name' => $user->name, 'new_name' => $username]),
                'type'     => 'Username Change',
            ]);

            $user->name = $username;
            $user->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Bans a user.
     *
     * @param array $data
     * @param User  $user
     * @param User  $staff
     *
     * @return bool
     */
    public function ban($data, $user, $staff) {
        DB::beginTransaction();

        try {
            if (!$user->is_banned) {
                // New ban (not just editing the reason), clear all their engagements
                if (!$this->logAdminAction($staff, 'Banned User', 'Banned '.$user->displayname)) {
                    throw new \Exception('Failed to log admin action.');
                }

                // 1. Character transfers
                $characterManager = new CharacterManager;
                $transfers = CharacterTransfer::where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->where('status', 'Pending')->get();
                foreach ($transfers as $transfer) {
                    $characterManager->processTransferQueue(['transfer' => $transfer, 'action' => 'Reject', 'reason' => ($transfer->sender_id == $user->id ? 'Sender' : 'Recipient').' has been banned from site activity.'], $staff);
                }

                // 2. Submissions and claims
                $submissionManager = new SubmissionManager;
                $submissions = Submission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($submissions as $submission) {
                    $submissionManager->rejectSubmission(['submission' => $submission, 'staff_comments' => 'User has been banned from site activity.'], $staff);
                }

                // 3. Gallery Submissions
                $galleryManager = new GalleryManager;
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($gallerySubmissions as $submission) {
                    $galleryManager->rejectSubmission($submission, $staff);
                    $galleryManager->postStaffComments($submission->id, ['staff_comments' => 'User has been banned from site activity.'], $staff);
                }
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Accepted')->get();
                foreach ($gallerySubmissions as $submission) {
                    $submission->update(['is_visible' => 0]);
                }

                // 4. Design approvals
                $requests = CharacterDesignUpdate::where('user_id', $user->id)->where(function ($query) {
                    $query->where('status', 'Pending')->orWhere('status', 'Draft');
                })->get();
                foreach ($requests as $request) {
                    (new DesignUpdateManager)->rejectRequest(['staff_comments' => 'User has been banned from site activity.'], $request, $staff, true);
                }

                // 5. Trades
                $tradeManager = new TradeManager;
                $trades = Trade::where(function ($query) {
                    $query->where('status', 'Open')->orWhere('status', 'Pending');
                })->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->where('recipient_id', $user->id);
                })->get();
                foreach ($trades as $trade) {
                    $tradeManager->rejectTrade(['trade' => $trade, 'reason' => 'User has been banned from site activity.'], $staff);
                }

                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['is_banned' => 'Yes', 'ban_reason' => $data['ban_reason'] ?? null]), 'type' => 'Ban']);

                $user->settings->banned_at = Carbon::now();

                $user->is_banned = 1;
                $user->rank_id = Rank::orderBy('sort')->first()->id;
                $user->save();
            } else {
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['ban_reason' => $data['ban_reason'] ?? null]), 'type' => 'Ban Update']);
            }

            $user->settings->ban_reason = isset($data['ban_reason']) && $data['ban_reason'] ? $data['ban_reason'] : null;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Unbans a user.
     *
     * @param User $user
     * @param User $staff
     *
     * @return bool
     */
    public function unban($user, $staff) {
        DB::beginTransaction();

        try {
            if (!$this->logAdminAction($staff, 'Unbanned User', 'Unbanned '.$user->displayname)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($user->is_banned) {
                $user->is_banned = 0;
                $user->save();

                $user->settings->ban_reason = null;
                $user->settings->banned_at = null;
                $user->settings->save();
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['is_banned' => 'No']), 'type' => 'Unban']);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deactivates a user.
     *
     * @param array $data
     * @param User  $user
     * @param User  $staff
     *
     * @return bool
     */
    public function deactivate($data, $user, $staff = null) {
        DB::beginTransaction();

        try {
            if (!$staff) {
                $staff = $user;
            }
            if (!$user->is_deactivated) {
                // New deactivation (not just editing the reason), clear all their engagements

                // 1. Character transfers
                $characterManager = new CharacterManager;
                $transfers = CharacterTransfer::where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id);
                })->where('status', 'Pending')->get();
                foreach ($transfers as $transfer) {
                    $characterManager->processTransferQueue(['transfer' => $transfer, 'action' => 'Reject', 'reason' => ($transfer->sender_id == $user->id ? 'Sender' : 'Recipient').'\'s account was deactivated.'], ($staff ? $staff : $user));
                }

                // 2. Submissions and claims
                $submissionManager = new SubmissionManager;
                $submissions = Submission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($submissions as $submission) {
                    $submissionManager->rejectSubmission(['submission' => $submission, 'staff_comments' => 'User\'s account was deactivated.'], $staff);
                }

                // 3. Gallery Submissions
                $galleryManager = new GalleryManager;
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Pending')->get();
                foreach ($gallerySubmissions as $submission) {
                    $galleryManager->rejectSubmission($submission, $staff);
                    $galleryManager->postStaffComments($submission->id, ['staff_comments' => 'User\'s account was deactivated.'], $staff);
                }
                $gallerySubmissions = GallerySubmission::where('user_id', $user->id)->where('status', 'Accepted')->get();
                foreach ($gallerySubmissions as $submission) {
                    $submission->update(['is_visible' => 0]);
                }

                // 4. Design approvals
                $requests = CharacterDesignUpdate::where('user_id', $user->id)->where(function ($query) {
                    $query->where('status', 'Pending')->orWhere('status', 'Draft');
                })->get();
                foreach ($requests as $request) {
                    (new DesignUpdateManager)->rejectRequest(['staff_comments' => 'User\'s account was deactivated.'], $request, $staff, true);
                }

                // 5. Trades
                $tradeManager = new TradeManager;
                $trades = Trade::where(function ($query) {
                    $query->where('status', 'Open')->orWhere('status', 'Pending');
                })->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)->where('recipient_id', $user->id);
                })->get();
                foreach ($trades as $trade) {
                    $tradeManager->rejectTrade(['trade' => $trade, 'reason' => 'User\'s account was deactivated.'], $staff);
                }

                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['is_deactivated' => 'Yes', 'deactivate_reason' => $data['deactivate_reason'] ?? null]), 'type' => 'Deactivation']);

                $user->settings->deactivated_at = Carbon::now();

                $user->is_deactivated = 1;
                $user->deactivater_id = $staff->id;
                $user->rank_id = Rank::orderBy('sort')->first()->id;
                $user->save();

                Notifications::create('USER_DEACTIVATED', User::find(Settings::get('admin_user')), [
                    'user_url'   => $user->url,
                    'user_name'  => $user->name,
                    'staff_url'  => $staff->url,
                    'staff_name' => $staff->name,
                ]);
            } else {
                UserUpdateLog::create(['staff_id' => $staff->id, 'user_id' => $user->id, 'data' => json_encode(['deactivate_reason' => $data['deactivate_reason'] ?? null]), 'type' => 'Deactivation Update']);
            }

            $user->settings->deactivate_reason = isset($data['deactivate_reason']) && $data['deactivate_reason'] ? $data['deactivate_reason'] : null;
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Reactivates a user account.
     *
     * @param User $user
     * @param User $staff
     *
     * @return bool
     */
    public function reactivate($user, $staff = null) {
        DB::beginTransaction();

        try {
            if (!$staff) {
                $staff = $user;
            }
            if ($user->is_deactivated) {
                $user->is_deactivated = 0;
                $user->deactivater_id = null;
                $user->save();

                $user->settings->deactivate_reason = null;
                $user->settings->deactivated_at = null;
                $user->settings->save();
                UserUpdateLog::create(['staff_id' => $staff ? $staff->id : $user->id, 'user_id' => $user->id, 'data' => json_encode(['is_deactivated' => 'No']), 'type' => 'Reactivation']);
            }

            Notifications::create('USER_REACTIVATED', User::find(Settings::get('admin_user')), [
                'user_url'   => $user->url,
                'user_name'  => ucfirst($user->name),
                'staff_url'  => $staff->url,
                'staff_name' => $staff->name,
            ]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a user page.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     * @param mixed                 $isStaff
     *
     * @return \App\Models\UserPage|bool
     */
    public function createUserPage($data, $user, $isStaff) {
        DB::beginTransaction();

        try {
            $data['user_id'] = $user->id;
            if (!$isStaff && ($user->pages->count() >= config('lorekeeper.user_pages.user_page_limit'))) {
                throw new \Exception('You have already created the maximum amount of user pages.');
            } elseif ($isStaff && $user->pages->count() >= config('lorekeeper.user_pages.staff_page_limit')) {
                throw new \Exception('You have already created the maximum amount of user pages.');
            }
            if (trim($data['key']) == '' || str_replace(' ', '-', trim($data['key'])) == '') {
                throw new \Exception('Key cannot be an empty string.');
            }
            $data['key'] = str_replace(' ', '-', trim($data['key']));
            if (UserPage::where([['key', '=', $data['key']], ['user_id', '=', $user->id]])->exists()) {
                throw new \Exception('You are already using this key for a user page.');
            }

            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            } else {
                $data['parsed_text'] = null;
                $data['text'] = null;
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['show_on_profile'])) {
                $data['show_on_profile'] = 0;
            }
            if (!isset($data['can_comment'])) {
                $data['can_comment'] = 0;
            }
            if (!isset($data['logged_in_only'])) {
                $data['logged_in_only'] = 0;
            }

            $page = UserPage::create($data);

            return $this->commitReturn($page);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a user page.
     *
     * @param array $data
     * @param User  $user
     * @param mixed $page
     *
     * @return \App\Models\UserPage|bool
     */
    public function updateUserPage($page, $data, $user) {
        DB::beginTransaction();

        try {
            if (($page->user_id != $user->id) && !$user->isStaff) {
                throw new \Exception('You cannot edit pages that are not your own.');
            }
            if (trim($data['key']) == '' || str_replace(' ', '-', trim($data['key'])) == '') {
                throw new \Exception('Key cannot be an empty string.');
            }
            $data['key'] = str_replace(' ', '-', trim($data['key']));
            if (UserPage::where([['key', '=', $data['key']], ['id', '!=', $page->id], ['user_id', '=', $user->id]])->exists()) {
                throw new \Exception('You are already using this key for a user page.');
            }

            if (isset($data['text']) && $data['text']) {
                $data['parsed_text'] = parse($data['text']);
            } else {
                $data['parsed_text'] = null;
                $data['text'] = null;
            }

            if (!isset($data['is_visible'])) {
                $data['is_visible'] = 0;
            }
            if (!isset($data['show_on_profile'])) {
                $data['show_on_profile'] = 0;
            }
            if (!isset($data['can_comment'])) {
                $data['can_comment'] = 0;
            }
            if (!isset($data['logged_in_only'])) {
                $data['logged_in_only'] = 0;
            }

            $page->update($data);

            return $this->commitReturn($page);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a user page.
     *
     * @param mixed $page
     * @param mixed $user
     * @param mixed $isStaff
     *
     * @return bool
     */
    public function deleteUserPage($page, $user, $isStaff = false) {
        DB::beginTransaction();

        try {
            if (!config('lorekeeper.user_pages.allow_deletion.enabled')) {
                throw new \Exception('User pages cannot be fully deleted.');
            }
            if (($page->user_id != $user->id) && !$isStaff) {
                throw new \Exception('You cannot delete pages that are not your own.');
            }

            if (!config('lorekeeper.user_pages.allow_deletion.force_delete')) {
                $page->delete();
            } else {
                $page->forceDelete();
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Change character like settings.
     *
     * @param mixed $data
     * @param User  $user
     *
     * @return bool
     */
    public function updateAllowCharacterLikes($data, $user) {
        $user->settings->allow_character_likes = $data;
        $user->settings->save();

        return true;
    }

    /**
     * Updates the user's border.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return bool
     */
    public function updateBorder($data, $user) {
        DB::beginTransaction();

        try {
            $borderId = (int) ($data['border'] ?? 0);
            $variantId = (int) ($data['border_variant_id'] ?? 0);
            $bottomId = (int) ($data['bottom_border_id'] ?? 0);
            $topId = (int) ($data['top_border_id'] ?? 0);

            // TFN: variants and layers must belong to the chosen main border,
            // and ownership/active/staff-only checks apply to every part.
            $border = null;
            if ($borderId) {
                $border = Border::base()->where('id', $borderId)->first();
                if (!$border) {
                    throw new \Exception('Invalid border selected.');
                }
                $this->checkBorderUsable($border, $border, $user);
            }

            if ($variantId) {
                $variant = $border ? Border::where('id', $variantId)->where('parent_id', $border->id)->where('border_type', 'variant')->first() : null;
                if (!$variant) {
                    throw new \Exception('Invalid border variant selected.');
                }
                $this->checkBorderUsable($variant, $border, $user);
            }

            if ((bool) $bottomId != (bool) $topId) {
                throw new \Exception('You must select both a top border and a bottom border.');
            }
            if ($bottomId) {
                $bottom = $border ? Border::where('id', $bottomId)->where('parent_id', $border->id)->where('border_type', 'bottom')->first() : null;
                if (!$bottom) {
                    throw new \Exception('That bottom border does not exist.');
                }
                $top = Border::where('id', $topId)->where('parent_id', $border->id)->where('border_type', 'top')->first();
                if (!$top) {
                    throw new \Exception('That top border does not exist.');
                }
                $this->checkBorderUsable($bottom, $border, $user);
                $this->checkBorderUsable($top, $border, $user);
            }

            $user->border_id = $borderId ?: null;
            $user->border_variant_id = $variantId ?: null;
            $user->bottom_border_id = $bottomId ?: null;
            $user->top_border_id = $topId ?: null;
            $user->save();

            $user->settings->border_settings = [
                'border_flip' => isset($data['border_flip']) && $data['border_flip'] ? 1 : 0,
            ];
            $user->settings->save();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Throws if a non-staff user may not use this border (or a variant/layer of $parent).
     *
     * @param \App\Models\Border\Border $border
     * @param \App\Models\Border\Border $parent
     * @param \App\Models\User\User     $user
     */
    private function checkBorderUsable($border, $parent, $user) {
        if ($user->isStaff) {
            return;
        }
        if ($parent->admin_only) {
            throw new \Exception('You cannot select a staff border.');
        }
        if (!$parent->is_default && !$user->hasBorder($parent->id)) {
            throw new \Exception('You do not own this border.');
        }
        if (!$border->is_active || !$parent->is_active) {
            throw new \Exception('This border is not active.');
        }
    }
}
