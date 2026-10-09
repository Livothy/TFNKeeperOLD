@extends('admin.layout')

@section('admin-title')
    {{ $raidBoss->id ? 'Edit' : 'Create' }} {{ ucwords(__('raids.raid') . ' ' . __('raids.boss')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucwords(__('raids.raid') . ' ' . __('raids.bosses')) => 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses'),
        ($raidBoss->id ? 'Edit' : 'Create') . ' ' . ucwords(__('raids.raid') . ' ' . __('raids.boss')) => $raidBoss->id
            ? 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/edit/' . $raidBoss->id
            : 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/create',
    ]) !!}

    <h1>
        {{ $raidBoss->id ? 'Edit' : 'Create' }} {{ ucwords(__('raids.raid') . ' ' . __('raids.boss')) }}
        @if ($raidBoss->id)
            <a href="#" class="btn btn-danger float-right delete-raid-boss-button m-1">Delete {{ ucwords(__('raids.raid') . ' ' . __('raids.boss')) }}</a>
            <a href="{{ url('admin/data/raids/edit/' . $raidBoss->raid_id) }}" class="btn btn-info float-right m-1">Edit {{ ucfirst(__('raids.raid')) }}</a>
        @endif
    </h1>

    @if ($raidBoss->id)
        <div class="card text-center mb-3">
            <div class="card-body">
                <h3 class="mb-1">
                    {!! $raidBoss->displayName !!}
                </h3>
                <h5>
                    Belongs to {{ __('raids.raid') }} {!! $raidBoss->raid->displayName !!}
                </h5>
                @include('widgets.raids._raid_boss_health', ['raidBoss' => $raidBoss])
            </div>
        </div>
    @endif

    {!! Form::open(['url' => $raidBoss->id ? 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/edit/' . $raidBoss->id : 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/create/' . $raid->id, 'files' => true]) !!}

    @if (!$raidBoss->id)
        <div class="alert alert-danger">
            You are creating a {{ __('raids.raid') . ' ' . __('raids.boss') }} for <b>{!! $raid->name !!}</b>.
            {!! Form::hidden('raid_id', $raid->id) !!}
        </div>
    @endif

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $raidBoss->name, ['class' => 'form-control']) !!}
    </div>

    @if (!$raidBoss->id)
        <div class="alert alert-danger">
            You will be able to upload images <b>after the {{ __('raids.boss') }} is first created.</b>
        </div>
    @endif

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!} {!! add_help('This is the description of the ' . __('raids.raid') . ' ' . __('raids.boss') . '.') !!}
        {!! Form::textarea('description', $raidBoss->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $raidBoss->id ? $raidBoss->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help(ucfirst(__('raids.raid')) . ' ' . __('raids.bosses') . ' that are not visible will be hidden from the ' . __('raids.raid') . ' ' . __('raids.boss') . ' list.') !!}
            </div>
        </div>

        @if (isset($raidBoss->raid->status) && $raidBoss->raid->status < 3)
            <div class="col-md">
                <div class="form-group">
                    {!! Form::label('health', 'Health', ['class' => 'form-label']) !!} {!! add_help('The total health the ' . __('raids.raid') . ' ' . __('raids.boss') . ' has.') !!}
                    {!! Form::number('health', $raidBoss->id ? $raidBoss->health : null, ['class' => 'form-control', 'placeholder' => 'Enter health...']) !!}
                </div>
            </div>
        @endif
    </div>

    @if ($raidBoss->id && $raidBoss->raid->status < 3)
        <h3>Health Thresholds</h3>
        <p class="mb-0">
            Here you can indicate if the color of the remaining health bar and text should change based on the {{ __('raids.boss') }}'s current health. <b>You must indicate a color for the bar AND/OR bar's text</b> for the entry to be saved. Type and
            amount are <b>required</b>.
        </p>
        <div class="text-right mb-2">
            <a class="btn btn-primary" id="addThreshold" href="#" type="button">Add Threshold</a>
        </div>
        <div id="thresholdsBody" class="border-top pt-1">
            @if ($raidBoss->thresholds)
                @foreach ($raidBoss->thresholds as $threshold)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="text-right">
                                <a href="#" class="btn btn-danger btn-sm remove-threshold">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                            <div class="row mb-1">
                                <div class="col">
                                    <div class="form-group">
                                        {!! Form::label('threshold_type[]', 'Type') !!} {!! add_help('Is this based on percentage or a specified amount?') !!}
                                        {!! Form::select('threshold_type[]', ['percent' => 'Percentage', 'amount' => 'Specific Amount'], $threshold['type'] ?? null, ['class' => 'form-control', 'placeholder' => 'Select type...']) !!}
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        {!! Form::label('threshold_amount[]', 'Amount') !!}
                                        {!! Form::number('threshold_amount[]', $threshold['amount'] ?? null, ['class' => 'form-control', 'placeholder' => 'Enter a number...', 'steps' => '1']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col">
                                    <div class="form-group">
                                        {!! Form::label('threshold_bar_color[]', 'Health Bar Color (Optional)') !!}
                                        <div class="input-group cp">
                                            {!! Form::text('threshold_bar_color[]', $threshold['bar_color'] ?? null, ['class' => 'form-control']) !!}
                                            <span class="input-group-append">
                                                <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="form-group">
                                        {!! Form::label('threshold_text_color[]', 'Health Bar Text Color (Optional)') !!}
                                        <div class="input-group cp">
                                            {!! Form::text('threshold_text_color[]', $threshold['text_color'] ?? null, ['class' => 'form-control']) !!}
                                            <span class="input-group-append">
                                                <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                This threshold will be active when the {{ __('raids.boss') }} is at
                                @if ($threshold['type'] == 'amount')
                                    <b>{{ $threshold['amount'] }} amount of health</b> or less.
                                @else
                                    <b>{{ $threshold['amount'] > 0 ? $raidBoss->health * ($threshold['amount'] / 100) : '0' }} amount of health ({{ $threshold['amount'] }}%)</b> or less. (Value will be rounded.)
                                @endif
                            </div>
                            @if ($raidBoss->health)
                                <h5 class="text-center mb-0">
                                    Bar Preview
                                </h5>
                                @if ($threshold['type'] == 'amount')
                                    <div class="progress font-weight-bold">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                            style="width: {{ $threshold['amount'] > 0 ? ($threshold['amount'] / $raidBoss->health) * 100 : 0 }}%; {!! isset($threshold['bar_color']) ? 'background-color: ' . $threshold['bar_color'] . '; ' : '' !!}{!! isset($threshold['text_color']) ? 'color: ' . $threshold['text_color'] . ';' : '' !!}" aria-valuenow="{{ $threshold['amount'] }}"
                                            aria-valuemin="0" aria-valuemax="{{ $raidBoss->health }}">
                                            {{ $threshold['amount'] }} / {{ $raidBoss->health }}
                                        </div>
                                    </div>
                                @else
                                    <div class="progress font-weight-bold">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $threshold['amount'] }}%; {!! isset($threshold['bar_color']) ? 'background-color: ' . $threshold['bar_color'] . '; ' : '' !!}{!! isset($threshold['text_color']) ? 'color: ' . $threshold['text_color'] . ';' : '' !!}"
                                            aria-valuenow="{{ $threshold['amount'] > 0 ? $raidBoss->health * ($threshold['amount'] / 100) : '0' }}" aria-valuemin="0" aria-valuemax="{{ $raidBoss->health }}">
                                            {{ $threshold['amount'] > 0 ? $raidBoss->health * ($threshold['amount'] / 100) : '0' }} / {{ $raidBoss->health }}
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif

    <div class="text-right mt-3">
        {!! Form::submit($raidBoss->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}

    @if ($raidBoss->id)
        <hr>

        <h3 class="mb-1">
            {{ ucfirst(__('raids.boss')) }} Images
        </h3>
        <p>
            You may create and edit images for this {{ __('raids.boss') }} below.
            @if ($raidBoss->raid->status < 3)
                You may set a single image, or instead define multiple images that will display based on the {{ __('raids.boss') }}'s current health. Decimal values for health thresholds are always rounded.
            @endif
        </p>
        <div class="text-right mb-1">
            <a href="#" class="btn btn-sm btn-primary create-boss-image">
                Add New Image
            </a>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                @if (!$bossImages)
                    <p class="text-center text-muted">
                        This {{ __('raids.boss') }} doesn't have any images yet.
                    </p>
                @else
                    <div class="row no-gutters">
                        @foreach ($bossImages as $image)
                            <div class="col-md-4 p-1 text-center">
                                <div class="card h-100 p-2">
                                    @if ($image->imageUrl)
                                        <div class="mb-2">
                                            <img src="{{ $image->imageUrl }}" class="img-fluid" alt="Image of {{ $raidBoss->name }}">
                                        </div>
                                    @endif
                                    <div class="font-weight-bold">
                                        {{ ucfirst($image->threshold_type) }} - {{ $image->health_threshold ?? 'N/A' }}
                                    </div>
                                    <div class="small">
                                        ({!! $image->thresholdString !!})
                                    </div>
                                    <div>
                                        <a href="#" class="edit-boss-image btn btn-info btn-sm m-1" data-id="{{ $image->id }}">
                                            Edit
                                        </a>
                                        <a href="#" class="delete-boss-image btn btn-danger btn-sm m-1" data-id="{{ $image->id }}">
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if ($raidBoss->raid->status < 3)
            <div class="mb-2 threshold-row hide">
                <div class="card">
                    <div class="card-body">
                        <div class="text-right">
                            <a href="#" class="btn btn-danger btn-sm remove-threshold">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <div class="row mb-1">
                            <div class="col">
                                <div class="form-group">
                                    {!! Form::label('threshold_type[]', 'Type') !!} {!! add_help('Is this based on percentage or a specified amount?') !!}
                                    {!! Form::select('threshold_type[]', ['percent' => 'Percentage', 'amount' => 'Specific Amount'], null, ['class' => 'form-control', 'placeholder' => 'Select type...']) !!}
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    {!! Form::label('threshold_amount[]', 'Amount') !!}
                                    {!! Form::number('threshold_amount[]', null, ['class' => 'form-control', 'placeholder' => 'Enter a number...', 'steps' => '1']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    {!! Form::label('threshold_bar_color[]', 'Health Bar Color (Optional)') !!}
                                    <div class="input-group cp">
                                        {!! Form::text('threshold_bar_color[]', null, ['class' => 'form-control']) !!}
                                        <span class="input-group-append">
                                            <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    {!! Form::label('threshold_text_color[]', 'Health Bar Text Color (Optional)') !!}
                                    <div class="input-group cp">
                                        {!! Form::text('threshold_text_color[]', null, ['class' => 'form-control']) !!}
                                        <span class="input-group-append">
                                            <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            @if ($raidBoss->id)
                $('.delete-raid-boss-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/delete') }}/{{ $raidBoss->id }}", "Delete {{ ucwords(__('raids.raid') . ' ' . __('raids.boss')) }}");
                });
                $('.create-boss-image').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/' . $raidBoss->id . '/image/create') }}", "Add {{ ucfirst(__('raids.boss')) }} Image");
                });
                $('.edit-boss-image').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/' . $raidBoss->id . '/image/edit') }}/" + $(this).data('id'), "Edit {{ ucfirst(__('raids.boss')) }} Image");
                });
                $('.delete-boss-image').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/' . $raidBoss->id . '/image/delete') }}/" + $(this).data('id'), "Delete {{ ucfirst(__('raids.boss')) }} Image");
                });

                var $thresholds = $('#thresholdsBody');
                var $thresholdRow = $('.threshold-row');

                attachRemoveListener($('#thresholdsBody .remove-threshold'));

                $('#addThreshold').on('click', function(e) {
                    e.preventDefault();
                    var $clone = $thresholdRow.clone();
                    $thresholds.append($clone);
                    $clone.removeClass('threshold-row hide');
                    $clone.find('.cp').colorpicker();
                    $clone.find('[data-toggle="tooltip"]').tooltip({
                        html: true
                    });
                    attachRemoveListener($clone.find('.remove-threshold'));
                });

                function attachRemoveListener(node) {
                    node.on('click', function(e) {
                        e.preventDefault();
                        $(this).parent().parent().parent().remove();
                    });
                }
            @endif
        });
    </script>
@endsection
