<h2 class="text-center">{{ $bookshelf->id ? 'Edit' : 'Create' }} {{ ucfirst(__('volumes.bookshelf')) }}
</h2>

{!! Form::open([
    'url' => $bookshelf->id ? 'admin/data/volumes/bookshelves/edit/' . $bookshelf->id : 'admin/data/volumes/bookshelves/create',
    'files' => true,
]) !!}

<h3>Basic Information</h3>

<div class="row">
    <div class="col-md-5">
        <div class="form-group">
            {!! Form::label('Name') !!}
            {!! Form::text('name', $bookshelf->name, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-7">
        <div class="form-group">
            {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
            <div>{!! Form::file('image') !!}</div>
            <div class="text-muted">Recommended size: 200px x 200px</div>
            @if ($bookshelf->has_image)
                <div class="form-check">
                    {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                    {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                </div>
            @endif
        </div>
    </div>
</div>


<div class="form-group">
    {!! Form::label('Summary (Optional)') !!} {!! add_help('This is a short blurb that shows up on ' . __('volumes.bookshelf') . ' index. HTML cannot be used here.') !!}
    {!! Form::text('summary', $bookshelf->summary, ['class' => 'form-control', 'maxLength' => 250]) !!}
</div>


<div class="text-right">
    {!! Form::submit($bookshelf->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if ($bookshelf->id)
    <hr class="my-4 w-75" />
    <div class="card mb-3 p-4">
        <h3>{{ ucfirst(__('volumes.bookshelf')) }} {{ ucfirst(__('volumes.books')) }}</h3>
        <p>You can sort this {{ __('volumes.bookshelf') }}'s {{ __('volumes.books') }} here.</p>

        <div class="card-body">

            @if (count($bookshelf->books))
                <div id="sortable_2" class="sortable_2">
                    <table class="table table-sm type-table">
                        <tbody id="sortable_2" class="sortable_2">
                            @foreach ($bookshelf->books as $book)
                                <tr class="sort-item" data-id="{{ $book->id }}">
                                    <td>
                                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                                        {!! $book->displayName !!}
                                    </td>
                                    <td>
                                        {!! $book->summary !!}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ url('admin/data/volumes/edit/' . $book->id) }}" class="btn btn-primary">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mb-4">
                    {!! Form::open(['url' => 'admin/data/volumes/bookshelves/books/' . $bookshelf->id]) !!}
                    {!! Form::hidden('sort', '', ['id' => 'sortable_2Order']) !!}
                    {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
                    {!! Form::close() !!}
                </div>
            @else
                <div class="alert alert-info">No {{ __('volumes.books') }} found.</div>
            @endif
        </div>
    </div>
    <hr class="my-4 w-75" />
    <h3>Preview</h3>
    <div class="col-md-12">
        <div class="card mb-2 text-center">
            <div class="card-header d-flex flex-wrap no-gutters">
                <h1 class="col-12">
                    <img src="{{ $bookshelf->imageUrl }}" style="margin-right: 10px">{!! $bookshelf->displayName !!} <img src="{{ $bookshelf->imageUrl }}" style="margin-left: 10px; ">
                </h1>
                <div class="col-12 text-center">
                    @if ($bookshelf->summary)
                        {!! $bookshelf->summary !!}
                    @endif

                </div>
            </div>
        </div>
    </div>
@endif

<script>
    $(document).ready(function() {
        $('.handle').on('click', function(e) {
            e.preventDefault();
        });
        $("#sortable_2").sortable({
            items: '.sort-item',
            handle: ".handle",
            placeholder: "sortable_2-placeholder",
            stop: function(event, ui) {
                $('#sortable_2Order').val($(this).sortable("toArray", {
                    attribute: "data-id"
                }));
            },
            create: function() {
                $('#sortable_2Order').val($(this).sortable("toArray", {
                    attribute: "data-id"
                }));
            }
        });
        $("#sortable_2").disableSelection();
    });
</script>
