@extends('world.layout')

@section('title')
    {{ $volume->name }}
@endsection

@section('meta-img')
    {{ $volume->imageUrl ? $volume->imageUrl : null }}
@endsection

@section('content')
    {!! breadcrumbs([
        'World' => 'world',
        __('volumes.library') => 'world/' . __('volumes.library'),
        $volume->volumeName(Auth::user() ?? null, $isAdmin ?? false) => $volume->idUrl,
    ]) !!}

    @include('world.volumes._volume_page_entry', ['volume' => $volume])
@endsection
