@extends('layout')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

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

        @hasSection('footer')
            @include('footer')
        @endif
    </div>
@stop

