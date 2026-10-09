<?php

namespace App\Services;

use App\Models\Carrier\Carrier;
use App\Models\Carrier\MarkingCarrier;
use Illuminate\Support\Facades\DB;

class CarrierService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Carrier Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of carriers.
    |
    */

    /**********************************************************************************************

        CARRIERS

    **********************************************************************************************/

    /**
     * Creates a new carrier.
     *
     * @param array                 $data
     * @param \App\Models\User\User $user
     *
     * @return \App\Models\Carrier\Carrier|bool
     */
    public function createCarrier($data, $user) {
        DB::beginTransaction();

        try {
            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $carrier = Carrier::create($data);

            if (isset($data['attached_markings']) && $data['attached_markings']) {
                foreach ($data['attached_markings'] as $marking_id => $name) {
                    $relation = MarkingCarrier::create([
                        'marking_id' => $marking_id,
                        'carrier_id' => $carrier->id,
                    ]);
                }
            }

            if (!$this->logAdminAction($user, 'Created Carrier', 'Created '.$carrier->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $carrier->imagePath, $carrier->imageFileName);
            }

            return $this->commitReturn($carrier);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Updates a carrier.
     *
     * @param \App\Models\Carrier\Carrier $carrier
     * @param array                       $data
     * @param \App\Models\User\User       $user
     *
     * @return \App\Models\Carrier\Carrier|bool
     */
    public function updateCarrier($carrier, $data, $user) {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Carrier::where('name', $data['name'])->where('id', '!=', $carrier->id)->exists()) {
                throw new \Exception('The name has already been taken.');
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            MarkingCarrier::where('carrier_id', $carrier->id)->delete();

            if (isset($data['attached_markings']) && $data['attached_markings']) {
                if (!is_array($data['attached_markings'])) {
                    $data['attached_markings'] = [$data['attached_markings']];
                }
                foreach ($data['attached_markings'] as $i => $marking_id) {
                    $relation = MarkingCarrier::create([
                        'marking_id' => $marking_id,
                        'carrier_id' => $carrier->id,
                    ]);
                }
            }

            $carrier->update($data);

            if (!$this->logAdminAction($user, 'Updated Carrier', 'Updated '.$carrier->displayName)) {
                throw new \Exception('Failed to log admin action.');
            }

            if ($image) {
                $this->handleImage($image, $carrier->imagePath, $carrier->imageFileName);
            }

            return $this->commitReturn($carrier);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a carrier.
     *
     * @param \App\Models\Carrier\Carrier $carrier
     * @param mixed                       $user
     *
     * @return bool
     */
    public function deleteCarrier($carrier, $user) {
        DB::beginTransaction();

        try {
            // Check first if the carrier is currently in use
            if (DB::table('carriers')->where('id', $carrier->id)->exists()) {
                throw new \Exception('A character with this carrier exists. Please remove the carrier first.');
            }

            if (!$this->logAdminAction($user, 'Deleted Carrier', 'Deleted '.$carrier->name)) {
                throw new \Exception('Failed to log admin action.');
            }

            if (file_exists($carrier->imageDirectory.'/'.$carrier->imageFileName)) {
                $this->deleteImage($carrier->imagePath, $carrier->imageFileName);
            }
            $carrier->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a carrier.
     *
     * @param array                       $data
     * @param \App\Models\Carrier\Carrier $carrier
     *
     * @return array
     */
    private function populateData($data, $carrier = null) {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }
        if (isset($data['remove_image'])) {
            if ($carrier && $carrier->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($carrier->imagePath, $carrier->imageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }
}
