@extends('page')

@section('css')
    <style>
        .dark-mode .input-group-text {
            color: #8d8585;
            font-weight: bold;
            background: #343a40;
        }

        .dark-mode .input-group-width {
            min-width: 100%;
        }

        .dark-mode .input-width {
            min-width: 70px;
        }

        .dark-mode .input-width-1 {
            color: #8d8585 !important;
            min-width: 50px;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('js/highstock.js') }}"></script>
    <script src="{{ asset('js/highstock/modules/data.js') }}"></script>
    <script src="{{ asset('js/highstock/modules/annotations.js') }}"></script>
    <script src="{{ asset('js/highstock/themes/dark-unica.js') }}"></script>
    <script src="{{ asset('js/highstock/indicators.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        $('#cb-price-volume-btn').click(function () {
            $("#cb-price-volume-ok>tbody>tr").remove()

            let url = "{{ route('observe.cb.code.price.volume', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                let close = []
                let volume = []

                response.data.forEach(function (v, index) {
                    let t = Date.parse(v['date'])

                    close.push([t, v['close']])

                    let color = '#514d4d'

                    if (v['ok']) {
                        color = 'red'

                        $("#cb-price-volume-ok>tbody").append("<tr>" +
                            "<td>" + v['date'] + "</td>" +
                            "<td>" + v['close'] + "</td>" +
                            "<td>" + spanColor(v['increase']) + "</td>" +
                            "<td>" + v['volume'] + "</td>" +
                            "<td>" + spanColor(v['weak_increase']) + "</td>" +
                            "<td>" + v['avg_5_volume'] + "</td>" +
                            "<td>" + v['avg_10_volume'] + "</td>" +
                            "<td>" + v['avg_5_close'] + "</td>" +
                            "<td>" + v['avg_10_close'] + "</td>" +
                            "</tr>")
                    }

                    volume.push({
                        x: t,
                        y: v['volume'],
                        color: color,
                    })
                })

                Highcharts.chart('cb-price-volume-chat', {
                    chart: {
                        zoomType: 'x',
                        resetZoomButton: {
                            position: {
                                x: 0,
                                y: -40
                            }
                        }
                    },
                    title: {
                        text: $('#code').val()
                    },
                    xAxis: {
                        type: "datetime",
                        labels: {
                            formatter: function () {
                                return Highcharts.dateFormat('%Y-%m-%d', this.value);
                            }
                        }
                    },
                    yAxis: [{
                        title: '收盤',
                        crosshair: {
                            width: 1,
                            color: '#6a33a4'
                        },
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 100,
                            zIndex: 1
                        }, {
                            color: '#785229',
                            width: 1,
                            value: 120,
                            zIndex: 1
                        }],
                    }, {
                        title: '成交量',
                        opposite: true
                    }],
                    tooltip: {
                        shared: true
                    },
                    series: [{
                        name: '成交量',
                        type: 'column',
                        yAxis: 1,
                        data: volume,
                        color: '#514d4d',
                        borderColor: '#514d4d',
                    }, {
                        name: '收盤',
                        type: 'line',
                        data: close,
                    }]
                });

                toastr.success('查價量成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查價量失敗')
            })
        })

        $('#cb-price-volume-date-btn').click(function () {
            $("#cb-price-volume-date>tbody>tr").remove()

            let url = "{{ route('observe.cb.price.volume') }}?date=" + $('#date').val()
            axios.get(url).then(function (response) {
                response.data.forEach(function (v, index) {
                    $("#cb-price-volume-date>tbody").append("<tr>" +
                        "<td>" + v['name'] + "</td>" +
                        "<td>" + v['code'] + "</td>" +
                        "<td>" + v['close'] + "</td>" +
                        "<td>" + spanColor(v['increase']) + "</td>" +
                        "<td>" + v['volume'] + "</td>" +
                        "<td>" + spanColor(v['weak_increase']) + "</td>" +
                        "<td>" + v['avg_5_volume'] + "</td>" +
                        "<td>" + v['avg_10_volume'] + "</td>" +
                        "<td>" + v['avg_5_close'] + "</td>" +
                        "<td>" + v['avg_10_close'] + "</td>" +
                        "</tr>")
                })

                toastr.success('查某日價量成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查某日價量失敗')
            })
        })

        function spanColor(value) {
            color = '#f4f6f3'

            if (value >= 2) {
                color = 'rgba(222,38,38,0.75)'
            }

            if (value < 0) {
                color = 'rgba(34,205,29,0.53)'
            }

            return ' <span style="color:' + color + '">' + value + '</span>'
        }
    </script>
@stop

@section('content')
    <div class="card card-default" id="base-title">
        <div class="card-header">
            <h3 class="card-title">CB價量</h3>
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
                                <span class="input-group-text">Code</span>
                            </div>
                            <input type="text" class="form-control" id="code">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="cb-price-volume-btn">
                        價量
                    </button>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">日期</span>
                            </div>
                            <input type="date" class="form-control" id="date">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="cb-price-volume-date-btn">
                        查日
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">價量</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div id="cb-price-volume-chat" class="row"></div>
            <div class="row">
                <div class="col-md-12 table-responsive p-0" style="height: 500px;">
                    <table id="cb-price-volume-ok" class="table table-dark table-head-fixed text-nowrap">
                        <thead>
                        <tr>
                            <th scope="col">日期</th>
                            <th scope="col">收盤</th>
                            <th scope="col">漲幅</th>
                            <th scope="col">成交量</th>
                            <th scope="col">週漲幅</th>
                            <th scope="col">5均量</th>
                            <th scope="col">10均量</th>
                            <th scope="col">5均價</th>
                            <th scope="col">10均價</th>
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
            <h3 class="card-title">某日CB價量</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-12 table-responsive p-0" style="height: 500px;">
                    <table id="cb-price-volume-date" class="table table-dark table-head-fixed text-nowrap">
                        <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Code</th>
                            <th scope="col">收盤</th>
                            <th scope="col">漲幅</th>
                            <th scope="col">成交量</th>
                            <th scope="col">週漲幅</th>
                            <th scope="col">5均量</th>
                            <th scope="col">10均量</th>
                            <th scope="col">5均價</th>
                            <th scope="col">10均價</th>
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
