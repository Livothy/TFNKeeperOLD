<?php

namespace App\Services;

use App\Models\Marking\Marking;
use App\Models\Species\Species;
use Illuminate\Support\Facades\DB;

class MarkingService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Marking Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of marking categories and markings.
    |
    */

    /**********************************************************************************************

        MARKINGS

    **********************************************************************************************/

    /**
     * Creates a new marking.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Marking\Marking|bool
     */
    public function createMarking($data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['species_id']) && $data['species_id'] == 'none') {
                $data['species_id'] = null;
            }

            if ((isset($data['species_id']) && $data['species_id']) && !Species::where('id', $data['species_id'])->exists()) {
                throw new \Exception('The selected species is invalid.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $marking = Marking::create($data);

            if (!$this->logAdminAction($user, 'Created Marking', 'Created '.$marking->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $marking->imagePath, $marking->imageFileName);
            }

            return $this->commitReturn($marking);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a marking.
     *
     * @param \App\Models\Marking\Marking $marking
     * @param array                       $data
     * @param \App\Models\User\User       $user
     *
     * @return \App\Models\Marking\Marking|bool
     */
    public function updateMarking($marking, $data, $user) {
        DB::beginTransaction();

        try {
            if (isset($data['species_id']) && $data['species_id'] == 'none') {
                $data['species_id'] = null;
            }

            // More specific validation
            if (Marking::where('name', $data['name'])->where('id', '!=', $marking->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }
            if ((isset($data['species_id']) && $data['species_id']) && !Species::where('id', $data['species_id'])->exists()) {
                throw new \Exception('The selected species is invalid.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $marking->update($data);

            if (!$this->logAdminAction($user, 'Updated Marking', 'Updated '.$marking->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $marking->imagePath, $marking->imageFileName);
            }

            return $this->commitReturn($marking);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a marking.
     *
     * @param \App\Models\Marking\Marking $marking
     * @param mixed                       $user
     *
     * @return bool
     */
    public function deleteMarking($marking, $user) {
        DB::beginTransaction();

        try {
            // Check first if the marking is currently in use
            if (DB::table('characters')->where('markings', 'like', '%'.$marking->id.'%')->exists()) {
                throw new \Exception('A character with this marking exists. Please remove the marking first.');
            }

            if (!$this->logAdminAction($user, 'Deleted Marking', 'Deleted '.$marking->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            if (file_exists($marking->imageDirectory.'/'.$marking->imageFileName)) {
                $this->deleteImage($marking->imagePath, $marking->imageFileName);
            }
            $marking->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a marking.
     *
     * @param array                       $data
     * @param \App\Models\Marking\Marking $marking
     *
     * @return array
     */
    private function populateData($data, $marking = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }
        if (isset($data['species_id']) && $data['species_id'] == 'none') {
            $data['species_id'] = null;
        }
        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (isset($data['remove_image'])) {
            if ($marking && $marking->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($marking->imagePath, $marking->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
