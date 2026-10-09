@extends('admin.layout')

@section('admin-title')
    Bases
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Bases' => 'admin/data/bases']) !!}

    <h1>Bases</h1>

    <p>This is a list of bases that can be attached to characters. </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/base/create') }}"><i class="fas fa-plus"></i> Create New Base</a>
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

    @if (!count($bases))
        <p>No bases found.</p>
    @else
        {!! $bases->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">ID</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">Code</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">Edit</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($bases as $base)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell">
                                    @if (!$base->is_visible)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {{ $base->name }}
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell">
                                    {{ $base->id }}
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell">
                                    {{ $base->code }}
                                </div>
                            </div>
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell"><a href="{{ url('admin/data/base/edit/' . $base->id) }}" class="btn btn-primary py-0 px-1 w-100">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $bases->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $bases->total() }} result{{ $bases->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
