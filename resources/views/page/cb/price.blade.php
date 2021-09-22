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

        $('#select-k-btn').click(function () {
            var url = "{{ route('cb.price', ['code' => ':code']) }}"
            newK('stock-chat', url.replace(':code', $('#code').val()))
        })

        $('#select-balance-btn').click(function () {
            var balance = []
            var price = []
            var name = ''

            let url = "{{ route('cb.stock.balance', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                response.data.data.forEach(function (v, index) {
                    balance.push([v.year + '-' + v.month, v.balance])
                })

                balance.reverse()

                name = response.data.name

                Highcharts.chart('balance-chat', {
                    title: {
                        text: response.data.name
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: {
                        title: {
                            text: null
                        }
                    },
                    navigator: {
                        enabled: false
                    },

                    exporting: {
                        enabled: false
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        id: 'balance',
                        name: '餘額',
                        data: balance
                    }]
                });

                toastr.success('查餘額成功')

                url = "{{ route('cb.price.month', ['code' => ':code']) }}"
                axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                    response.data.forEach(function (v, index) {
                        price.push([v.year + '-' + v.month, v.close])
                    })

                    Highcharts.chart('balance-price-chat', {
                        title: {
                            text: name
                        },
                        xAxis: {
                            type: "category"
                        },
                        yAxis: [{}, {
                            opposite: true
                        }],
                        tooltip: {
                            shared: true
                        },
                        series: [{
                            name: '餘額',
                            type: 'column',
                            yAxis: 1,
                            data: balance,
                        }, {
                            name: '月收盤',
                            type: 'spline',
                            data: price,
                            color: '#a0821a'
                        }]
                    });

                    toastr.success('查月收盤成功')
                }).catch(function (error) {
                    console.log(error)
                    toastr.error('查無月收盤')
                    return false
                })

            }).catch(function (error) {
                console.log(error)
                toastr.error('查無餘額')
                return false
            })
        })

        let urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('code')) {
            $('#code').val(urlParams.get('code'))
            $('#select-k-btn').click()
        }
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
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">代碼</span>
                            </div>
                            <input type="text" class="form-control" id="code">
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-k-btn">
                        K線
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-balance-btn">
                        餘額
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">K</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div id="stock-chat" class="row">
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">餘額</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div id="balance-chat" class="row">
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">餘額/月收盤價</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div id="balance-price-chat" class="row">
            </div>
        </div>
    </div>
@stop
