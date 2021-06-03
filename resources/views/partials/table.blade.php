@extends('page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buttons.dataTables.min.css') }}">

    <style>
        .dataTables_wrapper .dataTables_processing {
            background-color: #D6DBDF;
        }

        table.dataTable td {
            font-size: 1em;
        }

        .table td, .table th {
            padding: .30rem;
        }

    </style>

    @yield('table_css')
@stop

@section('js')
    <script src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>

    @yield('table_js')
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive">
                    <table id="list" class="table table-bordered table-striped dataTable dtr-inline" width="100%"
                           cellspacing="0">
                        <thead>
                        <tr>
                            @foreach($header as $v)
                                <th>{{ $v }}</th>
                            @endforeach
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach($modal as $value)
        <div class="modal fade" id="modal-{{ $value['id'] }}">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="modal-title" class="modal-title">{{ $value['title'] }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="modal-id">
                        @foreach($value['list'] as $v)
                            @php $t = ''; @endphp
                            @if(isset($v['disabled']) && $v['disabled'])
                                @php $t .= 'disabled readonly'; @endphp
                            @endif
                            @if(isset($v['placeholder']))
                                @php $t .= 'placeholder=' . $v['placeholder'] ; @endphp
                            @endif
                            <div class="input-group mb-1">
                                @if($v['type'] == 'select')
                                    <select class="custom-select" id="modal-{{ $value['id'] }}-type">
                                        @foreach($v['value'] as $a)
                                            <option value="{{ $a->code }}">{{ $a->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <label class="input-group-text"
                                               for="inputGroupSelect02">{{ $v['name'] }}</label>
                                    </div>
                                @elseif($v['type'] == 'checkbox')
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <input type="checkbox" id="modal-{{ $value['id'] }}-{{ $v['id'] }}">
                                        </div>
                                    </div>
                                    <input type="text" class="form-control" value="{{ $v['name'] }}" {{ $t }}>
                                @else
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">{{ $v['name'] }}</span>
                                    </div>
                                    @if($v['type'] == 'text')
                                        <input type="text" class="form-control"
                                               id="modal-{{ $value['id'] }}-{{ $v['id'] }}" {{ $t }}>
                                    @endif
                                    @if($v['type'] == 'number')
                                        <input type="number" class="form-control"
                                               id="modal-{{ $value['id'] }}-{{ $v['id'] }}" {{ $t }}>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
                        <button type="button" id="modal-{{ $value['id'] }}-btn"
                                class="btn btn-primary">{{ $value['btn'] }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop
