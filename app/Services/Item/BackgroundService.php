<?php namespace App\Services\Item;

use App\Services\Service;

use DB;
use Carbon\Carbon;

use App\Services\InventoryManager;

use App\Models\Character\Character;
use App\Models\Item\Item;
use App\Models\Item\ItemTag;

class BackgroundService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Background Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of background type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData()
    {
        return [ ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @return mixed
     */
    public function getTagData($tag)
    {
        $data = [];
        if($tag->data) {
            $data = $tag->data;
        }
        return $data;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param  string  $tag
     * @param  array   $data
     * @return bool
     */
    public function updateData($tag, $data)
    {
        DB::beginTransaction();

        try {
            $newData['padding-top'] = isset($data['padding-top']) ? (is_numeric($data['padding-top']) ? $data['padding-top'].'em' : $data['padding-top']) : '1em';
            $newData['padding-left'] = isset($data['padding-left']) ? (is_numeric($data['padding-left']) ? $data['padding-left'].'em' : $data['padding-left']) : '1em';
            $newData['padding-right'] = isset($data['padding-right']) ? (is_numeric($data['padding-right']) ? $data['padding-right'].'em' : $data['padding-right']) : '1em';
            $newData['padding-bottom'] = isset($data['padding-bottom']) ? (is_numeric($data['padding-bottom']) ? $data['padding-bottom'].'em' : $data['padding-bottom']) : '1em';

            // You can remove this if you want your admins to be able to slip in sneaky css, but I don't recommend it.
            // Also yes I know this throws a 'sdlfk' error as well as the Exception below. Ask Cy, I have no idea. I like it though.
            if(strpos($newData['padding-top'],';') || strpos($newData['padding-left'],';') || strpos($newData['padding-right'],';') || strpos($newData['padding-bottom'],';')) throw new \Exception("You should not be including semicolons in this!");

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);

                $saveName = $tag->id.'-image.'. $image->getClientOriginalExtension();
                $fileName = $tag->id.'-image.'. $image->getClientOriginalExtension().'?v='. Carbon::now()->format('mdY_').randomString(6);

                $newData['background-image'] = 'images/data/items/backgrounds/'.$fileName;
            }

            if($image) $this->handleImage($image, public_path('images/data/items/backgrounds/'), $saveName);
            else $newData['background-image'] = ( $tag->getData() ? $tag->getData()['background-image'] : null);

            $tag->update(['data' => json_encode($newData)]);

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Acts upon the item when used from the inventory.
     *
     * @param  \App\Models\User\UserItem  $stacks
     * @param  \App\Models\User\User      $user
     * @param  array                      $data
     * @return bool
     */
    public function act($stacks, $user, $data)
    {
        //
    }


    public function checkBackground($character)
    {
        // Checks if the character has an item with the background tag and if so, snags the first one. There should only be one!
        $items = ItemTag::where('tag','background')->pluck('item_id');
        $item = $character->items()->where('count','>','0')->whereIn('item_id',$items)->first();

        if(isset($item) && $item->count()) {

            // Checks whether the item with the background tag actually has a background image in the data.
            if(isset($item->tag('background')->data) && $item->tag('background')->is_active && isset($item->tag('background')->data['background-image'])) $tag = $item->tag('background')->getData();
            else return null;

            $mini = [];
            foreach($tag as $key => $info)
            {
                $mini[] = $key . ': ' . ($key == "background-image" ? "url('".url($info)."')" : $info);
            }
            return $mini;
        }
        else return null;
    }

}
