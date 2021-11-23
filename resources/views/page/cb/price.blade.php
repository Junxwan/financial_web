@extends('page')

@section('js')
    <script src="{{ asset('js/highstock.js') }}"></script>
    <script src="{{ asset('js/highstock/modules/data.js') }}"></script>
    <script src="{{ asset('js/highstock/modules/annotations.js') }}"></script>
    <script src="{{ asset('js/highstock/themes/dark-unica.js') }}"></script>
    <script src="{{ asset('js/highstock/indicators.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        initK()

        $('#select-cb-k-btn').click(function () {
            var url = "{{ route('cb.price', ['code' => ':code']) }}"
            newK('cb-chat', url.replace(':code', $('#code').val()), false, [], function (data) {
                let close = []
                let volume = []
                let increase = []

                data.price.forEach(function (v, i) {
                    close.push([v.x, v.close])
                    increase.push([v.x, v.increase])
                })

                data.volume.forEach(function (v, i) {
                    volume.push([v.x, v.y])
                })

                Highcharts.chart('stock-price-volume-chat', {
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
                        text: data.name + '(收盤/成交量)'
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
                        title: {
                            text: '收盤'
                        },
                    }, {
                        title: {
                            text: '成交量'
                        },
                        opposite: true,
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 100,
                            zIndex: 1
                        }],
                    }],
                    tooltip: {
                        shared: true,
                        xDateFormat: '%Y-%m-%d',
                    },
                    series: [{
                        name: '收盤',
                        type: 'line',
                        data: close,
                        color: '#af5661'
                    }, {
                        name: '成交量',
                        type: 'column',
                        data: volume,
                        color: '#2f99a3',
                        yAxis: 1,
                    }]
                });

                Highcharts.chart('stock-price-increase-chat', {
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
                        text: data.name + '(收盤/漲幅)'
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
                        title: {
                            text: '收盤'
                        },
                    }, {
                        title: {
                            text: '漲幅'
                        },
                        opposite: true,
                        max: 10,
                        min: -10,
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 5,
                            zIndex: 1
                        },{
                            color: '#703628',
                            width: 1,
                            value: 2,
                            zIndex: 1
                        }],
                    }],
                    tooltip: {
                        shared: true,
                        xDateFormat: '%Y-%m-%d',
                    },
                    series: [{
                        name: '漲幅',
                        type: 'column',
                        yAxis: 1,
                        data: increase,
                    }, {
                        name: '收盤',
                        type: 'line',
                        data: close,
                        color: '#af5661'
                    }]
                });
            })
        })

        $('#select-k-btn').click(function () {
            axios.get("{{ route('cb.price.conversion', ['code' => ':code']) }}".replace(':code', $('#code').val())).then(function (response) {
                var url = "{{ route('price.code', ['code' => ':code']) }}"
                newK('stock-chat', url.replace(':code', $('#code').val().slice(0, -1)), false, [{
                    color: '#a43efd',
                    width: 1,
                    value: response.data[response.data.length - 1].value,
                    zIndex: 1
                }])
            }).catch(function (error) {
                console.log(error)
            })
        })

        $('#select-balance-btn').click(function () {
            var balance = []
            var balanceRate = []
            var price = []
            var name = ''

            let url = "{{ route('cb.balance', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                response.data.data.forEach(function (v, index) {
                    balance.push([v.year + '-' + v.month, v.balance])
                    balanceRate.push([v.year + '-' + v.month, v.balance_rate])
                })

                balance.reverse()
                balanceRate.reverse()

                name = response.data.name

                Highcharts.chart('balance-chat', {
                    title: {
                        text: response.data.name
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: [{
                        title: {
                            text: '張'
                        },
                        min: 0,
                    }, {
                        title: {
                            text: '%'
                        },
                        opposite: true,
                        max: 100,
                        min: 0,
                    }],
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
                        name: '月底餘額',
                        type: 'line',
                        data: balance
                    }, {
                        id: 'balanceRate',
                        name: '月底餘額%',
                        yAxis: 1,
                        type: 'line',
                        data: balanceRate
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
                            color: '#514d4d',
                            borderColor: '#514d4d',
                        }, {
                            name: '可轉債月收盤',
                            type: 'spline',
                            data: price,
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

            url = "{{ route('cb.securitiesLendingRepay', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                let close = []
                let repay = []
                response.data.data.forEach(function (v, index) {
                    close.push([v.date, v.close])
                    repay.push([v.date, v.securities_lending_repay])
                })

                Highcharts.chart('securities-lending-repay-price-chat', {
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
                        text: response.data.name + '(融劵劵償)'
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: [{
                        title: {
                            text: '收盤'
                        },
                        crosshair: {
                            width: 1,
                            color: '#6a33a4'
                        }
                    }, {
                        title: {
                            text: '融劵劵償'
                        },
                        opposite: true
                    }],
                    tooltip: {
                        shared: true,
                    },
                    series: [{
                        name: '融劵劵償',
                        type: 'column',
                        yAxis: 1,
                        data: repay,
                        color: '#514d4d',
                        borderColor: '#514d4d',
                    }, {
                        name: '收盤',
                        type: 'line',
                        data: close,
                        marker: {
                            enabled: false
                        }
                    }]
                });

                toastr.success('查融劵劵償成功')

            }).catch(function (error) {
                console.log(error)
                toastr.error('查無融劵劵償')
                return false
            })
        })

        $('#select-premium-btn').click(function () {
            let url = "{{ route('cb.price.premium', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                let offClose = []
                let close = []
                let cbClose = []
                let premium = []
                let order = []

                response.data.data.forEach(function (v, index) {
                    offClose.push([v.date, v.off_price])
                    close.push([v.date, v.close])
                    cbClose.push([v.date, v.cb_close])
                    premium.push([v.date, v.premium])
                    order.push({
                        'cb': v.cb_close,
                        'premium': v.premium,
                        'date': v.date,
                    })
                })

                offClose.reverse()
                cbClose.reverse()
                close.reverse()
                premium.reverse()

                let labels = []
                let labelsClose = []
                let labelsCbClose = []
                response.data.conversion_prices.forEach(function (v, index) {
                    close.every(function (cV, cIndex) {
                        if (cV[0] >= v.date) {
                            labels.push({
                                point: {
                                    xAxis: 0,
                                    yAxis: 0,
                                    x: cIndex,
                                    y: v.value
                                },
                                text: v.value.toString()
                            })

                            labelsClose.push({
                                point: {
                                    xAxis: 0,
                                    yAxis: 1,
                                    x: cIndex,
                                    y: v.value
                                },
                                text: v.value.toString()
                            })

                            return false
                        }

                        return true
                    })

                    cbClose.every(function (cV, cIndex) {
                        if (cV[0] >= v.date) {
                            labelsCbClose.push({
                                point: {
                                    xAxis: 0,
                                    yAxis: 0,
                                    x: cIndex,
                                    y: cV[1]
                                },
                                text: v.value.toString()
                            })

                            return false
                        }

                        return true
                    })
                })

                order.sort(function (a, b) {
                    if (a.cb < b.cb) return -1;
                    if (a.cb > b.cb) return 1;
                    return 0;
                });

                dateOrder = []
                cbOrder = []
                premiumOrder = []
                order.forEach(function (v) {
                    cbOrder.push(v.cb)
                    premiumOrder.push(v.premium)
                    dateOrder.push(v.date)
                })

                Highcharts.chart('premium-chat', {
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
                        text: response.data.name + '(折溢)'
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: {
                        title: {
                            text: null
                        },
                        crosshair: {
                            width: 1,
                            color: '#6a33a4'
                        }
                    },
                    annotations: [{
                        labels: labelsClose
                    }],
                    series: [{
                        name: '折溢',
                        type: 'line',
                        data: premium,
                        color: '#2f99a3',
                        marker: {
                            enabled: false
                        }
                    }]
                });

                Highcharts.chart('premium-cb-chat', {
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
                        text: response.data.name + '(可轉債/折溢)'
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: [{
                        title: {
                            text: '可轉債'
                        },
                        tickInterval: 10,
                        crosshair: {
                            width: 1,
                            color: '#6a33a4'
                        }
                    }, {
                        title: {
                            text: '折溢'
                        },
                        tickInterval: 5,
                        opposite: true,
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 10,
                            zIndex: 1
                        }],
                    }],
                    annotations: [{
                        labels: labelsCbClose
                    }],
                    tooltip: {
                        shared: true,
                    },
                    series: [{
                        name: '折溢',
                        type: 'column',
                        yAxis: 1,
                        data: premium,
                        color: '#514d4d',
                        borderColor: '#514d4d',
                    }, {
                        name: '可轉債',
                        type: 'line',
                        data: cbClose,
                        marker: {
                            enabled: false
                        }
                    }]
                });

                Highcharts.chart('premium-stock-chat', {
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
                        text: response.data.name + '(股價/折溢)'
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: [{
                        title: {
                            text: '股價'
                        },
                        tickInterval: 10,
                        crosshair: {
                            width: 1,
                            color: '#6a33a4'
                        }
                    }, {
                        title: {
                            text: '折溢'
                        },
                        tickInterval: 5,
                        opposite: true,
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 10,
                            zIndex: 1
                        }, {
                            color: '#356b24',
                            width: 1,
                            value: -10,
                            zIndex: 1
                        }],
                    }],
                    annotations: [{
                        labels: labels
                    }],
                    tooltip: {
                        shared: true
                    },
                    series: [{
                        name: '折溢',
                        yAxis: 1,
                        type: 'column',
                        data: premium,
                        color: '#514d4d',
                        borderColor: '#514d4d',
                    }, {
                        name: '股價',
                        type: 'line',
                        data: close,
                        marker: {
                            enabled: false
                        }
                    }]
                });

                Highcharts.chart('premium-cb-order-chat', {
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
                        text: response.data.name + '(可轉債/折溢/排序)'
                    },
                    xAxis: [{
                        categories: dateOrder,
                    }],
                    yAxis: [{
                        title: {
                            text: '可轉債'
                        },
                        tickInterval: 10,
                        crosshair: {
                            width: 1,
                            color: '#6a33a4'
                        }
                    }, {
                        title: {
                            text: '折溢'
                        },
                        tickInterval: 5,
                        opposite: true,
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 10,
                            zIndex: 1
                        }],
                    }],
                    tooltip: {
                        shared: true
                    },
                    series: [{
                        name: '折溢',
                        yAxis: 1,
                        type: 'column',
                        data: premiumOrder,
                        color: '#514d4d',
                        borderColor: '#514d4d',
                    }, {
                        name: '可轉債',
                        type: 'line',
                        data: cbOrder,
                        marker: {
                            enabled: false
                        }
                    }]
                });

                Highcharts.chart('cb-price-chat', {
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
                        text: response.data.name + '(可轉債/個股)'
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: [{
                        title: {
                            text: '可轉債'
                        },
                    }, {
                        title: {
                            text: '個股'
                        },
                        opposite: true
                    }],
                    annotations: [{
                        labels: labels
                    }],
                    tooltip: {
                        shared: true
                    },
                    series: [{
                        name: '可轉債',
                        type: 'line',
                        data: cbClose,
                        color: '#af5661'
                    }, {
                        name: '個股',
                        type: 'line',
                        data: close,
                        color: '#2f99a3',
                        yAxis: 1,
                    }]
                });

                Highcharts.chart('premium-off-price-chat', {
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
                        text: response.data.name + '(可轉債/理論)'
                    },
                    xAxis: {
                        type: "category"
                    },
                    yAxis: [{
                        title: {
                            text: '可轉債'
                        },
                    }, {
                        title: {
                            text: '理論價'
                        },
                        opposite: true,
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 100,
                            zIndex: 1
                        }],
                    }],
                    annotations: [{
                        labels: labels
                    }],
                    tooltip: {
                        shared: true
                    },
                    series: [{
                        name: '可轉債',
                        type: 'line',
                        data: cbClose,
                        color: '#af5661'
                    }, {
                        name: '理論',
                        type: 'line',
                        data: offClose,
                        color: '#2f99a3',
                        yAxis: 1,
                    }]
                });

                toastr.success('查折溢價成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無折溢價')
            })
        })

        let urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('code')) {
            $('#code').val(urlParams.get('code'))
            $('#select-cb-k-btn').click()
            $('#select-k-btn').click()
            $('#select-premium-btn').click()
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
                            id="select-cb-k-btn">
                        K
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-k-btn">
                        個K
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-balance-btn">
                        餘額
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-premium-btn">
                        折溢
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
            <div id="cb-chat" class="row"></div>
            <div id="stock-chat" class="row"></div>
            <div id="stock-price-volume-chat" class="row"></div>
            <div id="stock-price-increase-chat" class="row"></div>
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
                <div id="balance-chat" class="row"></div>
                <div id="balance-price-chat" class="row"></div>
                <div id="securities-lending-repay-price-chat" class="row"></div>
            </div>
        </div>
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">折溢</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                            class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                            class="fas fa-remove"></i></button>
                </div>
            </div>
            <div class="card-body" style="display: block;">
                <div id="premium-chat" class="row">
                </div>
                <div id="premium-cb-chat" class="row">
                </div>
                <div id="premium-stock-chat" class="row">
                </div>
                <div id="premium-cb-order-chat" class="row">
                </div>
                <div id="cb-price-chat" class="row">
                </div>
                <div id="premium-off-price-chat" class="row">
                </div>
            </div>
        </div>
@stop
