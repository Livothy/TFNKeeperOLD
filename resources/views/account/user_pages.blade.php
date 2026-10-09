@extends('account.layout')

@section('account-title')
    User Pages
@endsection

@section('account-content')
    {!! breadcrumbs(['My Account' => Auth::user()->url, 'User Pages' => 'account/user-pages']) !!}

    <h1>
        Your User Pages
    </h1>
    <p>
        Here you may create your own personal pages on the site for uses such as written wishlists, on-site commission pricesheets, personal character information, and more. Content of user pages should comply with the website's <a
            href="{{ url('info/terms') }}">terms of service</a> - violations of TOS may be removed by staff without warning.<br>
        @if (Auth::user()->isStaff && config('lorekeeper.user_pages.user_page_limit') != config('lorekeeper.user_pages.staff_page_limit'))
            <b>As a staff member</b>, you may have a maximum of <b>{{ config('lorekeeper.user_pages.staff_page_limit') }} {{ config('lorekeeper.user_pages.staff_page_limit') == 1 ? 'page' : 'pages' }}</b> at any given time.
        @else
            You may have a maximum of <b>{{ config('lorekeeper.user_pages.user_page_limit') }} {{ config('lorekeeper.user_pages.user_page_limit') == 1 ? 'page' : 'pages' }}</b> at any given time.
        @endif
    </p>

    <div class="text-right mb-2">
        @if (Auth::user()->pages->count() < config('lorekeeper.user_pages.user_page_limit') || (Auth::user()->isStaff && Auth::user()->pages->count() < config('lorekeeper.user_pages.staff_page_limit')))
            <a href="{{ url('account/user-pages/create') }}" class="btn btn-primary">
                <i class="fas fa-plus" aria-hidden="true"></i>
                Create Page
            </a>
        @else
            <span data-toggle="tooltip" title="You have reached the maximum number of pages a user is allowed to have at one time.">
                <a href="#" class="btn btn-primary disabled">
                    <i class="fas fa-times" aria-hidden="true"></i>
                    Page Limit Reached
                </a>
            </span>
        @endif
    </div>

    <div class="card p-3 mb-2">
        @if (!Auth::user()->pages->count())
            <p class="text-muted text-center mb-0">
                You currently have no user pages.
            </p>
        @else
            <div class="logs-table">
                <div class="logs-table-header">
                    <div class="row no-gutters">
                        <div class="row no-gutters align-items-center col">
                            <div class="col col-md-3">
                                <div class="logs-table-cell">Page</div>
                            </div>
                            <div class="col col-md">
                                <div class="logs-table-cell">Key</div>
                            </div>
                            <div class="col col-md">
                                <div class="logs-table-cell">Settings</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="logs-table-cell">URL</div>
                            </div>
                            <div class="d-none d-md-block col-md">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="logs-table-body">
                    @foreach (Auth::user()->pages as $page)
                        <div class="logs-table-row">
                            <div class="row no-gutters align-items-center flex-wrap">
                                <div class="col col-md-3">
                                    <div class="logs-table-cell">
                                        {!! $page->displayName !!}
                                    </div>
                                </div>
                                <div class="col col-md">
                                    <div class="logs-table-cell">
                                        {{ $page->key }}
                                    </div>
                                </div>
                                <div class="col col-md">
                                    <div class="logs-table-cell">
                                        @if (!$page->is_visible)
                                            <i class="fas fa-eye-slash text-danger" data-toggle="tooltip" title="This page is currently hidden from other users."></i>
                                        @else
                                            <i class="fas fa-eye text-success" data-toggle="tooltip" title="This page is currently visible to other users."></i>
                                        @endif
                                        @if ($page->show_on_profile)
                                            <i class="fas fa-list" data-toggle="tooltip" title="This page is displayed on your user profile, linked in the sidebar."></i>
                                        @endif
                                        @if ($page->logged_in_only)
                                            <i class="fas fa-sign-in-alt text-secondary" data-toggle="tooltip" title="Only logged in users may view this page."></i>
                                        @endif
                                        @if ($page->can_comment)
                                            <i class="fas fa-comments" data-toggle="tooltip" title="Comments are currently toggled <b>on</b> for this page."></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="logs-table-cell">
                                        <span style="user-select: all;">
                                            {!! $page->url !!}
                                        </span>
                                    </div>
                                </div>
                                <div class="col text-right">
                                    <div class="logs-table-cell">
                                        <a href="{{ $page->editUrl }}" class="btn btn-primary btn-sm">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    @parent
@endsection
