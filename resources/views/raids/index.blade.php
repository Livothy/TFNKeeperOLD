@extends('raids.layout')

@section('raids-title')
    Index
@endsection

@section('content')
    {!! breadcrumbs([ucfirst(__('raids.raids')) . ' Index' => __('raids.raids')]) !!}

    <h1>{{ ucfirst(__('raids.raids')) }} Index</h1>
    <p>
        Here you can view all the past and present {{ __('raids.raids') }} as well as their {{ __('raids.bosses') }} and additional data such as rewards and attack method.
    </p>
    <hr>
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

    {!! $raids->render() !!}
    @if ($raids->count())
        @foreach ($raids as $raid)
            @include('raids._raid_entry', ['raid' => $raid])
        @endforeach
    @else
        <div class="text-center text-muted">
            No {{ __('raids.raids') }} so far.
        </div>
    @endif
    {!! $raids->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $raids->total() }} result{{ $raids->total() == 1 ? '' : 's' }} found.</div>
@endsection
