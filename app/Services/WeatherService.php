<?php

namespace App\Services;

use App\Models\Weather\Weather;
use App\Models\Weather\WeatherSeason;
use App\Models\Weather\WeatherTable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Settings;

class WeatherService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Weather Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of weathers and seasons.
    |
    */

    /**********************************************************************************************

        SEASONS

    **********************************************************************************************/

    /**
     * Creates a season.
     *
     * @param array $data
     *
     * @return bool|WeatherSeason
     */
    public function createSeason($data) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $season = WeatherSeason::create($data);
            $this->populateSeason($season, Arr::only($data, ['weather_id', 'weight']));

            if ($image) {
                $this->handleImage($image, $season->imagePath, $season->imageFileName);
            }

            return $this->commitReturn($season);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a season.
     *
     * @param WeatherSeason $season
     * @param array         $data
     *
     * @return bool|WeatherSeason
     */
    public function updateSeason($season, $data) {
        DB::beginTransaction();

        try {
            if (!$season) {
                throw new \Exception('Invalid season selected.');
            }
            $data = $this->populateData($data, $season);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $season->update($data);
            $this->populateSeason($season, Arr::only($data, ['weather_id', 'weight']));

            if ($image) {
                $this->handleImage($image, $season->imagePath, $season->imageFileName);
            }

            return $this->commitReturn($season);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a season.
     *
     * @param WeatherSeason $season
     *
     * @return bool
     */
    public function deleteSeason($season) {
        DB::beginTransaction();

        try {
            if (!$season) {
                throw new \Exception('Invalid season selected.');
            }
            if (Settings::get('site_season') == $season->id) {
                throw new \Exception("The site's season is currently set to this season. Change the season first.");
            }

            $season->loot()->delete();
            if ($season->has_image && file_exists($season->imagePath.'/'.$season->imageFileName)) {
                $this->deleteImage($season->imagePath, $season->imageFileName);
            }
            $season->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        WEATHER

    **********************************************************************************************/

    /**
     * Creates a weather.
     *
     * @param array $data
     *
     * @return bool|Weather
     */
    public function createWeather($data) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $weather = Weather::create(Arr::only($data, ['name', 'summary', 'description', 'parsed_description', 'is_visible', 'has_image']));

            if ($image) {
                $this->handleImage($image, $weather->imagePath, $weather->imageFileName);
            }

            return $this->commitReturn($weather);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a weather.
     *
     * @param Weather $weather
     * @param array   $data
     *
     * @return bool|Weather
     */
    public function updateWeather($weather, $data) {
        DB::beginTransaction();

        try {
            if (!$weather) {
                throw new \Exception('Invalid weather selected.');
            }
            $data = $this->populateData($data, $weather);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $weather->update(Arr::only($data, ['name', 'summary', 'description', 'parsed_description', 'is_visible', 'has_image']));

            if ($image) {
                $this->handleImage($image, $weather->imagePath, $weather->imageFileName);
            }

            return $this->commitReturn($weather);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a weather.
     *
     * @param Weather $weather
     *
     * @return bool
     */
    public function deleteWeather($weather) {
        DB::beginTransaction();

        try {
            if (!$weather) {
                throw new \Exception('Invalid weather selected.');
            }
            if (WeatherTable::where('weather_id', $weather->id)->exists()) {
                throw new \Exception('A season has this weather as an option. Please remove it from the season first.');
            }
            if (Settings::get('site_weather') == $weather->id) {
                throw new \Exception("The site's weather is currently set to this weather. Change the weather first.");
            }

            if ($weather->has_image && file_exists($weather->imagePath.'/'.$weather->imageFileName)) {
                $this->deleteImage($weather->imagePath, $weather->imageFileName);
            }
            $weather->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

        HELPERS

    **********************************************************************************************/

    /**
     * Replaces a season's weather table.
     *
     * @param WeatherSeason $season
     * @param array         $data
     */
    private function populateSeason($season, $data) {
        $season->loot()->delete();

        foreach ((array) ($data['weather_id'] ?? []) as $key => $weatherId) {
            if (!$weatherId || !Weather::where('id', $weatherId)->exists()) {
                throw new \Exception('One of the selected weathers is invalid.');
            }
            $weight = $data['weight'][$key] ?? null;
            if (!is_numeric($weight) || (int) $weight != $weight || (int) $weight < 1) {
                throw new \Exception('Each weather needs a whole-number weight of at least 1.');
            }

            WeatherTable::create([
                'weather_season_id' => $season->id,
                'weather_id'        => (int) $weatherId,
                'weight'            => (int) $weight,
            ]);
        }
    }

    /**
     * Processes user input for creating/updating a weather or season.
     *
     * @param array                      $data
     * @param Weather|WeatherSeason|null $model
     *
     * @return array
     */
    private function populateData($data, $model = null) {
        $data['parsed_description'] = isset($data['description']) && $data['description'] ? parse($data['description']) : null;
        $data['is_visible'] = isset($data['is_visible']) && $data['is_visible'] ? 1 : 0;

        if (isset($data['remove_image'])) {
            if ($model && $model->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                if (file_exists($model->imagePath.'/'.$model->imageFileName)) {
                    $this->deleteImage($model->imagePath, $model->imageFileName);
                }
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
