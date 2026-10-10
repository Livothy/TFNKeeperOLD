@extends('layouts.app')

@section('title')
    {{ $form->title }}
@endsection

@section('content')
    {!! breadcrumbs(['Site Forms & Polls' => 'forms', $form->title => $form->url]) !!}

    <div class="card mb-3">
        @include('forms._site_form_header')
        <div class="card-body">
            <div class="parsed-text">
                {!! $form->parsed_description ?? '<i>This form has no description.</i>' !!}
            </div>
            <hr>
            @if ($form->is_editable || ($action == 'submit' && $form->canSubmit($user) === true))
                @include('forms._site_form_edit')
            @else
                @include('forms._site_form_view')
            @endif
        </div>
    </div>
@endsection
