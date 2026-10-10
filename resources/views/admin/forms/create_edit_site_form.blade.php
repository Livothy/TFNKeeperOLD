@extends('admin.layout')

@section('admin-title')
    Forms & Polls
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Forms & Polls' => 'admin/forms', ($form->id ? 'Edit' : 'Create') . ' Post' => $form->id ? 'admin/forms/edit/' . $form->id : 'admin/forms/create']) !!}

    <h1>{{ $form->id ? 'Edit' : 'Create' }} Form
        @if ($form->id)
            <a href="#" class="btn btn-danger float-right delete-form-button">Delete Form</a>
        @endif
    </h1>

    {!! Form::open(['url' => $form->id ? 'admin/forms/edit/' . $form->id : 'admin/forms/create', 'files' => true]) !!}

    <h3>Basic Information</h3>
    <div class="row">
        <div class="form-group col">
            {!! Form::label('Title') !!}
            {!! Form::text('title', $form->title, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col">
            {!! Form::label('timeframe', 'Form Timeframe') !!} {!! add_help('This is the timeframe during which users can submit this form. I.E. yearly will only allow one submission per year, while lifetime will allow one submission total.') !!}
            {!! Form::select('timeframe', ['lifetime' => 'Lifetime', 'daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'], $form ? $form->timeframe : 0, ['class' => 'form-control stock-field', 'data-name' => 'timeframe']) !!}
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description') !!}
        {!! Form::textarea('description', $form->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                {!! Form::checkbox('is_active', 1, $form->id ? $form->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the form will not be visible to regular users.') !!}
            </div>
        </div>
        @if ($form->id && $form->is_active)
            <div class="col-md">
                <div class="form-group">
                    {!! Form::checkbox('bump', 1, null, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('bump', 'Bump form', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If toggled on, this will alert users that there is a new form. Best in conjunction with a clear notification of changes!') !!}
                </div>
            </div>
        @endif
        <div class="col-md-2">
            <div class="form-group">
                {!! Form::checkbox('is_anonymous', 1, $form->id ? $form->is_anonymous : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_anonymous', 'Set Anonymous', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the form will not be anonymous and admins can see who gave what response.') !!}
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                {!! Form::checkbox('is_public', 1, $form->id ? $form->is_public : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_public', 'Set Public', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, users cannot see the form results. If turned on, users can see anonymous results.') !!}
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                {!! Form::checkbox('is_editable', 1, $form->id ? $form->is_editable : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_editable', 'Set Editable', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, users cannot edit their latest submission.') !!}
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                {!! Form::checkbox('allow_likes', 1, $form->id ? $form->allow_likes : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('allow_likes', 'Allow Likes', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, users cannot like answers of other people.') !!}
            </div>
        </div>
    </div>


    <div class="pl-4">
        <div class="form-group">
            {!! Form::checkbox('is_timed', 0, $form->is_timed ?? 0, ['class' => 'form-check-input form-timed form-toggle form-field', 'id' => 'is_timed']) !!}
            {!! Form::label('is_timed', 'Set Timed Form', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Sets the form as timed between the chosen dates.') !!}
        </div>
        <div class="form-timed-quantity {{ $form->is_timed ? '' : 'hide' }}">
            <p>Set the start time for when the form should become visible, and the end time at which it closes. Closed forms are still visible on site! Even if set active, a form will only show once the start time is reached. </p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('start_at', 'Start Time') !!} {!! add_help('The form will cycle in at this date.') !!}
                        {!! Form::text('start_at', $form->start_at, ['class' => 'form-control', 'id' => 'datepicker2']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('end_at', 'End Time') !!} {!! add_help('The form will cycle out at this date.') !!}
                        {!! Form::text('end_at', $form->end_at, ['class' => 'form-control', 'id' => 'datepicker3']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>
    <h3>Questions</h3>
    <p>Add your question/s here. At least one question is required for the creation of a form.</p>
    <div id="questionContainer" class="mb-5">
        @foreach ($form->questions as $question)
            <div class="card mb-2">
                <div class="card-body">

                    <div class="card-title row">
                        <h5 class="col-lg col-12">Question</h5>
                        <div class="col-lg col-6">
                            {!! Form::checkbox('is_mandatory[' . $question->id . ']', 1, $question->is_mandatory ?? 0, ['class' => 'mandatory form-check-input form-timed form-toggle form-field']) !!}
                            Mandatory {!! add_help('If turned off, this question can be left empty.') !!}
                        </div>
                        <div class="col-lg col-6">
                            {!! Form::checkbox('is_multichoice[' . $question->id . ']', 0, $question->is_multichoice ?? 0, ['class' => 'multichoice form-check-input form-timed form-toggle form-field']) !!}
                            Multichoice {!! add_help('If turned on, this question will allow choices between multiple options - does not apply to questions without options.') !!}
                        </div>
                        <a href="#" class="btn btn-danger col-1 remove-question-button">X</a>
                    </div>
                    <div class="question">{!! Form::text('questions[' . $question->id . ']', $question->question, ['class' => 'form-control mt-2 mb-2']) !!}</div>

                    <div class="d-flex align-items-center">
                        <h5 class="card-text">Options (Optional)</h5>{!! add_help('If you do not provide options, it will be considered an open answer where users can write their own response.') !!}
                    </div>
                    <div class="options" id="option-{{ $question->id }}">
                        @foreach ($question->options as $option)
                            <div class="mb-2">
                                <div class="row">
                                    <div class="col-10">
                                        {!! Form::text('options[' . $question->id . '][' . $option->id . ']', $option->option, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col"><a href="#" class="btn btn-secondary float-right remove-option-button">X</a></div>
                                </div>
                            </div>
                        @endforeach
                        <div class="hide mb-2">
                            <div class="row">
                                <div class="col-10">
                                    {!! Form::text('options[' . $question->id . '][]', null, ['class' => 'form-control']) !!}
                                </div>
                                <div class="col"><a href="#" class="btn btn-secondary float-right remove-option-button">X</a></div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-2">
                        <a href="#" class="btn btn-outline-info addOption" id="button-{{ $question->id }}">Add Option</a>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="card hide mb-2">
            <div class="card-body">
                <div class="card-title row">
                    <h5 class="col-lg col-12">Question</h5>
                    <div class="col-lg col-6">
                        {!! Form::checkbox('is_mandatory[default]', 1, 0, ['class' => 'mandatory form-check-input form-timed form-toggle form-field']) !!}
                        Mandatory {!! add_help('If turned off, this question can be left empty.') !!}
                    </div>
                    <div class="col-lg col-6">
                        {!! Form::checkbox('is_multichoice[default]', 0, 0, ['class' => 'multichoice form-check-input form-timed form-toggle form-field']) !!}
                        Multichoice {!! add_help('If turned on, this question will allow choices between multiple options - does not apply to questions without options.') !!}
                    </div>
                    <a href="#" class="btn btn-danger col-1 remove-question-button">X</a>
                </div>
                <div class="question">{!! Form::text('questions[default]', null, ['class' => 'form-control mt-2 mb-2']) !!}</div>

                <div class="d-flex align-items-center">
                    <h5 class="card-text">Options (Optional)</h5>{!! add_help('If you do not provide options, it will be considered an open answer where users can write their own response.') !!}
                </div>
                <div class="options">
                    <div class="hide mb-2">
                        <div class="row">
                            <div class="col-10">
                                {!! Form::text('options[default][]', null, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col"><a href="#" class="btn btn-secondary float-right remove-option-button">X</a></div>
                        </div>
                    </div>
                </div>

                <div class="text-right mt-2">
                    <a href="#" class="btn btn-outline-info addOption">Add Option</a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info" id="addQuestion">Add Question</a>
    </div>

    <h3>Rewards</h3>
    <p>Rewards are credited on a per-user basis. They are given out each time the user submits the form, so make sure to set the form timeframe accordingly. Edits to existing answers will not reward the user again.</p>

    @include('widgets._loot_select', ['loots' => $form->rewards, 'showLootTables' => true, 'showRaffles' => true])

    <div class="text-right">
        {!! Form::submit($form->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['items' => $items, 'currencies' => $currencies, 'tables' => $tables, 'raffles' => $raffles, 'showLootTables' => true, 'showRaffles' => true])
@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
    <script>
        $(document).ready(function() {
            $('.delete-form-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/forms/delete') }}/{{ $form->id }}", 'Delete Form');
            });

            $('#is_timed').change(function() {
                if ($(this).is(':checked')) {
                    $('.form-timed-quantity').removeClass('hide');
                } else {
                    $('.form-timed-quantity').addClass('hide');
                }
            });

            $("#datepicker2").datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: 'HH:mm:ss',
            });

            $("#datepicker3").datetimepicker({
                dateFormat: "yy-mm-dd",
                timeFormat: 'HH:mm:ss',
            });

            attachRemoveListener($('#questionContainer .remove-question-button'));
            attachRemoveListener($('#questionContainer .remove-option-button'));

            var questions = $('#questionContainer');
            var questionRow = $('#questionContainer').find('.card.hide');

            $('#addQuestion').on('click', function(e) {
                e.preventDefault();
                var questionId = Math.random().toString(16).slice(2)

                //setup clone and add its unique id
                var clone = questionRow.clone();
                clone.removeClass('hide');
                questions.append(clone);
                attachRemoveListener(clone.find('.remove-question-button'));
                var questionInput = clone.find('.question input');
                questionInput.attr("name", "questions[" + questionId + "]");
                var mandatoryInput = clone.find('.card-title .mandatory');
                mandatoryInput.attr("name", "is_mandatory[" + questionId + "]");
                var multiInput = clone.find('.card-title .multichoice');
                multiInput.attr("name", "is_multichoice[" + questionId + "]");

                //setup options for the clone with its unique id
                var options = clone.find('.card-body .options');
                var optionButton = clone.find('.card-body .addOption');
                var optionInput = options.find('input');
                options.attr("id", "option-" + questionId);
                optionButton.attr("id", "button-" + questionId);
                optionInput.attr("name", "options[" + questionId + "][]");
                clone.find('[data-toggle="tooltip"]').tooltip({
                    html: true
                });
            });

            $(questions).on('click', '.addOption', function(e) {
                e.preventDefault();
                var clickedBtnId = $(this).attr('id');
                var options = $('#option-' + clickedBtnId.replace('button-', ''));
                var optionRow = options.find('.hide');
                var clone = optionRow.clone();
                clone.removeClass('hide');
                options.append(clone);
                attachRemoveListener(clone.find('.remove-option-button'));
            });


            function attachRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
            }
        });
    </script>
@endsection
