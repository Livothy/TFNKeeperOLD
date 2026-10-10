@extends('world.layout')

@section('title')
    {{ ucfirst(__('volumes.volumes')) }}
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', ucfirst(__('volumes.library')) => 'world/' . __('volumes.volumes')]) !!}
    <h1>{{ ucfirst(__('volumes.volumes')) }}</h1>
    <p>Here all the available {{ __('volumes.volumes') }} on the site.</p>
    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('book_id', $books, Request::get('book_id'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'category',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $volumes->render() !!}
    <div class="row">
        @foreach ($volumes as $volume)
            @include('world.volumes._volume_entry', ['volume' => $volume])
        @endforeach
    </div>
    {!! $volumes->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $volumes->total() }} result{{ $volumes->total() == 1 ? '' : 's' }}
        found.</div>
@endsection
