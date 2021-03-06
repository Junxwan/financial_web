@extends('page')

@section('js')
    <script src="{{ asset('js/highstock.js') }}"></script>
    <script src="{{ asset('js/highstock/modules/data.js') }}"></script>
    <script src="{{ asset('js/highstock/themes/dark-unica.js') }}"></script>
    <script src="{{ asset('js/highstock/indicators.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        initK()

        $('#select-btn').click(function () {
            var url = "{{ route('population.exponent.k', ['id' => ':id', 'year' => ':year']) }}"
            newK('stock', url.replace(':id', $('#tag').val()).replace(':year', $('#year').val()))
        })

        $('#select-financial-btn').click(function () {
            var url = "{{ route('population.exponent.profit', ['id' => ':id', 'year' => ':year', 'quarterly' => ':quarterly']) }}"
            axios.get(url.replace(':id', $('#tag').val()).replace(':year', $('#year').val()).replace(':quarterly', $('#quarterly').val())).then(function (response) {
                $("#financial>tbody>tr").remove()
                response.data.forEach(function (v) {
                    let html =
                        "<tr>" +
                        "<td>" + v.code + "</td>" +
                        "<td>" + v.name + "</td>" +
                        tdAmountText(v.revenue, v.revenue_yoy) +
                        tdAmountText(v.gross, v.gross_yoy) +
                        tdAmountText(v.fee, v.fee_r) +
                        tdAmountText(v.profit, v.profit_r) +
                        tdAmountText(v.profit_pre, v.profit_pre_r) +
                        tdAmountText(v.profit_after, v.profit_r) +
                        tdAmountText(v.outside) +
                        "<td>" + v.eps + "</td>" +
                        "<td>" + v.non_eps + "</td>" +
                        "<td>" + (v.eps > 0 ? Math.round(((v.eps - v.non_eps) / v.eps) * 10000) / 100 : 0) + "%</td>" +
                        "</tr>"

                    $("#financial>tbody").append(html)
                })

                toastr.success('???????????????')
            }).catch(function (error) {
                toastr.error('??????????????????')
            })
        })

        $('#select-ks-btn').click(function () {
            var url = "{{ route('population.exponent.stock.k', ['id' => ':id', 'year' => ':year']) }}"
            axios.get(url.replace(':id', $('#tag').val()).replace(':year', $('#year').val())).then(function (response) {
                $("#stocks>div").remove()

                response.data.forEach(function (v) {
                    let id = 'stock_' + v.code
                    $('#stocks').append('<div id="' + id + '" class="row"></div>')

                    newStockChat(id, v)
                })

                toastr.success('??????K??????')
            }).catch(function (error) {
                toastr.error('???K????????????')
            })
        })

        function tdAmountText(value1, value2) {
            if (value2 === undefined) {
                return "<td>" + amountText(value1) + "</td>"
            }

            if (value2 > 0) {
                color = '#f33f7a'
            } else {
                color = '#2a9309'
            }

            return "<td>" + amountText(value1) + '</br><span style="color:' + color + '"> (' + value2 + '%)</span> ' + "</td>"
        }

        let urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('tag')) {
            $('#tag').val(urlParams.get('tag'))
            $('#select-btn').click()
        }
    </script>
@stop

@section('content')
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">??????</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">?????????</span>
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
                                <span class="input-group-text">???</span>
                            </div>
                            <select class="custom-select" id="year">
                                @for($i = 0; $i < 8;$i++)
                                    <option value="{{ $year - $i }}">{{ $year - $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                            </div>
                            <select class="custom-select" id="quarterly">
                                @for($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}">Q{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-btn">
                        K???
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-financial-btn">
                        ??????
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-ks-btn">
                        ???k
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">??????</h3>
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
                            <th scope="col">??????</th>
                            <th scope="col">??????</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">??????</h3>
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
                    <table id="financial" class="table table-dark text-center">
                        <thead>
                        <tr>
                            <th scope="col">??????</th>
                            <th scope="col">??????</th>
                            <th scope="col">?????????(yoy)</th>
                            <th scope="col">??????</th>
                            <th scope="col">??????</th>
                            <th scope="col">??????</th>
                            <th scope="col">??????</th>
                            <th scope="col">??????</th>
                            <th scope="col">??????</th>
                            <th scope="col">EPS</th>
                            <th scope="col">??????EPS</th>
                            <th scope="col">??????</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">K??????</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div id="stocks" class="card-body" style="display: block;">
            <div id="stock" class="row"></div>
        </div>
    </div>
@stop
