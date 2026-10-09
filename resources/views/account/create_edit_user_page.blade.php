@extends('account.layout')

@section('account-title')
    {{ $userPage->id ? 'Edit' : 'Create' }} User Page
@endsection

@section('account-content')
    {!! breadcrumbs(['My Account' => Auth::user()->url, 'User Pages' => 'account/user-pages', ($userPage->id ? 'Edit' : 'Create') . ' User Page' => $userPage->id ? 'account/user-pages/edit/' . $userPage->id : 'account/user-pages/create']) !!}

    <h1>
        {{ $userPage->id ? 'Edit' : 'Create' }} User Page
        @if ($userPage->id && config('lorekeeper.user_pages.allow_deletion.enabled'))
            <a href="#" class="btn btn-danger float-right delete-user-page-button">Delete Page</a>
        @endif
        @if ($userPage->id)
            <a href="{{ $userPage->url }}" class="btn btn-primary float-right mr-md-2">View Page</a>
        @endif
    </h1>

    {!! Form::open(['url' => $userPage->id ? 'account/user-pages/edit/' . $userPage->id : 'account/user-pages/create']) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Page Title') !!}
                {!! Form::text('title', $userPage->title, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Key') !!} {!! add_help('This is a unique name used to form the URL of the page (i.e. site.com/user/username/page/<b>KEY</b>). Alphanumeric characters, dash, and underscore (no spaces) can be used.') !!}
                {!! Form::text('key', $userPage->key, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Page Content') !!}
        {!! Form::textarea('text', $userPage->text, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $userPage->id ? $userPage->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_visible', 'Is Viewable?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, other users will not be able to view this page even if they have the link to it.') !!}
            </div>
        </div>

        <div class="col-md">
            <div class="form-group">
                {!! Form::checkbox('show_on_profile', 1, $userPage->id ? $userPage->show_on_profile : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('show_on_profile', 'Show on Profile?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned on, a link to this page will display at the bottom of the User section on your profile\'s sidebar.') !!}
            </div>
        </div>

        <div class="col-md">
            <div class="form-group">
                {!! Form::checkbox('logged_in_only', 1, $userPage->id ? $userPage->logged_in_only : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('logged_in_only', 'Logged In Only?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned on, this page will only be accessible to users who are logged in.') !!}
            </div>
        </div>

        @if (config('lorekeeper.user_pages.allow_comments.enabled'))
            <div class="col-md">
                <div class="form-group">
                    {!! Form::checkbox('can_comment', 1, $userPage->id ? $userPage->can_comment : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('can_comment', 'Allow Comments?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned on, users will be able to comment on the page.') !!}
                </div>
            </div>
        @endif
    </div>

    <div class="text-right">
        {!! Form::submit($userPage->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    @if ($userPage->id && config('lorekeeper.user_pages.allow_deletion.enabled'))
        <script>
            $(document).ready(function() {
                $('.delete-user-page-button').on('click', function(e) {
                    e.preventDefault();
                    loadModal("{{ url('account/user-pages/delete') }}/{{ $userPage->id }}", 'Delete User Page');
                });
            });
        </script>
    @endif
@endsection
