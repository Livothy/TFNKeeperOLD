@extends('raids.layout')

@section('raids-title')
    {{ ucwords(__('raids.raid') . ' ' . __('raids.bosses')) }}
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('raids.raid') . ' ' . __('raids.bosses')) => __('raids.raids') . '/' . __('raids.bosses')]) !!}

    <h1>
        {{ ucwords(__('raids.raid') . ' ' . __('raids.bosses')) }}
    </h1>

    <div class="mb-2">
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group m-1">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group m-1">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'newest',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group m-1">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $bosses->render() !!}
    @if ($bosses->count())
        @foreach ($bosses as $boss)
            @include('raids._raid_boss_entry', ['raid' => $boss])
        @endforeach
    @else
        <div class="text-center text-muted">
            No {{ __('raids.raid') . ' ' . __('raids.bosses') }} encountered so far.
        </div>
    @endif
    {!! $bosses->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $bosses->total() }} result{{ $bosses->total() == 1 ? '' : 's' }} found.</div>
@endsection
