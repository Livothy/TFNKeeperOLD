<?php

namespace App\Services;

use App\Models\Base\Base;
use Illuminate\Support\Facades\DB;

class BaseService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Base Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of base categories and bases.
    |
    */

    /**********************************************************************************************

        MARKINGS

    **********************************************************************************************/

    /**
     * Creates a new base.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Base\Base|bool
     */
    public function createBase($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $base = Base::create($data);

            if (!$this->logAdminAction($user, 'Created Base', 'Created '.$base->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $base->imagePath, $base->imageFileName);
            }

            return $this->commitReturn($base);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a base.
     *
     * @param \App\Models\Base\Base $base
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Base\Base|bool
     */
    public function updateBase($base, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Base::where('name', $data['name'])->where('id', '!=', $base->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $base->update($data);

            if (!$this->logAdminAction($user, 'Updated Base', 'Updated '.$base->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $base->imagePath, $base->imageFileName);
            }

            return $this->commitReturn($base);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a base.
     *
     * @param \App\Models\Base\Base $base
     * @param mixed                 $user
     *
     * @return bool
     */
    public function deleteBase($base, $user) {
        DB::beginTransaction();

        try {
            // Check first if the base is currently in use
            if (DB::table('character_bases')->where('base_id', $base->id)->exists()) {
                throw new \Exception('A character with this base exists. Please remove the base first.');
            }

            if (!$this->logAdminAction($user, 'Deleted Base', 'Deleted '.$base->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            if (file_exists($base->imageDirectory.'/'.$base->imageFileName)) {
                $this->deleteImage($base->imagePath, $base->imageFileName);
            }
            $base->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a base.
     *
     * @param array                 $data
     * @param \App\Models\Base\Base $base
     *
     * @return array
     */
    private function populateData($data, $base = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (isset($data['remove_image'])) {
            if ($base && $base->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($base->imagePath, $base->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
