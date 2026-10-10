@extends('world.layout')

@section('title')
    {{ $book->name }}
@endsection

@section('meta-img')
    {{ $book->imageUrl ? $book->imageUrl : null }}
@endsection

@section('content')
    {!! breadcrumbs([
        'World' => 'world',
        __('volumes.library') => 'world/' . __('volumes.library'),
        $book->name => $book->idUrl,
    ]) !!}

    @include('world.volumes._book_page_entry', [
        'book' => $book,
    ])

    @if ($book->volumes->count())
        <h2 class="text-center">{{ __('volumes.volumes') }}</h2>
        <div class="row">
            @foreach ($book->volumes as $volume)
                @include('world.volumes._volume_entry', ['volume' => $volume])
            @endforeach
        </div>
    @endif

@endsection
