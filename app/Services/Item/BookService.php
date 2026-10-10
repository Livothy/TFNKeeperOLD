<?php namespace App\Services\Item;

use App\Models\Item\Item;
use App\Models\User\User;
use App\Models\User\UserItem;
use App\Models\User\UserVolume;
use App\Models\User\UserVolumeLog;
use App\Models\Volume\Book;
use App\Services\InventoryManager;
use App\Services\Service;
use Carbon\Carbon;
use DB;

class BookService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Book Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of book type items.
    |
     */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData()
    {
        return [
            'books' => Book::orderBy('name')->pluck('name', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for edits.
     *
     * @param string $tag
     *
     * @return mixed
     */
    public function getTagData($tag)
    {
        $bookData['book_id'] = $tag->data['book_id'] ?? null;

        return $bookData;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format for DB storage.
     *
     * @param string $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data)
    {
        $bookData['book_id'] = $data['book_id'] ?? null;

        DB::beginTransaction();

        try {
            $tag->update(['data' => json_encode($bookData)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stacks
     * @param \App\Models\User\User     $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stacks, $user, $data)
    {
        DB::beginTransaction();

        try {

            foreach ($stacks as $key => $stack) {
                // We don't want to let anyone who isn't the owner of the slot to use it,
                // so do some validation...
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                // Next, try to delete the tag item. If successful, we can grant the volume.
                if ((new InventoryManager)->debitStack($stack->user, 'Book Used', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        $book = Book::visible()->find($stack->item->tag($data['tag'])->getData()['book_id']);

                        if(!isset($book)){
                            throw new \Exception('Invalid '.__('volumes.book').'.');
                        }

                        if (!$book->volumes()->visible()->count()) {
                            throw new \Exception('This ' . __('volumes.book') . ' doesn\'t contain any ' . __('volumes.volumes') . '. Contact an admin.');
                        }

                        foreach ($book->volumes as $volume) {

                            if ($volume->is_visible && !UserVolume::where('user_id', $user->id)->where('volume_id', $volume->id)->exists()) {
                                //credit the volume
                                //doing it here because we can just use the item tags instead of putting it in the asset helper or w/e
                                if (!UserVolume::create(['user_id' => $user->id, 'volume_id' => $volume->id])) {
                                    throw new \Exception('Error crediting ' . __('volumes.volume') . '.');
                                }

                                if (!UserVolumeLog::create(
                                    [
                                        'sender_id' => null,
                                        'recipient_id' => $user->id,
                                        'character_id' => null,
                                        'volume_id' => $volume->id,
                                        'log' => 'Unlocked Volume (Unlocked from using a ' . $stack->item->name . ')',
                                        'log_type' => 'Unlocked Volume',
                                        'data' => 'Unlocked from using a ' . $stack->item->name,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ]
                                )) {
                                    throw new \Exception('Error making log');
                                }
                            }

                        }

                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }
}
