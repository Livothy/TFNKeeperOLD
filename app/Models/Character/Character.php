<?php

namespace App\Models\Character;

use App\Facades\Notifications;
use App\Models\Base\Base;
use App\Models\Carrier\Carrier;
use App\Models\Award\Award;
use App\Models\Award\AwardLog;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyLog;
use App\Models\Gallery\GalleryCharacter;
use App\Models\Item\Item;
use App\Models\Item\ItemLog;
use App\Models\Marking\Marking;
use App\Models\Model;
use App\Models\Rarity;
use App\Models\Submission\Submission;
use App\Models\Submission\SubmissionCharacter;
use App\Models\Trade;
use App\Models\User\User;
use App\Models\User\UserCharacterLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Settings;

class Character extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_image_id', 'character_category_id', 'rarity_id', 'user_id',
        'owner_alias', 'number', 'slug', 'description', 'parsed_description',
        'is_sellable', 'is_tradeable', 'is_giftable',
        'sale_value', 'transferrable_at', 'is_visible',
        'is_gift_art_allowed', 'is_gift_writing_allowed', 'is_trading', 'sort',
        'is_myo_slot', 'name', 'trade_id', 'owner_url', 'base',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'characters';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'transferrable_at' => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Accessors to append to the model.
     *
     * @var array
     */
    public $appends = ['is_available'];

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'character_category_id' => 'required',
        'rarity_id'             => 'required',
        'user_id'               => 'nullable',
        'number'                => 'required',
        'slug'                  => 'required|alpha_dash',
        'description'           => 'nullable',
        'sale_value'            => 'nullable|decimal:0,2',
        'image'                 => 'required|mimes:jpeg,jpg,gif,png|max:2048',
        'thumbnail'             => 'nullable|mimes:jpeg,jpg,gif,png|max:2048',
        'owner_url'             => 'url|nullable',
        'base'                  => 'nullable',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'character_category_id' => 'required',
        'number'                => 'required',
        'slug'                  => 'required',
        'description'           => 'nullable',
        'sale_value'            => 'nullable|decimal:0,2',
        'image'                 => 'nullable|mimes:jpeg,jpg,gif,png|max:2048',
        'thumbnail'             => 'nullable|mimes:jpeg,jpg,gif,png|max:2048',
        'base'                  => 'nullable',
    ];

    /**
     * Validation rules for MYO slots.
     *
     * @var array
     */
    public static $myoRules = [
        'rarity_id'   => 'nullable',
        'user_id'     => 'nullable',
        'number'      => 'nullable',
        'slug'        => 'nullable',
        'description' => 'nullable',
        'sale_value'  => 'nullable|decimal:0,2',
        'name'        => 'required',
        'image'       => 'nullable|mimes:jpeg,gif,png|max:2048',
        'thumbnail'   => 'nullable|mimes:jpeg,gif,png|max:2048',
        'base'        => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the character.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category the character belongs to.
     */
    public function category() {
        return $this->belongsTo(CharacterCategory::class, 'character_category_id');
    }

    /**
     * Get the masterlist image of the character.
     */
    public function image() {
        return $this->belongsTo(CharacterImage::class, 'character_image_id');
    }

    /**
     * Get all images associated with the character.
     *
     * @param mixed|null $user
     */
    public function images($user = null) {
        return $this->hasMany(CharacterImage::class, 'character_id')->images($user);
    }

    /**
     * Get the user-editable profile data of the character.
     */
    public function profile() {
        return $this->hasOne(CharacterProfile::class, 'character_id');
    }

    /**
     * Get the character's active design update.
     */
    public function designUpdate() {
        return $this->hasMany(CharacterDesignUpdate::class, 'character_id');
    }

    /**
     * Get the trade this character is attached to.
     */
    public function trade() {
        return $this->belongsTo(Trade::class, 'trade_id');
    }

    /**
     * Get the rarity of this character.
     */
    public function rarity() {
        return $this->belongsTo(Rarity::class, 'rarity_id');
    }

    /**
     * Get the character's associated gallery submissions.
     */
    public function gallerySubmissions() {
        return $this->hasMany(GalleryCharacter::class, 'character_id');
    }

    /**
     * Get the character's items.
     */
    public function items() {
        return $this->belongsToMany(Item::class, 'character_items')->withPivot('count', 'data', 'updated_at', 'id', 'stack_name')->whereNull('character_items.deleted_at');
    }

    /**
     * Get all of the likes that are not NULL.
     */
    public function characterLikes() {
        return $this->hasMany('App\Models\Character\CharacterLike')->where('character_id', $this->id)->whereNotNull('liked_at');
    }

    /**
     * Get the character's awards.
     */
    public function awards() {
        return $this->belongsToMany('App\Models\Award\Award', 'character_awards')->withPivot('count', 'data', 'updated_at', 'id')->whereNull('character_awards.deleted_at');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include either characters of MYO slots.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $isMyo
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMyo($query, $isMyo = false) {
        return $query->where('is_myo_slot', $isMyo);
    }

    /**
     * Scope a query to only include visible characters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('manage_characters')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to only include characters that the owners are interested in trading.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTrading($query) {
        return $query->where('is_trading', 1);
    }

    /**
     * Scope a query to only include characters that can be traded.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTradable($query) {
        return $query->where(function ($query) {
            $query->whereNull('transferrable_at')->orWhere('transferrable_at', '<', Carbon::now());
        })->where(function ($query) {
            $query->where('is_sellable', 1)->orWhere('is_tradeable', 1)->orWhere('is_giftable', 1);
        });
    }

    /**
     * Scope a query to only include characters that have the selected markings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $markingIds
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMarkings($query, $markingIds) {
        return $query->join('character_markings', 'characters.id', '=', 'character_markings.character_id')
            ->whereIn('character_markings.marking_id', $markingIds)
            ->select('characters.*')
            ->distinct();
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the character's availability for activities/transfer.
     *
     * @return bool
     */
    public function getIsAvailableAttribute() {
        if ($this->designUpdate()->active()->exists()) {
            return false;
        }
        if ($this->trade_id) {
            return false;
        }
        if (CharacterTransfer::active()->where('character_id', $this->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Display the owner's name.
     * If the owner is not a registered user on the site, this displays the owner's dA name.
     *
     * @return string
     */
    public function getDisplayOwnerAttribute() {
        if ($this->user_id) {
            return $this->user->displayName;
        } else {
            return prettyProfileLink($this->owner_url);
        }
    }

    /**
     * Gets the character's code.
     * If this is a MYO slot, it will return the MYO slot's name.
     *
     * @return string
     */
    public function getSlugAttribute() {
        if ($this->is_myo_slot) {
            return $this->name;
        } else {
            return $this->attributes['slug'];
        }
    }

    /**
     * Displays the character's name, linked to their character page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-character">'.$this->fullName.'</a>';
    }

    /**
     * Gets the character's name, including their code and user-assigned name.
     * If this is a MYO slot, simply returns the slot's name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        if ($this->is_myo_slot) {
            return $this->name;
        } else {
            return $this->slug.($this->name ? ': '.$this->name : '');
        }
    }

    /**
     * Gets the character's page's URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        if ($this->is_myo_slot) {
            return url('myo/'.$this->id);
        } else {
            return url('character/'.$this->slug);
        }
    }

    /**
     * Gets the character's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'characters';
    }

    /**
     * Gets the character's log type for log creation.
     *
     * @return string
     */
    public function getLogTypeAttribute() {
        return 'Character';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if the character's owner has registered on the site and updates ownership accordingly.
     */
    public function updateOwner() {
        // Return if the character has an owner on the site already.
        if ($this->user_id) {
            return;
        }

        // Check if the owner has an account and update the character's user ID for them.
        $owner = checkAlias($this->owner_url);
        if (is_object($owner)) {
            $this->user_id = $owner->id;
            $this->owner_url = null;
            $this->save();

            $owner->settings->is_fto = 0;
            $owner->settings->save();
        }
    }

    /**
     * Get the character's held currencies.
     *
     * @param bool $showAll
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCurrencies($showAll = false) {
        // Get a list of currencies that need to be displayed
        // On profile: only ones marked is_displayed
        // In bank: ones marked is_displayed + the ones the user has

        $owned = CharacterCurrency::where('character_id', $this->id)->pluck('quantity', 'currency_id')->toArray();

        $currencies = Currency::where('is_character_owned', 1);
        if ($showAll) {
            $currencies->where(function ($query) use ($owned) {
                $query->where('is_displayed', 1)->orWhereIn('id', array_keys($owned));
            });
        } else {
            $currencies = $currencies->where('is_displayed', 1);
        }

        $currencies = $currencies->orderBy('sort_character', 'DESC')->get();

        foreach ($currencies as $currency) {
            $currency->quantity = $owned[$currency->id] ?? 0;
        }

        return $currencies;
    }

    /**
     * Get the character's held currencies as an array for select inputs.
     *
     * @return array
     */
    public function getCurrencySelect() {
        return CharacterCurrency::where('character_id', $this->id)->leftJoin('currencies', 'character_currencies.currency_id', '=', 'currencies.id')->orderBy('currencies.sort_character', 'DESC')->get()->pluck('name_with_quantity', 'currency_id')->toArray();
    }

    /**
     * Get the character's currency logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getCurrencyLogs($limit = 10) {
        $character = $this;
        $query = CurrencyLog::with('currency')->where(function ($query) use ($character) {
            $query->with('sender.rank')->where('sender_type', 'Character')->where('sender_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient.rank')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's item logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getItemLogs($limit = 10) {
        $character = $this;

        $query = ItemLog::with('item')->where(function ($query) use ($character) {
            $query->with('sender.rank')->where('sender_type', 'Character')->where('sender_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient.rank')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');

        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's award logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getAwardLogs($limit = 10) {
        $character = $this;

        $query = AwardLog::with('award')->where(function ($query) use ($character) {
            $query->with('sender.rank')->where('sender_type', 'Character')->where('sender_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient.rank')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');

        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's ownership logs.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOwnershipLogs() {
        $query = UserCharacterLog::with('sender.rank')->with('recipient.rank')->where('character_id', $this->id)->orderBy('id', 'DESC');

        return $query->paginate(30);
    }

    /**
     * Get the character's update logs.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCharacterLogs() {
        $query = CharacterLog::with('sender.rank')->where('character_id', $this->id)->orderBy('id', 'DESC');

        return $query->paginate(30);
    }

    /**
     * Get submissions that the character has been included in.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getSubmissions() {
        return Submission::with('user.rank')->with('prompt')->where('status', 'Approved')->whereIn('id', SubmissionCharacter::where('character_id', $this->id)->pluck('submission_id')->toArray())->paginate(30);

        // Untested
        // $character = $this;
        // return Submission::where('status', 'Approved')->with(['characters' => function($query) use ($character) {
        //    $query->where('submission_characters.character_id', 1);
        // }])
        // ->whereHas('characters', function($query) use ($character) {
        //    $query->where('submission_characters.character_id', 1);
        // });
        // return Submission::where('status', 'Approved')->where('user_id', $this->id)->orderBy('id', 'DESC')->paginate(30);
    }

    /**
     * Notifies character's bookmarkers in case of a change.
     *
     * @param mixed $type
     */
    public function notifyBookmarkers($type) {
        // Bookmarkers will not be notified if the character is set to not visible
        if ($this->is_visible) {
            $column = null;
            switch ($type) {
                case 'BOOKMARK_TRADING':
                    $column = 'notify_on_trade_status';
                    break;
                case 'BOOKMARK_GIFTS':
                    $column = 'notify_on_gift_art_status';
                    break;
                case 'BOOKMARK_GIFT_WRITING':
                    $column = 'notify_on_gift_writing_status';
                    break;
                case 'BOOKMARK_OWNER':
                    $column = 'notify_on_transfer';
                    break;
                case 'BOOKMARK_IMAGE':
                    $column = 'notify_on_image';
                    break;
            }

            // The owner of the character themselves will not be notified, in the case that
            // they still have a bookmark on the character after it was transferred to them
            $bookmarkers = CharacterBookmark::where('character_id', $this->id)->where('user_id', '!=', $this->user_id);
            if ($column) {
                $bookmarkers = $bookmarkers->where($column, 1);
            }

            $bookmarkers = User::whereIn('id', $bookmarkers->pluck('user_id')->toArray())->get();

            // This may have to be redone more efficiently in the case of large numbers of bookmarkers,
            // but since we're not expecting many users on the site to begin with it should be fine
            foreach ($bookmarkers as $bookmarker) {
                Notifications::create($type, $bookmarker, [
                    'character_url'  => $this->url,
                    'character_name' => $this->fullName,
                ]);
            }
        }
    }

    /**********************************************************************************************

        CHARACTER GENO/PHENO

    **********************************************************************************************/

    /**
     * Gets the final array for the character genome.
     *
     * @return array
     */
    public function getMarkingFinalArray() {
        $markings = CharacterMarking::where('character_id', $this->id)->get();

        if (!$markings->count()) {
            //If no markings, return null
            return null;
        }

        $rendered = [];
        foreach ($markings as $marking) {
            $temp = Marking::where('id', $marking->marking_id)->first();
            $rendered['is_chimera'] = $marking->data === null ? 0 : 1;
            $has_multi_bases = false;
            $marking_data = $marking->data ?? 0;
            if ($marking->carrier_id) {
                //Add carriers if needed
                $rendered['carriers'][$marking->carrier_id] = Carrier::where('id', $marking->carrier_id)->pluck('name')->toArray();
            }
            if (str_contains($marking->base_id, '|')) {
                $has_multi_bases = true;
                $multi_bases = explode('|', $marking->base_id);
                foreach ($multi_bases as $base) {
                    $temp_bases[] = Base::where('id', $base)->pluck('code', 'name')->toArray();
                }
            }
            if ($temp) {
                if ($rendered['is_chimera'] === 1) {
                    $marksrender[$marking_data][$temp->order_in_genome][] = [
                        'name'          => $temp->name,
                        'is_dominant'   => $marking->is_dominant,
                        'code'          => $marking->code,
                        'link'          => $temp->getUrlAttribute(),
                        'base_info'     => $has_multi_bases ? $temp_bases : Base::where('id', $marking->base_id)->pluck('code', 'name')->toArray(),
                    ];
                } else {
                    $marksrender[0][$temp->order_in_genome][] = [
                        'name'          => $temp->name,
                        'is_dominant'   => $marking->is_dominant,
                        'code'          => $marking->code,
                        'link'          => $temp->getUrlAttribute(),
                        'base_info'     => $has_multi_bases ? $temp_bases : Base::where('id', $marking->base_id)->pluck('code', 'name')->toArray(),
                    ];
                }
            }
        }
        ksort($marksrender);
        $rendered['markings'] = $marksrender;

        return $rendered;
    }

    /**
     * Gets the phenotype for the character genome.
     *
     * @param mixed $markings
     * @param mixed $type
     *
     * @return string
     */
    public function getMarkingLinkedArray($markings, $type = 'phenotype') {
        $bases = $this->getBaseCoat();

        if ($markings === null || count($markings) < 1) {
            if (count($bases) > 1 && array_key_exists(1, $bases)) {
                //If Chimera and no marks
                if ($type === 'phenotype') {
                    return $bases[0]['name'].' // '.$bases[1]['name'];
                } else {
                    return $bases[0]['code'].'+//'.$bases[1]['code'].'+';
                }
            } elseif (count($bases) > 1 && !array_key_exists(0, $bases)) {
                //This shit is a mess, I know, I'm tired, no switchcase here :(
                if ($type === 'phenotype') {
                    return $bases['name'];
                } else {
                    return $bases['code'].'+';
                }
            } else {
                if ($type === 'phenotype') {
                    if (array_key_exists(0, $bases)) {
                        return $bases[0]['name'];
                    }

                    return $bases['name'];
                } else {
                    if (array_key_exists(0, $bases)) {
                        return $bases[0]['code'].'+';
                    }

                    return $bases['code'].'+';
                }
            }
        }

        if (!is_array($markings) || !array_key_exists('markings', $markings)) {
            return 'Unknown';
        }

        $chimera = false;
        if (array_key_exists('is_chimera', $markings) && $markings['is_chimera'] == 1) {
            $chimera = true;
            $geno_sides = [];
        }

        $html_inner = [];

        //return print_r($markings, true);

        foreach ($markings['markings'] as $side => $group) {
            //Sides - Max of 2 for chimera
            $sideInner = $this->handleMarkingGroup($group, $type);
            $geno_sides[$side] = $sideInner;

            //Get the bases per side
            $geno_sides[$side][2] = $bases[0];
            ksort($geno_sides[$side]);
        }
        if (!isset($geno_sides[1]) && isset($bases[1])) {
            //If secondary side is without markings
            $geno_sides[1][2] = $bases[1];
        }
        if (!isset($geno_sides[0]) && isset($bases[0])) {
            //If primary side is without markings
            $geno_sides[0][2] = $bases[0];
        }
        foreach ($geno_sides as $i => $side) {
            $html_inner[] = $this->renderFinalMarkingOutput($side, $type);
        }
        if (count($html_inner) == 2) {
            $seperator = ($type == 'phenotype' ? ' // ' : '//');
            $html_inner = implode($seperator, $html_inner);
        } else {
            $html_inner = implode('', $html_inner);
        }

        return $html_inner;
    }

    public function handleMarkingGroup($group, $type = 'phenotype') {
        $html_inner = [];
        foreach ($group as $id => $order_group) {
            //Inside each order group, we have the markings
            foreach ($order_group as $marking) {
                //Individual markings
                $html_inner[$id][] = $this->renderIndividualMarking($marking, $type);
            }
        }

        return $html_inner;
    }

    /**
     * Gets the individual gene template for the marking string.
     *
     * @param mixed $marking
     * @param mixed $type
     *
     * @return string
     */
    public function renderIndividualMarking($marking, $type = 'phenotype') {
        $marking = (object) $marking;
        $url = $marking->link;

        switch ($type) {
            case 'phenotype':
                $text = $marking->name;
                break;
            case 'genotype':
                $text = $marking->code;
                break;
            default:
                $text = $marking->name;
        }

        return '<a href="'.$url.'" class="marking-link">'.$text.'</a>';
    }

    /**
     * Gets the genotype for the character genome.
     *
     * @param mixed $array
     * @param mixed $type
     *
     * @return string
     */
    public function renderFinalMarkingOutput($array, $type = 'phenotype') {
        if (!is_array($array)) {
            return 'Unknown';
        }

        switch ($type) {
            case 'phenotype':
                $base = $array[2] ?? [0 => ['name' => 'Unknown', 'code' => '??']];
                unset($array[2]);

                foreach ($array as $order => $marking_group) {
                    foreach ($marking_group as $marking) {
                        if ($order < 2) {
                            $temp[0][] = $marking;
                        } elseif ($order > 2 && $order < 9) {
                            $temp[1][] = $marking;
                        } elseif ($order >= 9) {
                            $temp[2][] = $marking;
                        }
                    }
                }
                $html = (isset($temp[0]) && count($temp[0]) > 0 ? implode(' ', $temp[0]).' ' : '');
                $html .= $base['name'];
                if (isset($temp[1]) && count($temp[1]) > 0) {
                    $html .= ' with '.implode(' ', $temp[1]);
                }
                $html .= isset($temp[2]) ? ' and '.implode(' ', $temp[2]) : '';

                break;
            case 'genotype':
                $html = $array[2]['code'].'+';
                unset($array[2]);

                foreach ($array as $order => $marking_group) {
                    foreach ($marking_group as $marking) {
                        if ($order < 9) {
                            $temp[0][] = $marking;
                        } elseif ($order >= 9) {
                            $temp[1][] = $marking;
                        }
                    }
                }
                if (!isset($temp)) {
                    $temp = [];
                }
                $html .= (isset($temp[0]) && count($temp[0]) > 0 ? implode('/', $temp[0]) : '');
                $html .= (array_key_exists(1, $temp) && count($temp[1]) > 0 ? '/'.implode('/', $temp[1]) : '');

                break;
        }

        return '<span class="marking-output">'.$html.'</span>';
    }

    /**
     * Gets the genotype for the character genome.
     *
     * @return array
     */
    public function getBaseCoat() {
        if (str_contains($this->base, '|')) {
            $chimera_bases = [];
            $bases = explode('|', $this->base);
            if (count($bases) > 1) {
                foreach ($bases as $b) {
                    $temp = Base::where('id', $b)->first();
                    $chimera_bases[] = [
                        'name' => $temp->name,
                        'code' => $temp->code,
                    ];
                }

                return $chimera_bases;
            }
        }

        //If Not Chimera, Continue as normal
        $base = Base::where('id', $this->base)->first();

        if (!$base) {
            return [
                [
                    'name'  => 'Unknown',
                    'code'  => '??',
                ],
            ];
        }

        return [0 => [
            'name'  => $base->name,
            'code'  => $base->code,
        ]];
    }

    /**
     * Return like count based on site setting.
     *
     * Will always return the accurate count even if settings are flip flopped around (i am paranoid.)
     */
    public function getLikeTotalAttribute() {
        // can like only once
        if (!Settings::get('character_likes')) {
            return $this->characterLikes->count();
        } else {
            return $this->profile->like_count;
        }
    }
}
