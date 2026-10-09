<?php

namespace App\Models\Raid;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RaidBoss extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'raid_id', 'description', 'parsed_description', 'data',
        'health', 'damage', 'is_visible', 'sort',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raid_bosses';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Validation rules for raid creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'               => 'required|between:3,100',
    ];

    /**
     * Validation rules for raid updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'               => 'required|between:3,100',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the raid this boss is attached to.
     */
    public function raid() {
        return $this->belongsTo(Raid::class, 'raid_id');
    }

    /**
     * Get the images that belong to this boss.
     */
    public function images() {
        return $this->hasMany(RaidBossImage::class, 'raid_boss_id')->whereNull('deleted_at');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible bosses.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->isStaff) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to sort bosses in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort bosses by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query, $reverse = false) {
        return $query->orderBy('id', $reverse ? 'ASC' : 'DESC');
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-raid">'.$this->name.'</a>';
    }

    /**
     * Gets the URL of the model's page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url(__('raids.raids').'/'.__('raids.bosses').'?name='.$this->name);
    }

    /**
     * Gets the URL of the model's page.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url(__('raids.raids').'/'.__('raids.boss').'/'.$this->id);
    }

    /**
     * Gets the current image of the boss.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->images->count()) {
            return null;
        } elseif ($this->images->count() == 1) {
            return $this->images->first()->imageUrl;
        }
        $bossImages = $this->images()->get()->sortByDesc('thresholdCalc');
        $imageKey = $bossImages->search(function ($boss, $key) {
            if ($this->remainingHealth <= $boss->thresholdCalc) {
                return $key;
            }

            return false;
        });

        if ($imageKey != false) {
            $image = $bossImages[$imageKey];
        } else {
            $image = $bossImages->first();
        }

        return $image->imageUrl;
    }

    /**
     * Gets the boss's HP thresholds.
     *
     * @return array
     */
    public function getThresholdsAttribute() {
        if (!isset($this->data) || !isset($this->data['thresholds'])) {
            return null;
        }

        return $this->data['thresholds'];
    }

    /**
     * Gets the raid boss's remaining health.
     *
     * @return int
     */
    public function getRemainingHealthAttribute() {
        $damageDone = $this->damage ?? 0;
        $remaining = $this->health - $damageDone;
        if ($remaining < 0) {
            return 0;
        }

        return $remaining;
    }

    /**
     * Gets the raid boss's remaining health.
     *
     * @return int
     */
    public function getRemainingHealthPercentageAttribute() {
        if (!$this->health) {
            return null;
        } elseif ($this->remainingHealth == 0) {
            return 100;
        } elseif ($this->remainingHealth == $this->health) {
            return 100;
        }
        $remaining = $this->remainingHealth;
        $division = $remaining / $this->health;
        $calc = $division * 100;

        return $calc;
    }

    /**
     * Gets the raid boss's health bar styling.
     *
     * @return string
     */
    public function getBarStylingAttribute() {
        if (!$this->thresholds) {
            return null;
        }
        $initialArray = $this->thresholds;
        foreach ($initialArray as $initialKey => $initialValue) {
            if ($initialValue['type'] == 'percent' && $initialValue['amount'] > 0) {
                $percent = $initialValue['amount'] / 100;
                $calc = $this->health * $percent;
                if (is_float($calc)) {
                    $calc = round($calc);
                }
            } else {
                $calc = $initialValue['amount'];
            }

            $initialArray[$initialKey]['calc'] = intval($calc);
        }

        $leftover = $this->remainingHealth;
        $styleCollect = collect($initialArray);
        $sortedStyles = $styleCollect->sortByDesc(function ($styleItem) {
            return $styleItem['calc'];
        });
        foreach ($sortedStyles as $styleKey => $styleItem) {
            if ($leftover < $styleItem['calc']) {
                $lastKey = $styleKey;
                unset($sortedStyles[$styleKey]);
            }
        }

        if (!$sortedStyles->count() && isset($lastKey)) {
            $style = $styleCollect[$lastKey];
        } elseif ($sortedStyles->count()) {
            $byHealth = $styleCollect->search(function ($style, $key) {
                if ($this->remainingHealth <= $style['calc']) {
                    return $key;
                }

                return false;
            });

            if ($byHealth != false) {
                $style = $styleCollect[$byHealth];
            }
        } else {
            $style = null;
        }

        if ($style) {
            $string = '';
            if (isset($style['bar_color'])) {
                $string .= 'background-color: '.$style['bar_color'].'; ';
            }
            if (isset($style['text_color'])) {
                $string .= 'color: '.$style['text_color'].';';
            }

            return $string;
        }

        return null;
    }

    /**
     * Gets the raid's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'bosses';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/'.__('raids.raid').'-'.__('raids.bosses').'/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'manage_raids';
    }
}
