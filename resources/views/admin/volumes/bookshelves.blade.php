@extends('admin.layout')

@section('admin-title')
    {{ ucfirst(__('volumes.bookshelves')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', ucfirst(__('volumes.bookshelves')) => 'admin/data/bookshelves']) !!}

    <h1>Bookshelves</h1>

    <p>This is a list of {{ __('volumes.bookshelves') }} in the game that can hold {{ __('volumes.books') }}.</p>
    <p>
    <div class="text-right form-group">
        <a class="btn btn-success create-bookshelf" href="#">Create Bookshelf</a>
        <a class="btn btn-primary" href="{{ url('admin/data/volumes') }}"> {{ ucfirst(__('volumes.library')) }} Home</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($bookshelves))
        <p>No {{ __('volumes.bookshelves') }} found.</p>
    @else
        {!! $bookshelves->render() !!}
        <table class="table table-sm category-table">
            <tbody id="sortable" class="sortable">
                @foreach ($bookshelves as $bookshelf)
                    <tr class="sort-item" data-id="{{ $bookshelf->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            {!! $bookshelf->displayName !!}
                        </td>
                        <td class="text-right">
                            <a class="btn btn-success edit-bookshelf" href="#" data-id="{{ $bookshelf->id }}">Edit {{ ucfirst(__('volumes.bookshelf')) }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/volumes/bookshelves/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
        {!! $bookshelves->render() !!}
    @endif

@endsection
@section('scripts')
    @parent
    <script>
        $('.create-bookshelf').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('/admin/data/volumes/bookshelves/create') }}", 'Create Bookshelf');
        });
        $('.edit-bookshelf').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('/admin/data/volumes/bookshelves/edit/') }}/" + $(this).data('id'), 'Edit Bookshelf');
        });
        $('.handle').on('click', function(e) {
            e.preventDefault();
        });
        $("#sortable").sortable({
            items: '.sort-item',
            handle: ".handle",
            placeholder: "sortable-placeholder",
            stop: function(event, ui) {
                $('#sortableOrder').val($(this).sortable("toArray", {
                    attribute: "data-id"
                }));
            },
            create: function() {
                $('#sortableOrder').val($(this).sortable("toArray", {
                    attribute: "data-id"
                }));
            }
        });
        $("#sortable").disableSelection();
    </script>
@endsection
