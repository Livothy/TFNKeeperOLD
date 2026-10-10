@extends('admin.layout')

@section('admin-title')
    Fetch Quests
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Fetch Quests' => 'admin/data/fetch-quests',
        ($fetchquest->id ? 'Edit' : 'Create') . ' FetchQuest' => $fetchquest->id
            ? 'admin/data/fetch-quests/edit/' . $fetchquest->id
            : 'admin/data/fetch-quests/create',
    ]) !!}

    <h1>{{ $fetchquest->id ? 'Edit' : 'Create' }} Fetch Quest
        @if ($fetchquest->id)
            <a href="#" class="btn btn-danger float-right delete-fetchquest-button">Delete Fetch Quest</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $fetchquest->id ? 'admin/data/fetch-quests/edit/' . $fetchquest->id : 'admin/data/fetch-quests/create',
        'files' => true,
    ]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $fetchquest->name, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('Questgiver Name') !!}{!! add_help('Optional. Who is giving the quest?') !!}
        {!! Form::text('questgiver_name', $fetchquest->questgiver_name, ['class' => 'form-control']) !!}
    </div>

    <div class="row">
        @if ($fetchquest->has_image)
            <div class="col-md-2">
                <div class="form-group">
                    <img src="{{ $fetchquest->imageUrl }}" class="img-fluid mr-2 mb-2" style="height: 10em;" />
                    <br>
                </div>
            </div>
        @endif
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Fetch Quest Image (Optional)') !!} {!! add_help('This image will show up on the fetch quest homepage.') !!}
                <div>{!! Form::file('image') !!}</div>
                <div class="text-muted">Recommended size: 100px x 100px</div>
                @if ($fetchquest->has_image)
                    <div class="form-check">
                        {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                        {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $fetchquest->description, ['class' => 'form-control wysiwyg']) !!}
    </div>


    <div class="form-group">
        {!! Form::checkbox('is_active', 1, $fetchquest->id ? $fetchquest->is_active : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
        ]) !!}
        {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('fetch quests that are not active will be hidden from the fetch quest list.') !!}
    </div>


    {!! Form::label('Cooldown') !!}{!! add_help('Cooldown before a user can complete this fetch quest again') !!}
    {!! Form::text('cooldown', $fetchquest->cooldown ?? null, [
        'class' => 'form-control cooldown-field',
        'data-name' => 'cooldown',
    ]) !!}
    <br>

    <h2>Currency Reward</h2>
    <p>Set a currency max/min for the user to recieve here. In its base state, the user will recieve a random amount between
        the max and min that you set here.</p>
    <p>If all parameters are set, the currency reward will randomize between the minimum/maximum value for the minimum and
        max reward. Kind of confusing I know, but basically it just means you can randomize the currency gen just a little
        more.</p>
    <p>Set on only the Minimum Reward (MIN) and Maximum Reward (MIN) for a constant reward that randomizes between those two
        amounts.</p>
    <div class="form-group">
        {!! Form::label('Currency Rewarded') !!}
        {!! Form::select('currency_id', $currencies, $fetchquest->currency_id, ['class' => 'form-control']) !!}
    </div>
    <div class="row">
        <div class="form-group col-6">
            {!! Form::label('reward_min_min', 'Minimum Reward (MIN)') !!}
            {!! Form::number(
                'reward_min_min',
                isset($fetchquest->extras['reward_min_min']) ? $fetchquest->extras['reward_min_min'] : '',
                ['class' => 'form-control', 'placeholder' => 'Minimum Reward (MIN)', 'min' => 1, 'max' => 100],
            ) !!}
        </div>
        <div class="form-group col-6">
            {!! Form::label('reward_min_max', 'Minimum Reward (MAX)') !!} {!! add_help('Max for the minimum reward to randomize into.') !!}
            {!! Form::number(
                'reward_min_max',
                isset($fetchquest->extras['reward_min_max']) ? $fetchquest->extras['reward_min_max'] : '',
                ['class' => 'form-control', 'placeholder' => 'Minimum Reward (MAX)', 'min' => 1, 'max' => 100],
            ) !!}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-6">
            {!! Form::label('reward_max_min', 'Maximum Reward (MIN)') !!}
            {!! Form::number(
                'reward_max_min',
                isset($fetchquest->extras['reward_max_min']) ? $fetchquest->extras['reward_max_min'] : '',
                ['class' => 'form-control', 'placeholder' => 'Maximum Reward (MIN)', 'min' => 1, 'max' => 100],
            ) !!}
        </div>
        <div class="form-group col-6">
            {!! Form::label('reward_max_max', 'Maximum Reward (MAX)') !!} {!! add_help('Max for the maximum reward to randomize into.') !!}
            {!! Form::number(
                'reward_max_max',
                isset($fetchquest->extras['reward_max_max']) ? $fetchquest->extras['reward_max_max'] : '',
                ['class' => 'form-control', 'placeholder' => 'Maximum Reward (MAX)', 'min' => 1, 'max' => 100],
            ) !!}
        </div>
    </div>

    <h3>Extra Rewards</h3>
    <p>These rewards are credited alongside the currency rewards above. When this fetch quest is completed by a user, ONE
        random reward from below will be selected.</p>
    @include('widgets._loot_select', [
        'loots' => $fetchquest->rewards,
        'showLootTables' => true,
        'showRaffles' => true,
    ])

    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                {!! Form::label('Category (Optional)') !!} {!! add_help('Optional category for items to be selected within. If this is set then it will only select items within this category and will also further filter out with the exceptions below.') !!}
                {!! Form::select('fetch_category', $fetchCategories, $fetchquest->fetch_category, ['class' => 'form-control categoryselectize']) !!}
            </div>
        </div>
    </div>

    <h3>Exceptions</h3>
    <p>You can select items or a category of item that you DO NOT want to be randomized into here.</p>
    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info" id="addException">Add Exception</a>
    </div>
    <table class="table table-sm" id="exceptionTable">
        <thead>
            <tr>
                <th width="35%">Exception Type</th>
                <th width="35%">Exception</th>
                <th width="10%"></th>
            </tr>
        </thead>
        <tbody id="exceptionTableBody">
            @if ($fetchquest->exceptions)
                @foreach ($fetchquest->exceptions as $exception)
                    <tr class="exception-row">
                        <td>{!! Form::select(
                            'exception_type[]',
                            ['Item' => 'Item', 'ItemCategory' => 'Item Category'],
                            $exception->exception_type,
                            [
                                'class' => 'form-control exception-type',
                                'placeholder' => 'Select Exception Type',
                            ],
                        ) !!}</td>
                        <td class="exception-row-select">
                            @if ($exception->exception_type == 'Item')
                                {!! Form::select('exception_id[]', $items, $exception->exception_id, [
                                    'class' => 'form-control item-select selectize',
                                    'placeholder' => 'Select Item',
                                ]) !!}
                            @elseif($exception->exception_type == 'ItemCategory')
                                {!! Form::select('exception_id[]', $categories, $exception->exception_id, [
                                    'class' => 'form-control category-select selectize',
                                    'placeholder' => 'Select Category',
                                ]) !!}
                            @endif
                        </td>
                        <td class="text-right"><a href="#" class="btn btn-danger remove-exception-button">Remove</a>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="text-right">
        {!! Form::submit($fetchquest->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', [
        'items' => $items,
        'currencies' => $currencies,
        'tables' => $tables,
        'raffles' => $raffles,
        'showLootTables' => true,
        'showRaffles' => true,
    ])

    <div id="exceptionRowData" class="hide">
        <table class="table table-sm">
            <tbody id="exceptionRow">
                <tr class="exception-row">
                    <td>{!! Form::select('exception_type[]', ['Item' => 'Item', 'ItemCategory' => 'Item Category'], null, [
                        'class' => 'form-control exception-type',
                        'placeholder' => 'Select Exception Type',
                    ]) !!}</td>
                    <td class="exception-row-select"></td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-exception-button">Remove</a></td>
                </tr>
            </tbody>
        </table>
        {!! Form::select('exception_id[]', $items, null, [
            'class' => 'form-control item-select',
            'placeholder' => 'Select Item',
        ]) !!}
        {!! Form::select('exception_id[]', $categories, null, [
            'class' => 'form-control category-select',
            'placeholder' => 'Select Category',
        ]) !!}
    </div>

    @if ($fetchquest->id)
        <h3>Preview</h3>
        @include('fetchquests._fetch_entry', ['fetch' => $fetchquest])
    @endif
