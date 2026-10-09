@extends('admin.layout')

@section('admin-title')
    Carriers
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Carriers' => 'admin/data/carriers']) !!}

    <h1>Carriers</h1>

    <p>This is a list of carriers that can be attached to markings. </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/carrier/create') }}"><i class="fas fa-plus"></i> Create New Carrier</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($carriers))
        <p>No carriers found.</p>
    @else
        {!! $carriers->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-1">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-12 col-md-1">
                        <div class="logs-table-cell">ID</div>
                    </div>
                    <div class="col-12 col-md-9">
                        <div class="logs-table-cell">Attached Markings</div>
                    </div>
                    <div class="col-12 col-md-1">
                        <div class="logs-table-cell">Edit</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($carriers as $carrier)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell">
                                    {{ $carrier->name }}
                                </div>
                            </div>
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell">
                                    {{ $carrier->id }}
                                </div>
                            </div>
                            <div class="col-12 col-md-9">
                                <div class="logs-table-cell">
                                    {!! $carrier->getAttachedMarkingsAttribute() !!}
                                </div>
                            </div>
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell"><a href="{{ url('admin/data/carrier/edit/' . $carrier->id) }}" class="btn btn-primary py-0 px-1 w-100">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $carriers->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $carriers->total() }} result{{ $carriers->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
