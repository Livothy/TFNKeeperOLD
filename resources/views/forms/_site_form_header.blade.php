<div class="card-header">
    <h2 class="card-title mb-0">
        @if(!$form->is_active || ($form->is_active && $form->is_timed && isset($form->start_at) && $form->start_at > Carbon\Carbon::now()))
        <i class="fas fa-eye-slash mr-1" data-toggle="tooltip" title="This form is hidden."></i>
        @endif
        {!! $form->displayName !!}
    </h2>
    <div class="h6">
        <b>@if($form->is_timed && isset($form->end_at) && $form->end_at < Carbon\Carbon::now())[CLOSED] @else [OPEN] @endif</b> 
        @if($form->is_timed) 
            Open @if($form->startDate) from {!! format_date($form->startDate) !!} @endif @if($form->endDate) until {!! format_date($form->endDate) !!} @endif
        @endif
    </div>
    <div class="h5">
        <span class="badge bg-warning border">
            @if($form->is_anonymous)
            This form is anonymous {!! add_help('Staff will be unable to see your name linked to your answers, however, the site owners may still access this information through the database.') !!}
            @else
            This form is not anonymous. {!! add_help('Staff will be able to easily see your name linked to your answers.') !!}
            @endif
        </span>
        <span class="badge bg-light border">
            @if($form->timeframe == 'lifetime')
            Once per user
            @else
            {{ $form->timeframe }} per user
            @endif
        </span>
        <span class="badge bg-light border">
            @if($form->is_editable)
            Editable
            @else
            Not editable
            @endif
        </span>
        <span class="badge bg-light border">
            @if($form->is_public)
            Public
            @else
            Not Public
            @endif
        </span>
        @if($form->rewards->count() > 0)
        <span class="badge bg-light border">
            Grants reward
        </span>
        @endif
    </div>
    <small>
        Posted {!! $form->post_at ? pretty_date($form->post_at) : pretty_date($form->created_at) !!} :: Last edited {!! pretty_date($form->updated_at) !!} by {!! $form->user->displayName !!}
    </small>
</div>