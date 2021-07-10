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
            Highcharts.getJSON(url.replace(':id', $('#tag').val()).replace(':year', $('#year').val()), function (data) {
                toastr.success('成功')

                groupingUnits = [[
                    'day',
                    [1]
                ], [
                    'week',
                    [1]
                ], [
                    'month',
                    [1, 3, 6]
                ], [
                    'year',
                    null
                ]]

                Highcharts.seriesTypes.column.prototype.pointAttribs = (function (func) {
                    return function (point, state) {
                        var attribs = func.apply(this, arguments);

                        var candleSeries = this.chart.series[0]; // Probably you'll need to change the index
                        var candlePoint = candleSeries.points.filter(function (p) {
                            return p.index == point.index;
                        })[0];

                        var color = (candlePoint.open < candlePoint.close) ? 'red' : 'green'; // Replace with your colors
                        attribs.fill = state == 'hover' ? Highcharts.Color(color).brighten(0.3).get() : color;

                        return attribs;
                    };
                }(Highcharts.seriesTypes.column.prototype.pointAttribs));

                Highcharts.stockChart('stock', {
                    rangeSelector: {
                        buttons: [{
                            type: 'month',
                            count: 1,
                            text: '1m'
                        }, {
                            type: 'month',
                            count: 3,
                            text: '3m'
                        }, {
                            type: 'month',
                            count: 6,
                            text: '6m'
                        }, {
                            type: 'year',
                            count: 1,
                            text: '1y'
                        }, {
                            type: 'year',
                            count: 2,
                            text: '2y'
                        }, {
                            type: 'year',
                            count: 3,
                            text: '3y'
                        }, {
                            type: 'all',
                            count: 1,
                            text: 'All'
                        }],
                        selected: 1,
                        inputEnabled: false
                    },
                    chart: {
                        height: '550'
                    },

                    yAxis: [{
                        crosshair: true,
                        labels: {
                            align: 'right',
                            x: -3
                        },
                        height: '70%',
                        lineWidth: 2,
                        resize: {
                            enabled: true
                        }
                    }, {
                        labels: {
                            align: 'right',
                            x: -3
                        },
                        top: '65%',
                        height: '35%',
                        offset: 0,
                        lineWidth: 2
                    }],

                    xAxis: {
                        crosshair: true,
                        type: "datetime",
                        tickInterval: 24 * 3600 * 1000 * 60,
                        labels: {
                            formatter: function () {
                                return Highcharts.dateFormat('%Y-%m-%d', this.value);
                            }
                        }
                    },

                    tooltip: {
                        shape: 'square',
                        headerShape: 'callout',
                        borderWidth: 0,
                        shadow: true,
                        useHTML: true,
                        valueDecimals: 2,
                        xDateFormat: '%Y-%m-%d',
                        formatter: function () {
                            let html = '日期: ' + '<span style="color:#7dbbd2">' + Highcharts.dateFormat('%Y-%m-%d', this.x) + '</span>'

                            console.log(this.points)

                            $.each(this.points, function () {
                                switch (this.series.name) {
                                    case 'prices':
                                        let c = 'white'
                                        if (this.point.increase < 0) {
                                            c = 'green'
                                        } else if (this.point.increase > 0) {
                                            c = 'red'
                                        }

                                        html +=
                                            ' 開盤: ' + '<span style="color:#7dbbd2">' + this.point.open + '</span>' +
                                            ' 收盤: ' + '<span style="color:#7dbbd2">' + this.point.close + '</span>' +
                                            ' 最高: ' + '<span style="color:#7dbbd2">' + this.point.high + '</span>' +
                                            ' 最低: ' + '<span style="color:#7dbbd2">' + this.point.low + '</span>' +
                                            ' 漲幅: ' + '<span style="color:' + c + '">' + this.point.increase + '</span>'
                                        break
                                    case 'volume':
                                        value = this.y
                                        if (value > Math.pow(10, 8)) {
                                            value = (Math.round(value / Math.pow(10, 8) * 100) / 100) + '億'
                                        } else {
                                            value = (Math.round(value / 10000 * 100) / 100) + '萬'
                                        }

                                        html += ' 成交金額: ' + '<span style="color:#7dbbd2">' + value + '</span>'
                                        break
                                    case '5Ma':
                                        html += '<span style="color:#ff8c00"> ' + this.series.name + ': </span> ' + '<span style="color:#ff8c00">' + Math.round(this.y * 100) / 100 + '</span>'
                                        break
                                    case '10Ma':
                                        html += '<span style="color:#00ffff"> ' + this.series.name + ': </span> ' + '<span style="color:#00ffff">' + Math.round(this.y * 100) / 100 + '</span>'
                                        break
                                    case '20Ma':
                                        html += '<span style="color:#0a932f"> ' + this.series.name + ': </span> ' + '<span style="color:#0a932f">' + Math.round(this.y * 100) / 100 + '</span>'
                                        break
                                    case '60Ma':
                                        html += '<span style="color:#d4b40f"> ' + this.series.name + ': </span> ' + '<span style="color:#d4b40f">' + Math.round(this.y * 100) / 100 + '</span>'
                                        break
                                }
                            });

                            return html;
                        },
                        positioner: function (width, height, point) {
                            var chart = this.chart,
                                position;

                            if (point.isHeader) {
                                position = {
                                    x: Math.max(
                                        0,
                                        Math.min(
                                            point.plotX + chart.plotLeft - width / 2,
                                            chart.chartWidth - width - chart.marginRight
                                        )
                                    ),
                                    y: point.plotY
                                };
                            } else {
                                position = {
                                    x: point.series.chart.plotLeft,
                                    y: point.series.yAxis.top - chart.plotTop
                                };
                            }

                            return position;
                        }
                    },

                    navigator: {
                        enabled: false
                    },

                    exporting: {
                        enabled: false
                    },

                    series: [{
                        type: 'candlestick',
                        data: data.prices,
                        id: 'prices',
                        name: 'prices',
                        upColor: 'white',
                        upLineColor: 'white',
                        color: 'red',
                        lineColor: 'red',
                    }, {
                        type: 'column',
                        data: data.volume,
                        yAxis: 1,
                        id: 'volume',
                        name: 'volume',
                    }, {
                        type: 'sma',
                        id: '5Ma',
                        name: '5Ma',
                        linkedTo: 'prices',
                        zIndex: 1,
                        lineWidth: 0.5,
                        color: '#ff8c00',
                        params: {
                            period: 5
                        },
                        marker: {
                            enabled: false,
                            states: {
                                hover: {
                                    enabled: false,
                                }
                            }
                        }
                    }, {
                        type: 'sma',
                        id: '10Ma',
                        name: '10Ma',
                        linkedTo: 'prices',
                        zIndex: 1,
                        lineWidth: 0.5,
                        color: '#00ffff',
                        params: {
                            period: 10
                        },
                        marker: {
                            enabled: false,
                            states: {
                                hover: {
                                    enabled: false,
                                }
                            }
                        }
                    }, {
                        type: 'sma',
                        id: '20Ma',
                        name: '20Ma',
                        linkedTo: 'prices',
                        zIndex: 1,
                        lineWidth: 0.5,
                        color: '#0a932f',
                        params: {
                            period: 20
                        },
                        marker: {
                            enabled: false,
                            states: {
                                hover: {
                                    enabled: false,
                                }
                            }

                        }
                    }, {
                        type: 'sma',
                        id: '60Ma',
                        name: '60Ma',
                        linkedTo: 'prices',
                        zIndex: 1,
                        lineWidth: 0.5,
                        color: '#d4b40f',
                        params: {
                            period: 60
                        },
                        marker: {
                            enabled: false,
                            states: {
                                hover: {
                                    enabled: false,
                                }
                            }
                        }
                    }]
                });
            });
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
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">指數</span>
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
        </div>
    </div>
@stop
