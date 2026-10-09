<li class="list-group-item">
    <strong>{!! $ad->user->displayName !!}:</strong> {!! $ad->text !!} {!! $ad->created_at ? pretty_date($ad->created_at) : '' !!}
    @if (Auth::check() && Auth::user()->hasPower('manage_user_ads'))
        <a class="btn btn-sm btn-primary ml-2" href="{{ url('admin/user_ads/edit/' . $ad->id) }}">
            Edit
        </a>
    @endif
    @if (Auth::check() && (Auth::user()->hasPower('manage_user_ads') || Auth::user()->id == $ad->user_id))
        <button type="button" class="btn btn-danger btn-sm delete-user-ad-button" data-id="{{ $ad->id }}">Delete</button>
    @endif
</li>

@section('scripts')
    @parent
    <script>
        $('.delete-user-ad-button').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            loadModal("{{ url('user_ads/delete') }}/" + id, 'Delete Ad');
        });
    </script>
@endsection
