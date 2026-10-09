@extends('admin.layout')

@section('admin-title')
    {{ ucfirst(__('raids.raids')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', ucfirst(__('raids.raids')) => 'admin/data/' . __('raids.raids')]) !!}

    <h1>{{ ucfirst(__('raids.raids')) }}</h1>

    <p>
        This is a list of all the {{ __('raids.raids') }} on the site.
    </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/' . __('raids.raids') . '/create') }}">
            <i class="fas fa-plus"></i> Create New {{ ucfirst(__('raids.raid')) }}
        </a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">{!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}</div>
        {!! Form::close() !!}
    </div>

    @if (!count($raids))
        <p>No {{ __('raids.raids') }} found.</p>
    @else
        {!! $raids->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <div class="logs-table-cell pl-0">
                            {!! add_help('<b>Regardless of visibility</b>, whether or not the ' . __('raids.raid') . ' is active based on <b>start time</b> and <b>end time</b>.') !!}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col">
                        <div class="logs-table-cell">{{ ucfirst(__('raids.boss')) }}</div>
                    </div>
                    <div class="col-5 col-md-2">
                        <div class="logs-table-cell">Starts</div>
                    </div>
                    <div class="col-5 col-md-2">
                        <div class="logs-table-cell">Ends</div>
                    </div>
                    <!-- This is just here to make the table even. lol -->
                    <div class="col-auto">
                        <div class="logs-table-cell">
                            <span class="btn btn-primary py-0 px-2" style="opacity: 0; pointer-events: none;">
                                <i class="fas fa-cog" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($raids as $raid)
                    <div class="logs-table-row">
                        <div class="row no-gutters align-items-center flex-wrap">
                            <div class="col-auto">
                                <div class="logs-table-cell">
                                    @if ($raid->isActive)
                                        <i class="fas fa-check text-success" data-toggle="tooltip" title="This {{ __('raids.raid') }} is currently <b>active</b>."></i>
                                    @else
                                        <i class="fas fa-times text-danger" data-toggle="tooltip" title="This {{ __('raids.raid') }} is currently <b>inactive</b>."></i>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-3 text-truncate">
                                <div class="logs-table-cell">
                                    <span class="ubt-texthide">
                                        @if (!$raid->is_visible)
                                            <i class="fas fa-eye-slash" data-toggle="tooltip" title="This {{ __('raids.raid') }} is currently not visible."></i>
                                        @endif
                                        @if (($raid->status == 1 || $raid->status == 2) && (!$raid->isDefeated || ($raid->isDefeated && $raid->continue_raid)))
                                            <i class="fas fa-sync-alt" data-toggle="tooltip" title="This {{ __('raids.raid') }} is currently <b>ongoing</b>."></i>
                                        @elseif ($raid->status == 2 && !$raid->distributed_at)
                                            <i class="fas fa-exclamation-triangle text-danger fa-fade" data-toggle="tooltip"
                                                title="<b>This {{ __('raids.raid') }} has been defeated!</b> Rewards need to be distributed. Please proceed to the edit page."></i>
                                        @elseif ($raid->status == 3)
                                            <i class="fas fa-check-circle text-success" data-toggle="tooltip" title="This {{ __('raids.raid') }} has <b>concluded</b>."></i>
                                        @endif
                                        {{ $raid->name }}
                                    </span>
                                </div>
                            </div>
                            <div class="col">
                                <div class="logs-table-cell">
                                    <span class="ubt-texthide">
                                        {!! $raid->currentBoss() ? $raid->currentBoss()->displayName : '---' !!}
                                    </span>
                                </div>
                            </div>
                            <div class="col-5 col-md-2">
                                <div class="logs-table-cell">
                                    {!! $raid->start_at ? pretty_date($raid->start_at) : '---' !!}
                                </div>
                            </div>
                            <div class="col-5 col-md-2">
                                <div class="logs-table-cell">
                                    {!! $raid->end_at ? pretty_date($raid->end_at) : '---' !!}
                                </div>
                            </div>
                            <div class="col-2 col-md-auto text-right">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/' . __('raids.raids') . '/edit/' . $raid->id) }}" class="btn btn-primary py-0 px-2">
                                        <i class="fas fa-cog" data-toggle="tooltip" title="Edit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {!! $raids->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $raids->total() }} result{{ $raids->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
