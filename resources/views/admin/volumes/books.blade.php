@extends('admin.layout')

@section('admin-title') {{ ucfirst(__('volumes.books')) }} @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', ucfirst(__('volumes.books')) => 'admin/data/volumes/books']) !!}


<div class="text-right mb-3">
<a class="btn btn-primary" href="{{ url('admin/data/volumes') }}"> {{ ucfirst(__('volumes.library')) }} Home</a>
</div>
<h1>Books</h1>

<p>This is a list of {{ __('volumes.books') }} that will be used to categorize {{ __('volumes.volumes') }}. Think of {{ __('volumes.books') }} like categories-- {{ __('volumes.volumes') }} can be assigned to a {{ __('volumes.book') }}.</p> 

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/data/volumes/books/create') }}"><i class="fas fa-plus"></i> Create New {{ ucfirst(__('volumes.book')) }}</a></div>
@if(!count($books))
    <p>No {{ __('volumes.books') }} found.</p>
@else 
    <table class="table table-sm book-table">
        <tbody id="sortable" class="sortable">
            @foreach($books as $book)
                <tr class="sort-books" books-id="{{ $book->id }}">
                    <td>
                        {!! $book->displayName !!}
                    </td>
                    <td>
                        {!! $book->bookshelf ? $book->bookshelf->displayName : '' !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/volumes/books/edit/'.$book->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
@endif

@endsection

