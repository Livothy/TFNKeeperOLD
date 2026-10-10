@extends('layouts.app')

@section('title')
    Home
@endsection

@section('content')
    <div class="row shops-row">
        @foreach ($fetches as $fetch)
            @include('fetchquests._fetch_entry', ['fetch' => $fetch])
        @endforeach
    </div>
@endsection
