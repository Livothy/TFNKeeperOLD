@extends('queues.layout')

@section('queues-title')
    All Queues
@endsection

@section('content')
    {!! breadcrumbs(['Queues' => 'queues', 'All Queues' => 'queues/queues']) !!}
    <h1>All Queues</h1>

    {!! $submissions->render() !!}

    <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
            <a class="nav-link active" id="promptTab" data-toggle="tab" href="#prompts" role="tab">Prompts</a>
        </li>
        @if (Settings::get('is_design_queue_public'))
            <li class="nav-item">
                <a class="nav-link" id="designsTab" data-toggle="tab" href="#designs" role="tab">Designs</a>
            </li>
        @endif
    </ul>
    <!-- PROMPTS TAB -->
    <div class="card-body tab-content">
        <div class="tab-pane fade show active" id="prompts">
            <div>
                {!! Form::open(['method' => 'GET', 'class' => '']) !!}
                <div class="form-inline justify-content-end">
                    <div class="form-group ml-3 mb-3">
                        {!! Form::select('prompt_category_id', $categories, Request::get('prompt_category_id'), ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group ml-3 mb-3">
                        {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
                    </div>
                </div>
                {!! Form::close() !!}

                {!! $submissions->render() !!}

                <div class="mb-4 logs-table">
                    <div class="logs-table-header">
                        <div class="row">
                            @if (!$isClaims)
                                <div class="col-12 col-md-2">
                                    <div class="logs-table-cell">Prompt</div>
                                </div>
                                <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                                    <div class="logs-table-cell">User</div>
                                </div>
                                <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }}">
                                    <div class="logs-table-cell">Link</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="logs-table-cell">Submitted</div>
                                </div>
                                <div class="col-6 col-md-1">
                                    <div class="logs-table-cell">Status</div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="logs-table-body">
                        @foreach ($submissions as $submission)
                            <div class="logs-table-row" style="{{ $submission->user->id == Auth::user()->id ? 'background-color:rgba(48, 121, 240, 0.1);' : '' }}">
                                <div class="row flex-wrap">
                                    @if (!$isClaims)
                                        <div class="col-12 col-md-2">
                                            <div class="logs-table-cell">{!! $submission->prompt->displayName !!}</div>
                                        </div>
                                        <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                                            <div class="logs-table-cell">{!! $submission->user->displayName !!}</div>
                                        </div>
                                        <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }}">
                                            <div class="logs-table-cell"><span class="ubt-texthide"><a href="{{ $submission->url }}">{{ $submission->url }}</a></span></div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="logs-table-cell">{!! pretty_date($submission->created_at) !!}</div>
                                        </div>
                                        <div class="col-3 col-md-1">
                                            <div class="logs-table-cell"><span
                                                    class="btn btn-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $submission->status }}</span></div>
                                        </div>
                                        <div class="col-3 col-md-1">
                                            <div class="logs-table-cell"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="text-center mt-4 small text-muted">
                {{ $submissions->total() }} result{{ $submissions->total() == 1 ? '' : 's' }} found.
            </div>
        </div>

        <!-- DESIGNS TAB -->
        @if (Settings::get('is_design_queue_public'))
            <div class="tab-pane fade mt-5" id="designs">
                {!! $designs->render() !!}
                <div class="mb-4 logs-table">
                    <div class="logs-table-header">
                        <div class="row">
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">Character/MYO</div>
                            </div>
                            <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                                <div class="logs-table-cell">User</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">Submitted</div>
                            </div>
                            <div class="col-6 col-md-1">
                                <div class="logs-table-cell">Status</div>
                            </div>
                        </div>
                    </div>
                    <div class="logs-table-body">
                        @foreach ($designs as $design)
                            <div class="logs-table-row" style="{{ $design->user->id == Auth::user()->id ? 'background-color:rgba(48, 121, 240, 0.1);' : '' }}">
                                <div class="row flex-wrap">
                                    <div class="col-3 col-md-3">
                                        <div class="logs-table-cell">{!! $design->character->displayName !!}</div>
                                    </div>
                                    <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                                        <div class="logs-table-cell">{!! $design->user->displayName !!}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="logs-table-cell">{!! pretty_date($design->created_at) !!}</div>
                                    </div>
                                    <div class="col-3 col-md-1">
                                        <div class="logs-table-cell"><span class="btn btn-{{ $design->status == 'Pending' ? 'secondary' : ($design->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $design->status }}</span></div>
                                    </div>
                                    <div class="col-3 col-md-1">
                                        <div class="logs-table-cell"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="text-center mt-4 small text-muted">
                    {{ $designs->total() }} result{{ $designs->total() == 1 ? '' : 's' }} found.
                </div>
            </div>
        @endif
    </div>

@endsection
