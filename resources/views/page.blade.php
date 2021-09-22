@extends('layout')

@section('body')
    <div class="wrapper">

        @include('partials.navbar')
        @include('partials.left-sidebar')

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    @yield('content_header')
                </div>
            </div>

            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>

        @include('footer')
    </div>
@stop

