@extends('admin.layout')

@section('admin-title')
    Markings
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Markings' => 'admin/data/markings']) !!}

    <h1>Markings</h1>

    <p>This is a list of markings that can be attached to characters. </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/markings/create') }}"><i class="fas fa-plus"></i> Create New Marking</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('species_id', $specieses, Request::get('species_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('rarity_id', $rarities, Request::get('rarity_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($markings))
        <p>No markings found.</p>
    @else
        {!! $markings->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Rarity</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Species</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($markings as $marking)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell">
                                    @if (!$marking->is_visible)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {{ $marking->name }}
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="logs-table-cell">{!! $marking->rarity->displayName !!}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="logs-table-cell">{{ $marking->species ? $marking->species->name : '---' }}</div>
                            </div>
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell"><a href="{{ url('admin/data/markings/edit/' . $marking->id) }}" class="btn btn-primary py-0 px-1 w-100">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $markings->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $markings->total() }} result{{ $markings->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
