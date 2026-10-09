@extends('raids.layout')

@section('raids-title')
    {!! $boss->name !!}
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('raids.raid') . ' ' . __('raids.bosses')) => __('raids.raids') . '/' . __('raids.bosses'), $boss->name => __('raids.raids') . '/' . __('raids.boss') . '/' . $boss->id]) !!}

    <div class="row no-gutters justify-content-center">
        <div class="col-md-11">
            @include('raids._raid_boss_entry', ['boss' => $boss])
        </div>
    </div>
@endsection
