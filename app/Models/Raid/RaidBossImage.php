<?php

namespace App\Models\Raid;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RaidBossImage extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'raid_boss_id', 'health_threshold', 'hash', 'extension', 'has_image', 'threshold_type',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'raid_boss_images';

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
        'image'              => 'required|mimes:png,gif,webp',
        'health_threshold'   => 'nullable|integer',
        'threshold_type'     => 'in:percent,amount',
    ];

    /**
     * Validation rules for raid updating.
     *
     * @var array
     */
    public static $updateRules = [
        'image'              => 'mimes:png,gif,webp',
        'health_threshold'   => 'nullable|integer',
        'threshold_type'     => 'in:percent,amount',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the boss this image is attached to.
     */
    public function boss() {
        return $this->belongsTo(RaidBoss::class, 'raid_boss_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort features by newest first.
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
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/raids/bosses';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->hash.$this->id.'-image.'.($this->extension ?? 'png');
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the threshold calculation based on
     * the boss's max HP.
     *
     * @return int
     */
    public function getThresholdCalcAttribute() {
        if (!isset($this->health_threshold)) {
            return 0;
        }

        if ($this->threshold_type == 'percent') {
            $totalHealth = $this->boss->health ?? null;
            if ($totalHealth) {
                if ($this->health_threshold == 100) {
                    $calc = $totalHealth;
                } else {
                    $percent = $this->health_threshold / 100;
                    $calc = $totalHealth * $percent;

                    if (is_float($calc)) {
                        $calc = round($calc);
                    }
                }

                return $calc;
            }
        } elseif ($this->threshold_type == 'amount') {
            $calc = $this->health_threshold ?? 0;

            return $calc;
        }

        return 0;
    }

    /**
     * Gets the threshold string.
     *
     * @return string
     */
    public function getThresholdStringAttribute() {
        if (isset($this->health_threshold)) {
            if ($this->threshold_type == 'percent') {
                return 'At '.$this->health_threshold.'% health '.'('.$this->thresholdCalc.' HP)';
            } elseif ($this->threshold_type == 'amount') {
                return 'At '.$this->health_threshold.' health';
            }
        }

        return 'Unknown';
    }
}
