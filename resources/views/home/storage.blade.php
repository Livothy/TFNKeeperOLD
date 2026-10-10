@extends('home.layout')

@section('home-title') {{ ucwords(__('safetydeposit.name')) }} @endsection

@section('home-content')
{!! breadcrumbs([ ucwords(__('safetydeposit.name'))  => __('safetydeposit.url') ]) !!}

<h1>
    {{ ucwords(__('safetydeposit.name')) }}
    <div class="float-right mb-3">
        <a class="btn btn-primary" href="{{ url('inventory') }}"><i class="fas fa-fw fa-box mr-2"></i> Back to Inventory</a>
    </div>
</h1>


<p>
    This is your {{ __('safetydeposit.name') }}.
    <br/>
    No actions can be performed on this page except to remove objects. You will need to remove the object from storage to act upon them.
</p>

<hr>

<p class="text-center">
    <strong>Items:</strong> {{ $storages->count() }} <span class="text-muted px-2"> | </span> <b>Qty:</b> {{ $sum }}
</p>

@if($storages->total() )

{!! Form::open(['url' => __('safetydeposit.url').'/withdraw']) !!}
    {!! $storages->render() !!}

    <div class="row my-4 no-gutters">
        <div class="row col-12 mt-1 pt-1">
            <div class="col-6 col-md-3 font-weight-bold">Object</div>
            <div class="col-6 col-md-3 font-weight-bold">Type</div>
            <div class="col-6 col-md-2 font-weight-bold">Count</div>
            <div class="col-6 col-md font-weight-bold text-right">Remove?</div>
        </div>
        @foreach($storages as $storage)
            <div class="card d-block col-12 mt-1 p-2">
                <div class="row align-items-center">
                    <div class="col-4 col-md-3">
                        @if($storage->first()->imageUrl)
                            <img src="{{ $storage->first()->imageUrl }}" alt="{{ $storage->first()->name }}" class="img-fluid p-2 border mr-2"/>
                        @endif
                        <strong>{!! $storage->first()->storable ? $storage->first()->displayName : 'Unknown' !!}</strong>
                    </div>
                    <div class="col-4 col-md-3">{!! class_basename($storage->first()->storable) !!}</div>
                    <div class="col-4 col-md-2">
                        {{ $storage->sum('count') }}
                        @if($storage->count())
                            <a data-toggle="collapse" href="#collapse{{ $storage->first()->id }}" role="button" aria-expanded="false" aria-controls="collapse{{ $storage->first()->id }}">
                                <span title="{{ $storage->count() }} stacks" data-toggle="tooltip" class="ml-2">
                                    <i class="fas fa-layer-group" alt="{{ $storage->count() }} stacks"></i> Open {{ $storage->count() }} stack{{ $storage->count() == 1 ? '' : 's' }}
                                </span>
                            </a>
                        @endif
                    </div>
                    <div class="col-12 col-md-2 ml-auto text-center">
                        @if($storage->sum('count') > 1)
                            {!! Form::button('One', ['class' => 'btn btn-primary btn-block ', 'type' => 'submit', 'name' => 'remove_one', 'value' => $storage->first()->id]) !!}
                        @endif
                        {!! Form::button('All', ['class' => 'btn btn-primary btn-block ', 'type' => 'submit', 'name' => 'remove_all', 'value' => $storage->first()->id]) !!}
                    </div>
                </div>

                @if($storage->count())
                    <div class="collapse row col-12 mt-2 no-gutters border p-2 align-items-stretch" id="collapse{{$storage->first()->id}}">
                        @foreach($storage as $stack)
                            <div class="d-none d-md-flex col-md-1 {{ !$loop->first ? 'ubt-top' : '' }} py-2 text-center">#{!! $stack->id !!}</div>
                            <div class="col-12 col-md-4 {{ !$loop->first ? 'ubt-top' : '' }} py-2"><strong>Data:</strong> {!! isset($stack->data['data']) ? $stack->data['data'] : 'N/A' !!}</div>
                            <div class="col-12 col-md-4 {{ !$loop->first ? 'ubt-top' : '' }} py-2"><strong>Notes:</strong> {!! isset($stack->data['notes']) ? $stack->data['notes'] : 'N/A' !!}</div>
                            <div class="col-4 col-md-1 {{ !$loop->first ? 'ubt-top' : '' }} py-2 text-right pr-2">x {!! $stack->count !!}</div>
                            <div class="col-8 col-md-2 text-center d-flex {{ !$loop->first ? 'ubt-top' : '' }} py-1 align-items-center">
                                {!! Form::number('remove['.$stack->id.']', null, ['class' => 'form-control mr-1', 'max' => $stack->count, 'min' => 0]) !!}
                                {!! Form::button('-1', ['class' => 'btn btn-primary', 'type' => 'submit', 'name' => 'remove_one', 'value' => $stack->id]) !!}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

        <div class="text-right">{!! Form::button('Withdraw Selected Items', ['class' => 'btn btn-primary px-3', 'type' => 'submit']) !!}</div>
{!! Form::close() !!}
@endif

{!! $storages->render() !!}

<div class="text-center mt-4 small text-muted">{{ $storages->total() }} result{{ $storages->total() == 1 ? '' : 's' }} found.</div>

@endsection


@section('scripts')
<script>

$( document ).ready(function() {
    $('.remove-one').on('click', function(e) {
        e.preventDefault();
        var $parent = $(this);
        loadModal("{{ url('safetydeposit/remove') }}/" + $parent.data('id'), $parent.data('name'));
    });
});

</script>
@endsection