@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])

    <script>
        $(document).ready(function() {
            var $exceptionTable = $('#exceptionTableBody');
            var $exceptionRow = $('#exceptionRow').find('.exception-row');
            var $itemSelect = $('#exceptionRowData').find('.item-select');
            var $categorySelect = $('#exceptionRowData').find('.category-select');

            $('#exceptionTableBody .selectize').selectize();
            $('.categoryselectize').selectize();
            attachRemoveListener($('#exceptionTableBody .remove-exception-button'));

            $('#addException').on('click', function(e) {
                e.preventDefault();
                var $clone = $exceptionRow.clone();
                $exceptionTable.append($clone);
                attachExceptionTypeListener($clone.find('.exception-type'));
                attachRemoveListener($clone.find('.remove-exception-button'));
            });

            $('.exception-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().find('.exception-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'ItemCategory') $clone = $categorySelect.clone();


                $cell.html('');
                $cell.append($clone);
            });

            function attachExceptionTypeListener(node) {
                node.on('change', function(e) {
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.exception-row-select');

                    var $clone = null;
                    if (val == 'Item') $clone = $itemSelect.clone();
                    else if (val == 'ItemCategory') $clone = $categorySelect.clone();

                    $cell.html('');
                    $cell.append($clone);
                    $clone.selectize();
                });
            }

            function attachRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
            }
            $('.delete-fetchquest-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/fetch-quests/delete') }}/{{ $fetchquest->id }}",
                    'Delete FetchQuest');
            });
        });
    </script>
@endsection
