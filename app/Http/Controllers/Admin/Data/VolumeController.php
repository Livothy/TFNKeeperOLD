<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\User\User;
use App\Models\Volume\Book;
use App\Models\Volume\Bookshelf;
use App\Models\Volume\Volume;
use App\Services\VolumeService;
use Auth;
use Illuminate\Http\Request;

class VolumeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Volume Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of volumes.
    |
     */

    /**********************************************************************************************

    VolumeS
     **********************************************************************************************/

    /**
     * Shows the volume index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getVolumeIndex(Request $request)
    {
        $query = Volume::query();
        $data = $request->only(['name', 'book_id', 'is_visible']);
        if (isset($data['book_id']) && $data['book_id'] != 'none') {
            $query->where('book_id', $data['book_id']);
        }

        if (isset($data['is_visible']) && $data['is_visible'] != 'none') {
            $query->where('is_visible', $data['is_visible']);
        }

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%' . $data['name'] . '%');
        }

        return view('admin.volumes.volumes', [
            'volumes' => $query->paginate(20)->appends($request->query()),
            'books' => ['none' => 'Any ' . ucfirst(__('volumes.book'))] + Book::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
            'is_visible' => ['none' => 'Any Status', '0' => 'Unreleased', '1' => 'Released'],
        ]);
    }

    /**
     * Shows the create volume page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateVolume()
    {
        return view('admin.volumes.create_edit_volume', [
            'volume' => new Volume,
            'books' => ['none' => 'No book'] + Book::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit volume page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditVolume($id)
    {
        $volume = Volume::find($id);
        if (!$volume) {
            abort(404);
        }

        return view('admin.volumes.create_edit_volume', [
            'volume' => $volume,
            'books' => ['none' => 'No ' . ucfirst(__('volumes.book'))] + Book::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits an volume.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\VolumeService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditVolume(Request $request, VolumeService $service, $id = null)
    {
        $id ? $request->validate(Volume::$updateRules) : $request->validate(Volume::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_visible', 'book_id'
            , 'summary', 'is_global',
        ]);
        if ($id && $service->updateVolume(Volume::find($id), $data, Auth::user())) {
            flash(ucfirst(__('volumes.volume')) . ' updated successfully.')->success();
        } else if (!$id && $volume = $service->createVolume($data, Auth::user())) {
            flash(ucfirst(__('volumes.volume')) . ' created successfully.')->success();
            return redirect()->to('admin/data/volumes/edit/' . $volume->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Gets the volume deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteVolume($id)
    {
        $volume = Volume::find($id);
        return view('admin.volumes._delete_volume', [
            'volume' => $volume,
        ]);
    }

    /**
     * Creates or edits an volume.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\VolumeService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteVolume(Request $request, VolumeService $service, $id)
    {
        if ($id && $service->deleteVolume(Volume::find($id))) {
            flash(ucfirst(__('volumes.volume')) . ' deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->to('admin/data/volumes');
    }

    /**********************************************************************************************
    Books
     **********************************************************************************************/

    /**
     * Shows the book index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBookIndex(Request $request)
    {
        $query = Book::query();
        $data = $request->only(['name', 'bookshelf_id', 'is_visible']);
        if (isset($data['bookshelf_id']) && $data['bookshelf_id'] != 'none') {
            $query->where('bookshelf_id', $data['bookshelf_id']);
        }

        if (isset($data['is_visible']) && $data['is_visible'] != 'none') {
            $query->where('is_visible', $data['is_visible']);
        }

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%' . $data['name'] . '%');
        }

        return view('admin.volumes.books', [
            'books' => $query->paginate(20)->appends($request->query()),
            'bookshelves' => ['none' => 'Any ' . ucfirst(__('volumes.bookshelf'))] + Bookshelf::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'is_visible' => ['none' => 'Any Status', '0' => 'Unreleased', '1' => 'Released'],
        ]);
    }

    /**
     * Shows the create book page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBook()
    {
        return view('admin.volumes.create_edit_book', [
            'book' => new Book,
            'bookshelves' => ['none' => 'No ' . ucfirst(__('volumes.bookshelf'))] + Bookshelf::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit book page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBook($id)
    {
        $book = Book::find($id);
        if (!$book) {
            abort(404);
        }

        return view('admin.volumes.create_edit_book', [
            'book' => $book,
            'bookshelves' => ['none' => 'No ' . ucfirst(__('volumes.bookshelf'))] + Bookshelf::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'users' => User::query()->orderBy('name')->pluck('name', 'id')->toArray(),
            'characters' => Character::myo(0)->orderBy('sort', 'DESC')->get()->pluck('fullName', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a book.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\VolumeService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBook(Request $request, VolumeService $service, $id = null)
    {
        $id ? $request->validate(Book::$updateRules) : $request->validate(Book::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'summary', 'is_visible', 'bookshelf_id', 'next_image', 'remove_next', 'is_public', 'tags','numeric_prefix','text_prefix'
        ]);
        if ($id && $service->updateBook(Book::find($id), $data, Auth::user())) {
            flash(ucfirst(__('volumes.book')) . ' updated successfully.')->success();
        } else if (!$id && $book = $service->createBook($data, Auth::user())) {
            flash(ucfirst(__('volumes.book')) . ' created successfully.')->success();
            return redirect()->to('admin/data/volumes/books/edit/' . $book->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Gets the book deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBook($id)
    {
        $book = Book::find($id);
        return view('admin.volumes._delete_book', [
            'book' => $book,
        ]);
    }

    /**
     * Deletes a book.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\VolumeService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBook(Request $request, VolumeService $service, $id)
    {
        if ($id && $service->deleteBook(Book::find($id))) {
            flash(ucfirst(__('volumes.book')) . ' deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->to('admin/data/volumes/books');
    }

    /**
     * Sort a book's volumes
     *
     */
    public function postSortVolumes(Request $request, VolumeService $service, $id)
    {
        if ($service->sortVolumes($request->get('sort'), Book::find($id))) {
            flash('Volume order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Edit book authors
     *
     */
    public function postEditAuthors(Request $request, VolumeService $service, $id)
    {
        $data = $request->only([
            'author', 'author_type', 'credit_type',
        ]);
        if ($service->editAuthors($data, Book::find($id))) {
            flash('Book authors updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**********************************************************************************************
    Bookshelves
     **********************************************************************************************/

    /**
     * Shows the bookshelf index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBookshelfIndex(Request $request)
    {
        $query = Bookshelf::orderBy('sort', 'DESC');
        $data = $request->only(['name']);

        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%' . $data['name'] . '%');
        }

        return view('admin.volumes.bookshelves', [
            'bookshelves' => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create bookshelf page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBookshelf()
    {
        return view('admin.volumes.create_edit_bookshelf', [
            'bookshelf' => new Bookshelf,
            'books' => ['none' => 'No book'] + Book::orderBy('name', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the edit bookshelf page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBookshelf($id)
    {
        $bookshelf = Bookshelf::find($id);
        if (!$bookshelf) {
            abort(404);
        }

        return view('admin.volumes.create_edit_bookshelf', [
            'bookshelf' => $bookshelf,
        ]);
    }

    /**
     * Creates or edits a bookshelf.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBookshelf(Request $request, VolumeService $service, $id = null)
    {
        $id ? $request->validate(Bookshelf::$updateRules) : $request->validate(Bookshelf::$createRules);
        $data = $request->only([
            'name', 'summary', 'image', 'remove_image',
        ]);
        if ($id && $service->updateBookshelf(Bookshelf::find($id), $data, Auth::user())) {
            flash(ucfirst(__('volumes.bookshelf')) . ' updated successfully.')->success();
        } else if (!$id && $bookshelf = $service->createBookshelf($data, Auth::user())) {
            flash(ucfirst(__('volumes.bookshelf')) . ' created successfully.')->success();
            return redirect()->back();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Gets the bookshelf deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBookshelf($id)
    {
        $bookshelf = Bookshelf::find($id);
        return view('admin.volumes._delete_bookshelf', [
            'bookshelf' => $bookshelf,
        ]);
    }

    /**
     * Sort a bookshelf's books
     *
     */
    public function postSortBooks(Request $request, VolumeService $service, $id)
    {
        if ($service->sortBooks($request->get('sort'), Bookshelf::find($id))) {
            flash(ucfirst(__('volumes.book')) . ' order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

    /**
     * Sort a bookshelf
     *
     */
    public function postSortBookshelves(Request $request, VolumeService $service)
    {
        if ($service->sortBookshelves($request->get('sort'))) {
            flash(ucfirst(__('volumes.bookshelf')) . ' order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }

        }
        return redirect()->back();
    }

}
