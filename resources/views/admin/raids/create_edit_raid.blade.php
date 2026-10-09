@extends('admin.layout')

@section('admin-title')
    {{ $raid->id ? 'Edit' : 'Create' }} {{ ucfirst(__('raids.raids')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucfirst(__('raids.raids')) => 'admin/data/' . __('raids.raids'),
        ($raid->id ? 'Edit' : 'Create') . ' ' . ucfirst(__('raids.raid')) => $raid->id ? 'admin/data/' . __('raids.raids') . '/edit/' . $raid->id : 'admin/data/' . __('raids.raids') . '/create',
    ]) !!}

    <h1>
        {{ $raid->id ? 'Edit' : 'Create' }} {{ ucfirst(__('raids.raid')) }}
        @if ($raid->id)
            <a href="#" class="btn btn-danger float-right delete-raid-button">Delete {{ ucfirst(__('raids.raid')) }}</a>
        @endif
    </h1>

    @if ($raid->id)
        @if ($raid->status == 0)
            <div class="alert alert-success">
                <div class="mb-1 h3">This {{ __('raids.raid') }} hasn't begun yet.</div>
                <div>
                    If a {{ __('raids.raid') }} has no start time, it must be manually started.
                    @if ($raid->start_at)
                        This {{ __('raids.raid') }} is set to begin {!! pretty_date($raid->start_at) !!}
                    @endif
                    <br>
                    If you'd like to manually start the {{ __('raids.raid') }}, please click through with the 'Start {{ ucfirst(__('raids.raid')) }}' button.
                </div>
                <div class="text-right mt-2">
                    <a href="#" class="start-raid-button btn btn-primary">
                        Start {{ ucfirst(__('raids.raid')) }}
                    </a>
                </div>
            </div>
        @elseif ($raid->status == 1)
            <div class="alert alert-primary">
                <div class="mb-1 h3">This {{ __('raids.raid') }} is currently ongoing!</div>
                <div>
                    If a {{ __('raids.raid') }} has no end time, is set to continue after the {{ __('raids.boss') }} is defeated, or the {{ __('raids.boss') }} does not have a set health target, it will continue indefinitely.
                    @if ($raid->end_at)
                        This {{ __('raids.raid') }} is set to end {!! pretty_date($raid->end_at) !!}
                    @endif
                    <br>
                    If you'd like to manually end the {{ __('raids.raid') }}, please click through with the 'End {{ ucfirst(__('raids.raid')) }}' button.
                </div>
                <div class="text-right mt-2">
                    <a href="#" class="end-raid-button btn btn-primary">
                        End {{ ucfirst(__('raids.raid')) }}
                    </a>
                </div>
            </div>
        @elseif ($raid->status == 2)
            <div class="alert alert-success">
                <div class="mb-1 h3">This {{ __('raids.raid') }} has been defeated!</div>
                <div>
                    This {{ __('raids.raid') }} has been defeated! It ended {!! pretty_date($raid->end_at) !!}. There were a total of {{ $raid->participantCount }} {{ $raid->participantCount == 1 ? 'participant' : 'participants' }}.
                    <br>
                    Rewards are not distributed automatically as to give staff time to review participation and adjust rewards as needed. <b>To distribute proper rewards to all participants, please click the button below.</b>
                </div>
                <div class="text-right mt-2">
                    <a href="#" class="reward-raid-button btn btn-primary">
                        Distribute Rewards
                    </a>
                </div>
            </div>
        @elseif ($raid->status == 3)
            <div class="alert alert-success">
                <div class="mb-1 h3">Concluded {{ ucfirst(__('raids.raid')) }}</div>
                <div>
                    This {{ __('raids.raid') }} has concluded and rewards have been distributed.
                    <br>
                    Rewards were distributed {!! pretty_date($raid->distributed_at) !!}.
                </div>
            </div>
        @endif
    @endif

    {!! Form::open(['url' => $raid->id ? 'admin/data/' . __('raids.raids') . '/edit/' . $raid->id : 'admin/data/' . __('raids.raids') . '/create', 'files' => true]) !!}

    @if (!$raid->id)
        <div class="alert alert-danger">
            First, you must set some basic information for this {{ __('raids.raid') }}. <b>Once the {{ __('raids.raid') }} is created</b> you may indicate rewards, {{ __('raids.bosses') }}, requirements, and toggle visibility on.
        </div>
    @endif

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $raid->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Background Image (Optional)') !!} {!! add_help('This image is the ' . __('raids.raid') . '\'s background that the ' . __('raids.boss') . ' will be placed on top of.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Accepted filetypes: png, gif, webp.</div>
        @if ($raid->has_background)
            <div>
                <a href="{{ $raid->imageUrl }}" target="_blank">View current background?</a>
            </div>
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current background', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!} {!! add_help('This is the description of the ' . __('raids.raid') . ' that shows up on the ' . __('raids.raid') . ' page.') !!}
        {!! Form::textarea('description', $raid->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    @if ($raid->id && $raid->status < 3)
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('start_at', 'Start Time (Optional)') !!} {!! add_help(
                        ucfirst(__('raids.raids')) . ' cannot be attacked before the starting time. If the ' . __('raids.raid') . ' currently is set to not be visible, the ' . __('raids.raid') . ' will <b>automatically become visible</b> once past starting time.',
                    ) !!}
                    {!! Form::text('start_at', $raid->start_at, ['class' => 'form-control datepicker']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('end_at', 'End Time (Optional)') !!} {!! add_help(ucfirst(__('raids.raids')) . ' cannot be attacked after the ending time.') !!}
                    {!! Form::text('end_at', $raid->end_at, ['class' => 'form-control datepicker']) !!}
                </div>
            </div>
        </div>
    @endif

    @if ($raid->id && $raid->status == 3)
        <div class="form-group">
            {!! Form::checkbox('is_visible', 1, $raid->id ? $raid->is_visible : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!}
        </div>
    @endif

    @if ($raid->id && $raid->status < 3)
        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    {!! Form::checkbox('is_visible', 1, $raid->id ? $raid->is_visible : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
                        ucfirst(__('raids.raids')) . ' that are not visible will be hidden from the ' . __('raids.raid') . ' list. The start time setting overrides this setting, i.e. if this is set to hidden, it will still be visible past the start time.',
                    ) !!}
                </div>
            </div>

            <div class="col-md">
                <div class="form-group">
                    {!! Form::checkbox('continue_raid', 1, $raid->id ? $raid->continue_raid : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('continue_raid', 'Continue after health depleted?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is toggled <b>on</b>, the ' . __('raids.raid') . ' will not end when the ' . __('raids.boss') . '\'s health is surpassed in damage. When set to <b>off</b>, the ' . __('raids.raid') . ' will automatically conclude.') !!}
                </div>
            </div>
        </div>

        <h3>{{ ucwords(__('raids.raid') . ' ' . __('raids.boss')) }}</h3>
        @if (!$raid->currentBoss())
            <p>
                Click the button below to create a {{ __('raids.boss') }} for this {{ __('raids.raid') }}.
            </p>
            <a href="{{ url('admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/create/' . $raid->id) }}" class="btn btn-primary d-block mb-3">
                Create {{ ucwords(__('raids.raid') . ' ' . __('raids.boss')) }}
            </a>
        @else
            @if ($raid->currentBoss())
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <h4 class="mb-1">
                                Current {{ ucfirst(__('raids.boss')) }} Preview
                            </h4>
                            @include('widgets.raids._raid_boss_display', ['raid' => $raid, 'raidBoss' => $raid->currentBoss()])
                            <a class="btn btn-primary m-1" href="{{ $raid->currentBoss()->adminUrl }}">
                                Edit {{ ucfirst(__('raids.boss')) }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <hr>

        <h3>Damage</h3>
        <p>
            Indicate what deals damage to this {{ __('raids.raid') }} below. You may indicate a currency or an item, its required quantity per attack, and how much damage an attack deals with an optional range for varied damage.
        </p>
        @include('widgets.raids._raid_damage_select', ['damage' => $raid->damage])

        <hr>

        <h3>Rewards</h3>
        <p>
            Rewards are credited on a per-user basis. Only users who have participated in the {{ __('raids.raid') }} and have done sufficient enough damage will be rewarded anything indicated below. Rewards are distributed at the end of the
            {{ __('raids.raid') }}. The prizes a user receives is <b>inclusive of all the damage thresholds they have passed</b> - i.e. if you reward 5 currency for dealing 20 damage, 10 currency for dealing 30 damage, and 50 currency for dealing 35
            damage, a user who has dealt 31 damage by the end of the {{ __('raids.raid') }} will receive a total of 15 currency.
        </p>
        <p>
            If you want a reward to be distributed to <i>all participating users</i> regardless of the amount of damage they dealt, set the damage requirement to <b>0</b>
        </p>
        @include('widgets.raids._raid_loot_select', ['loots' => $raid->rewards, 'showLootTables' => true, 'showRaffles' => true])
    @endif

    <div class="text-right mt-3">
        {!! Form::submit($raid->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}

    @if ($raid->id)
        @include('widgets.raids._raid_loot_select_row', ['showLootTables' => true, 'showRaffles' => true])
    @endif
@endsection

@section('scripts')
    @parent
    @if ($raid->id)
        @include('js._raid_loot_js', ['showLootTables' => true, 'showRaffles' => true])
    @endif
    @include('widgets._datetimepicker_js')
    <script>
        $(document).ready(function() {
            $('.delete-raid-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/' . __('raids.raids') . '/delete') }}/{{ $raid->id }}", "Delete {{ ucfirst(__('raids.raid')) }}");
            });

            @if ($raid->id)
                @if (!$raid->status)
                    $('.start-raid-button').on('click', function(e) {
                        e.preventDefault();
                        loadModal("{{ url('admin/data/' . __('raids.raids') . '/start') }}/{{ $raid->id }}", "Manually Start {{ ucfirst(__('raids.raid')) }}?");
                    });
                @elseif ($raid->status == 1)
                    $('.end-raid-button').on('click', function(e) {
                        e.preventDefault();
                        loadModal("{{ url('admin/data/' . __('raids.raids') . '/end') }}/{{ $raid->id }}", "Manually End {{ ucfirst(__('raids.raid')) }}?");
                    });
                @elseif ($raid->status == 2)
                    $('.reward-raid-button').on('click', function(e) {
                        e.preventDefault();
                        loadModal("{{ url('admin/data/' . __('raids.raids') . '/reward') }}/{{ $raid->id }}", "Distribute {{ ucfirst(__('raids.raid')) }} Rewards");
                    });
                @endif
            @endif

            $('#damageTableBody .selectize').selectize();
            var $damageItemSelect = $('.damage-data').find('.damage-item-select');
            var $damageCurrencySelect = $('.damage-data').find('.damage-currency-select');

            $('.damage-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().parent().find('.damage-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $damageItemSelect.clone();
                else if (val == 'Currency') $clone = $damageCurrencySelect.clone();

                $cell.html('');
                $cell.append($clone);
                $clone.selectize();
            });
        });
    </script>
@endsection
