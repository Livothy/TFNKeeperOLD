@if ($action == 'edit')
    @if ($number && $number <= $form->latestSubmissionNumber())
        {!! Form::open(['url' => 'forms/send/' . $form->id]) !!}
        <h2> Edit Form Submission {{ $number }}</h2>
        Questions marked with * are mandatory.
        <div class="border rounded p-4">

            @foreach ($form->questions as $key => $question)
                <h5>{{ $question->question }} {{ $question->is_mandatory ? '*' : '' }}</h5>
                @if ($question->options->count() > 0)
                    @foreach ($question->options as $option)
                        <div class="form-group mb-0">
                            @php
                                $answer =
                                    $question->answers
                                        ->where('user_id', $user->id)
                                        ->where('submission_number', $number)
                                        ->where('option_id', $option->id)
                                        ->first()?->option_id == $option->id;
                            @endphp
                            @if ($question->is_multichoice)
                                <label>{{ Form::checkbox($question->id . '[]', $option->id, $answer ? true : false, ['class' => 'mr-1']) }} {{ $option->option }}</label>
                            @else
                                <label>{{ Form::radio($question->id, $option->id, $answer ? true : false, ['class' => 'mr-1']) }} {{ $option->option }}</label>
                            @endif
                        </div>
                    @endforeach
                @else
                    {!! Form::textarea(
                        $question->id,
                        $number
                            ? $question->answers->where('user_id', $user->id)->where('submission_number', $number)->first()?->answer
                            : null,
                        ['class' => 'form-control'],
                    ) !!}
                @endif
            @endforeach
            {!! Form::hidden('action', 'edit') !!}
            {!! Form::hidden('submission_number', $number) !!}

        </div>
        <div class="text-right mt-2">
            @if ($user)
                {!! Form::submit('Edit', ['class' => 'btn btn-success']) !!}
            @else
                You must be logged in to send the form.
            @endif
        </div>
        {!! Form::close() !!}
    @else
        <h2>Edit Form Submissions </h2>
        @foreach ($form->userAnswers($user) as $submission => $answers)
            <div class="row">
                <div class="col-8"> Submission {{ $submission }} </div>
                <div class="col-4"> <a class="btn btn-primary btn-sm float-right" href="?action=edit&number={{ $submission }}">Edit</a> </div>
            </div>
            <hr>
        @endforeach
    @endif
@else
    {!! Form::open(['url' => 'forms/send/' . $form->id]) !!}
    <h2> Submit Form </h2>
    Questions marked with * are mandatory.

    <div class="border rounded p-4">

        @foreach ($form->questions as $key => $question)
            <h5>{{ $question->question }} {{ $question->is_mandatory ? '*' : '' }}</h5>
            @if ($question->options->count() > 0)
                @foreach ($question->options as $option)
                    <div class="form-group mb-0">
                        @if ($question->is_multichoice)
                            <label>{{ Form::checkbox($question->id . '[]', $option->id, false, ['class' => 'mr-1']) }} {{ $option->option }}</label>
                        @else
                            <label>{{ Form::radio($question->id, $option->id, $loop->index == 0 ? true : false, ['class' => 'mr-1']) }} {{ $option->option }}</label>
                        @endif
                    </div>
                @endforeach
            @else
                {!! Form::textarea($question->id, null, ['class' => 'form-control']) !!}
            @endif
        @endforeach
        {!! Form::hidden('action', 'submit') !!}

    </div>
    <div class="text-right mt-2">
        @if ($user)
            {!! Form::submit('Submit', ['class' => 'btn btn-success']) !!}
        @else
            You must be logged in to send the form.
        @endif
    </div>
    {!! Form::close() !!}
@endif
