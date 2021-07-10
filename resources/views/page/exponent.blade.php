@extends('page')

@section('js')
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script src="{{ asset('js/highstock.js') }}"></script>
    <script src="{{ asset('js/highstock/modules/data.js') }}"></script>
    <script src="{{ asset('js/highstock/themes/dark-unica.js') }}"></script>
    <script src="{{ asset('js/highstock/indicators.js') }}"></script>
    <script>
        $('#select-btn').click(function () {
            var url = "{{ route('exponent.tag', ['id' => ':id', 'year' => ':year']) }}"
            newK('stock', url.replace(':id', $('#tag').val()).replace(':year', $('#year').val()))
        })
    </script>
@stop

@section('content')
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">基本</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">指數名</span>
                            </div>
                            <select class="custom-select" id="tag">
                                @foreach($tags as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">年</span>
                            </div>
                            <select class="custom-select" id="year">
                                @for($i = 0; $i < 8;$i++)
                                    <option value="{{ $year - $i }}">{{ $year - $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="select-btn">
                        查
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">指數</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div id="stock" class="row">
            </div>
            <div class="row">
                <div class="col-md-12">
                    <table id="stock_list" class="table table-dark">
                        <thead>
                        <tr>
                            <th scope="col">代碼</th>
                            <th scope="col">名稱</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
