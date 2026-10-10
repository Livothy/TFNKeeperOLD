<?php namespace App\Services;

use App\Models\User\User;
use App\Models\Volume\Book;
use App\Models\Volume\BookAuthor;
use App\Models\Volume\Bookshelf;
use App\Models\Volume\BookTag;
use App\Models\Volume\Volume;
use App\Services\Service;
use DB;

class VolumeService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Volume Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of volumes.
    |
     */

    /**********************************************************************************************

    Volumes
     **********************************************************************************************/

    /**
     * Creates a new volume.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Volume\Volume
     */
    public function createVolume($data, $user)
    {
        DB::beginTransaction();

        try {

            if (isset($data['book_id']) && $data['book_id'] == 'none') {
                $data['book_id'] = null;
            }

            if ((isset($data['book_id']) && $data['book_id']) && !Book::where('id', $data['book_id'])->exists()) {
                throw new \Exception("The selected " . __('volumes.book') . " is invalid.");
            }

            $data = $this->populateData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $volume = Volume::create($data);
            $volume->save();

            if ($image) {
                $this->handleImage($image, $volume->imagePath, $volume->imageFileName);
            }

            return $this->commitReturn($volume);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates an volume.
     *
     * @param  \App\Models\Volume\Volume  $volume
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Volume\Volume
     */
    public function updateVolume($volume, $data, $user)
    {
        DB::beginTransaction();

        try {

            if (isset($data['book_id']) && $data['book_id'] == 'none') {
                $data['book_id'] = null;
            }

            // More specific validation
            if (Volume::where('name', $data['name'])->where('id', '!=', $volume->id)->exists()) {
                throw new \Exception("The name has already been taken.");
            }

            if ((isset($data['book_id']) && $data['book_id']) && !Book::where('id', $data['book_id'])->exists()) {
                throw new \Exception("The selected " . __('volumes.book') . " is invalid.");
            }

            $data = $this->populateData($data, $volume);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $volume->update($data);
            $volume->save();

            if ($volume) {
                $this->handleImage($image, $volume->imagePath, $volume->imageFileName);
            }

            return $this->commitReturn($volume);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating an volume.
     *
     * @param  array                  $data
     * @param  \App\Models\Volume\Volume  $volume
     * @return array
     */
    private function populateData($data, $volume = null)
    {
        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (isset($data['remove_image'])) {
            if ($volume && $volume->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($volume->imagePath, $volume->imageFileName);
            }
            unset($data['remove_image']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }

        if (!isset($data['is_global'])) {
            $data['is_global'] = 0;
        }

        return $data;
    }

    /**
     * Deletes an volume.
     *
     * @param  \App\Models\Volume\Volume  $volume
     * @return bool
     */
    public function deleteVolume($volume)
    {
        DB::beginTransaction();

        try {
            // Check first if the volume is currently owned or if some other site feature uses it
            if (DB::table('user_volumes')->where('volume_id', $volume->id)->exists()) {
                throw new \Exception("At least one user currently owns this " . __('volumes.volume') . ". Please remove the " . __('volumes.volumes') . " before deleting it.");
            }

            DB::table('user_volumes_log')->where('volume_id', $volume->id)->delete();
            DB::table('user_volumes')->where('volume_id', $volume->id)->delete();
            if ($volume->has_image) {
                $this->deleteImage($volume->imagePath, $volume->imageFileName);
            }

            $volume->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************
    Books
     **********************************************************************************************/

    /**
     * Create a book.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return \App\Models\Volume\Book|bool
     */
    public function createBook($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateBookData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $next = null;
            if (isset($data['next_image']) && $data['next_image']) {
                $data['has_next'] = 1;
                $next = $data['next_image'];
                unset($data['next_image']);
            } else {
                $data['has_next'] = 0;
            }

            $book = Book::create($data);

            $this->updateTags($data, $book);

            if ($image) {
                $this->handleImage($image, $book->imagePath, $book->imageFileName);
            }

            if ($next) {
                $this->handleImage($next, $book->imagePath, $book->nextImageFileName);
            }

            return $this->commitReturn($book);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a book.
     *
     * @param  \App\Models\Volume\Book  $book
     * @param  array                          $data
     * @param  \App\Models\User\User          $user
     * @return \App\Models\Volume\Book|bool
     */
    public function updateBook($book, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if (Book::where('name', $data['name'])->where('id', '!=', $book->id)->exists()) {
                throw new \Exception("The name has already been taken.");
            }

            $data = $this->populateBookData($data, $book);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $next = null;
            if (isset($data['next_image']) && $data['next_image']) {
                $data['has_next'] = 1;
                $next = $data['next_image'];
                unset($data['next_image']);
            }

            $book->update($data);

            $this->updateTags($data, $book);

            if ($book) {
                $this->handleImage($image, $book->imagePath, $book->imageFileName);
            }

            if ($book) {
                $this->handleImage($next, $book->imagePath, $book->nextImageFileName);
            }

            return $this->commitReturn($book);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handle book data.
     *
     * @param  array                               $data
     * @param  \App\Models\Volume\Book|null  $book
     * @return array
     */
    private function populateBookData($data, $book = null)
    {

        if (isset($data['bookshelf_id']) && $data['bookshelf_id'] == 'none') {
            $data['bookshelf_id'] = null;
        }

        if ((isset($data['bookshelf_id']) && $data['bookshelf_id']) && !Bookshelf::where('id', $data['bookshelf_id'])->exists()) {
            throw new \Exception("The selected " . __('volumes.bookshelf') . " is invalid.");
        }

        if (isset($data['description']) && $data['description']) {
            $data['parsed_description'] = parse($data['description']);
        }

        if (isset($data['remove_image'])) {
            if ($book && $book->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($book->imagePath, $book->imageFileName);
            }
            unset($data['remove_image']);
        }

        if (isset($data['remove_next'])) {
            if ($book && $book->has_next && $data['remove_next']) {
                $data['has_next'] = 0;
                $this->deleteImage($book->imagePath, $book->nextImageFileName);
            }
            unset($data['remove_next']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }

        if (!isset($data['is_public'])) {
            $data['is_public'] = 0;
        }

        if (!isset($data['numeric_prefix'])) {
            $data['numeric_prefix'] = 0;
        }

        return $data;
    }

    /**
     * Delete a book.
     *
     * @param  \App\Models\Volume\Book  $book
     * @return bool
     */
    public function deleteBook($book)
    {
        DB::beginTransaction();

        try {
            // Check first if the book is currently in use
            if (Volume::where('book_id', $book->id)->exists()) {
                throw new \Exception("A " . __('volumes.volume') . " with this " . __('volumes.book') . " exists. Please change its " . __('volumes.book') . " first.");
            }

            if ($book->has_image) {
                $this->deleteImage($book->imagePath, $book->imageFileName);
            }

            if ($book->has_next) {
                $this->deleteImage($book->imagePath, $book->nextImageFileName);
            }

            $book->authors()->delete();
            $book->tags()->delete();
            $book->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts book's volumes
     *
     * @param  array  $data
     * @return bool
     */
    public function sortVolumes($data, $book)
    {
        DB::beginTransaction();

        try {
            // explode the sort array
            $sort = explode(',', $data);

            foreach ($sort as $key => $s) {
                Volume::where('book_id', $book->id)->where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating book authors.
     *
     */
    public function editAuthors($data, $book)
    {
        DB::beginTransaction();

        try {
            // Clear the old authors...
            $book->authors()->delete();

            if (isset($data['author_type'])) {
                foreach ($data['author_type'] as $key => $type) {
                    BookAuthor::create([
                        'book_id' => $book->id,
                        'author_type' => $type,
                        'author' => $data['author'][$key] ?? null,
                        'credit_type' => $data['credit_type'][$key] ?? null,
                    ]);
                }
            }
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating book tags.
     *
     */
    public function updateTags($data, $book)
    {
        DB::beginTransaction();

        try {
            // Clear the old tags...
            $book->tags()->delete();

            if (isset($data['tags'])) {
                $data['tags'] = explode(',', $data['tags']);
                foreach ($data['tags'] as $tag) {
                    BookTag::create([
                        'book_id' => $book->id,
                        'tag' => $tag,
                    ]);
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**********************************************************************************************

    Bookshelves
     **********************************************************************************************/

    /**
     * Creates a new Bookshelf.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Volume\Bookshelf
     */
    public function createBookshelf($data, $user)
    {
        DB::beginTransaction();

        try {

            $data = $this->populateBookshelfData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            } else {
                $data['has_image'] = 0;
            }

            $bookshelf = Bookshelf::create($data);
            $bookshelf->save();

            if ($image) {
                $this->handleImage($image, $bookshelf->imagePath, $bookshelf->imageFileName);
            }

            return $this->commitReturn($bookshelf);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a bookshelf.
     *
     * @param  \App\Models\Volume\Bookshelf  $bookshelf
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Volume\Bookshelf
     */
    public function updateBookshelf($bookshelf, $data, $user)
    {
        DB::beginTransaction();

        try {

            if (isset($data['book_id']) && $data['book_id'] == 'none') {
                $data['book_id'] = null;
            }

            // More specific validation
            if (Bookshelf::where('name', $data['name'])->where('id', '!=', $bookshelf->id)->exists()) {
                throw new \Exception("The name has already been taken.");
            }

            $data = $this->populateBookshelfData($data);

            $image = null;
            if (isset($data['image']) && $data['image']) {
                $data['has_image'] = 1;
                $image = $data['image'];
                unset($data['image']);
            }

            $bookshelf->update($data);
            $bookshelf->save();

            if ($bookshelf) {
                $this->handleImage($image, $bookshelf->imagePath, $bookshelf->imageFileName);
            }

            return $this->commitReturn($bookshelf);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a bookshelf.
     *
     * @param  array                  $data
     * @param  \App\Models\Volume\Bookshelf  $bookshelf
     * @return array
     */
    private function populateBookshelfData($data, $bookshelf = null)
    {
        if (isset($data['remove_image'])) {
            if ($bookshelf && $bookshelf->has_image && $data['remove_image']) {
                $data['has_image'] = 0;
                $this->deleteImage($bookshelf->imagePath, $bookshelf->imageFileName);
            }
            unset($data['remove_image']);
        }

        if (!isset($data['is_visible'])) {
            $data['is_visible'] = 0;
        }

        if (!isset($data['is_global'])) {
            $data['is_global'] = 0;
        }

        return $data;
    }

    /**
     * Deletes an bookshelf.
     *
     * @param  \App\Models\Volume\Bookshelf  $bookshelf
     * @return bool
     */
    public function deleteBookshelf($bookshelf)
    {
        DB::beginTransaction();

        try {

            if ($bookshelf->has_image) {
                $this->deleteImage($bookshelf->imagePath, $bookshelf->imageFileName);
            }

            Book::where('bookshelf_id', $bookshelf->id)->update(['bookshelf_id' => null]);
            $bookshelf->delete();

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts bookshelves
     *
     * @param  array  $data
     * @return bool
     */
    public function sortBookshelves($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Bookshelf::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts bookshelf's books
     *
     * @param  array  $data
     * @return bool
     */
    public function sortBooks($data, $bookshelf)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach ($sort as $key => $s) {
                Book::where('bookshelf_id', $bookshelf->id)->where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
