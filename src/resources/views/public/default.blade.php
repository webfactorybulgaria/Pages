@extends('pages::public.master')

@section('page')

    <div class="row">

        <div class="col-sm-4">
            @if($children)
            <ul class="nav nav-subpages">
                @foreach ($children as $child)
                @include('pages::public._listItem', array('child' => $child))
                @endforeach
            </ul>
            @endif
        </div>


        <div class="col-sm-8">
            {!! $page->body !!}
            @include('galleries::public._galleries', ['model' => $page])
        </div>

    </div>

@stop
