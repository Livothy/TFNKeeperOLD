@extends('admin.layout')

@section('admin-title') {{ ucfirst(__('volumes.volumes')) }} @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', ucfirst(__('volumes.volume')) => 'admin/data/volumes']) !!}

<h1>Volumes</h1>

<p>This is a list of {{ __('volumes.volumes') }} in the game that users can find and collect.</p> 
<p>
<div class="text-right form-group">
    <a class="btn btn-primary" href="{{ url('admin/data/volumes/bookshelves') }}"><i class="fas fa-folder"></i> {{ ucfirst(__('volumes.bookshelves')) }}</a>
    <a class="btn btn-primary" href="{{ url('admin/data/volumes/books') }}"><i class="fas fa-folder"></i> {{ ucfirst(__('volumes.books')) }}</a>
<a class="btn btn-primary" href="{{ url('admin/data/volumes/create') }}"><i class="fas fa-plus"></i> Create New {{ ucfirst(__('volumes.volume')) }}</a>
</div>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('book_id', $books, Request::get('book_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('is_visible', $is_visible, Request::get('is_visible'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($volumes))
    <p>No {{ __('volumes.volumes') }} found.</p>
@else 
    {!! $volumes->render() !!}
    <table class="table table-sm category-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>{{ ucfirst(__('volumes.book')) }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($volumes as $volume)
                <tr class="sort-item" data-id="{{ $volume->id }}">
                    <td>
                        {!! $volume->displayName !!} @include('world.volumes._volume_icons', ['volume' => $volume])
                    </td>
                    <td> {!! $volume->book ? $volume->book->displayName : '' !!} </td>
                    <td class="text-right">
                        <a href="{{ url('admin/data/volumes/edit/'.$volume->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {!! $volumes->render() !!}
@endif

@endsection

