@if($form)
    {!! Form::open(['url' => 'admin/forms/delete/'.$form->id]) !!}

    <p>You are about to delete the form <strong>{{ $form->title }}. This will also delete all related questions and answers!</strong> This is not reversible. If you would like to preserve the content while preventing users from accessing the form, you can use the active setting instead to hide the form.</p>
    <p>Are you sure you want to delete <strong>{{ $form->title }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Form', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid form selected.
@endif