<div class="card mb-3">
    @include('forms._site_form_header')
    <div class="card-body">
        <div class="parsed-text">
            {!! $form->parsed_description ?? '<i>This form has no description.</i>' !!}
        </div>
        <hr>
        @if ($page)
            <!---Not logged in or is not trying to edit or submit it--->
            @if ($form->is_public)
                @include('forms._site_form_results')
            @else
                <i>Answers are hidden.</i>
            @endif
            @if ($user)
                <i>
                    @if ($form->canSubmit($user) === true)
                        You can submit this form below.
                    @elseif($form->canSubmit($user) === 'closed')
                        This form is no longer open to submit to.
                    @else
                        You have already submitted this form.
                    @endif
                </i>
                @if ($form->answers->where('user_id', $user->id)->count() > 0)
                    <a class="btn btn-primary float-right mt-5" href="/forms/send/{{ $form->id }}?action=edit">Your Answers</a>
                @endif
                @if ($form->canSubmit($user) === true)
                    <a class="btn btn-primary float-right mt-5" href="/forms/send/{{ $form->id }}?action=submit">Submit Form</a>
                @endif
            @endif
        @endif
    </div>
    <?php $commentCount = App\Models\Comment\Comment::where('commentable_type', 'App\Models\Forms\SiteForm')
        ->where('commentable_id', $form->id)
        ->count(); ?>
    @if (!$page)
        <hr>
        <div class="text-right mb-2 mr-2">
            <a class="btn" href="{{ $form->url }}"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : '' }}</a>
        </div>
    @else
        <div class="text-right mb-2 mr-2">
            <span class="btn"><i class="fas fa-comment"></i> {{ $commentCount }} Comment{{ $commentCount != 1 ? 's' : '' }}</span>
        </div>
    @endif
</div>
