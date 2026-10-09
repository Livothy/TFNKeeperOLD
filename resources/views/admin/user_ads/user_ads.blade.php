@extends('admin.layout')

@section('admin-title')
    User Ads
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'User Ads' => 'admin/user_ads']) !!}

    <h1>User Ads</h1>
    <div class="alert alert-warning">
        You can edit and delete user ads here. <strong>Deleting an ad is not reversible.</strong>
    </div>

    @if (!count($user_ads))
        <p>No user ads found.</p>
    @else
        {!! $user_ads->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-5">
                        <div class="logs-table-cell">Text</div>
                    </div>
                    <div class="col-2">
                        <div class="logs-table-cell">Created At</div>
                    </div>
                    <div class="col-3">
                        <div class="logs-table-cell">Last Edited</div>
                    </div>
                    <div class="col">
                        <div class="logs-table-cell">Action</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($user_ads as $ad)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-5">
                                <div class="logs-table-cell">
                                    {!! $ad->text !!}
                                </div>
                            </div>
                            <div class="col-2 ">
                                <div class="logs-table-cell">{!! pretty_date($ad->created_at) !!}</div>
                            </div>
                            <div class="col-3">
                                <div class="logs-table-cell">{!! pretty_date($ad->updated_at) !!}</div>
                            </div>
                            <div class=" text-center">
                                <div class="logs-table-cell"><a href="{{ url('admin/user_ads/edit/' . $ad->id) }}" class="btn btn-primary py-0 px-2 w-100">Edit</a></div>
                                <div class="logs-table-cell"><a href="{{ url('admin/user_ads/delete/' . $ad->id) }}" class="btn btn-danger py-0 px-2 w-100 delete-user-ads-button" data-id="{{ $ad->id }}">Delete</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $user_ads->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $user_ads->total() }} result{{ $user_ads->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $('.delete-user-ads-button').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            loadModal("{{ url('admin/user_ads/delete') }}/" + id, 'Delete Ad');
        });
    </script>
@endsection
