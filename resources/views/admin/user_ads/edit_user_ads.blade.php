@extends('admin.layout')

@section('admin-title')
    Edit User Ad
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'User Ads' => 'admin/user_ads', 'Edit Ad' => 'admin/user_ads/edit/' . $user_ads->id]) !!}

    <h1>
        Edit User Ad
        {{-- <a href="#" class="btn btn-danger float-right delete-user-ads-button">Delete Ad</a> --}}
    </h1>

    <div class="alert alert-info">
        You can edit and delete user ads here. Deleting an ad is not reversible.
    </div>

    {!! Form::open(['url' => 'admin/user_ads/edit/' . $user_ads->id, 'files' => true]) !!}

    <div class="form-group">
        {!! Form::label('text', 'Ad Content') !!}
        {!! Form::text('text', $user_ads->text, ['class' => 'w-100']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection
@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-user-ads-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/user_ads/delete') }}/{{ $user_ads->id }}", 'Delete Ad');
            });
        });
    </script>
@endsection
