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

        .dark-mode .span-decline {
            color: #448d7b;
        }

        .dark-mode .span-growing {
            color: #c7556e;
        }

    </style>
@stop

@section('js')
    <script src="{{ asset('js/highcharts.js') }}"></script>
    <script src="{{ asset('js/highstock/themes/dark-unica.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        $('#code, #quarterly, #year_month').change(function () {
            var url = '{{ route("stock.search", ":code") }}';
            code = $('#code').val()

            axios.get(url.replace(':code', code)).then(function (response) {
                $('#name').val(response.data.name)
                $('#capital').val(Math.round(response.data.capital / 1000))
                $('#capital_text').html(amountText(response.data.capital))
                toastr.success('查訊成功')

                if (revenueMonth(code, $('#year_month').val().slice(0, 4), $('#year_month').val().slice(5, 7))) {
                    profit(code, $('#quarterly').val().slice(0, 4), $('#quarterly').val().slice(6, 7))
                    getEps(code)
                }

            }).catch(function (error) {
                console.log(error)
                toastr.error('查無資料')
            })
        })

        // 月營收
        function revenueMonth(code, year, month) {
            var url = '{{ route("revenue.recent", ['code' => ':code', 'year' => ':year', 'month' => ':month']) }}'
            url = url.replace(':code', code).replace(':year', year).replace(':month', month)

            return axios.get(url).then(function (response) {
                $('#year_month').val(
                    response.data[0].year + '-' + (new String(response.data[0].month)).padStart(2, '0')
                )

                $("#month-revenue>tbody>tr").remove()

                let revenues = []
                let total_increase = []
                yoys = []
                qoqs = []
                yearRevenueg = {}
                monthPrice = []

                var formatter = new Intl.NumberFormat()

                response.data.forEach(function (v, index) {
                    let html =
                        "<tr>" +
                        "<td>" + v.year + "-" + v.month + "</td>" +
                        "<td>" + amountText(v.value) + "</td>" +
                        "<td>" + spanColor(v.yoy) + "</td>" +
                        "<td>" + spanColor(v.qoq) + "</td>" +
                        "<td>" + formatter.format(v.value) + "</td>" +
                        "<td>" + formatter.format(v.total) + "</td>" +
                        "<td>" + formatter.format(v.y_total) + "</td>" +
                        "<td>" + spanColor(v.total_increase) + "</td>" +
                        "</tr>"

                    $("#month-revenue>tbody").append(html)

                    let t = Date.parse(v.year + '-' + v.month + '-02')
                    revenues.push([t, v.value])
                    total_increase.push([t, v.total_increase])
                    yoys.push([t, v.yoy])
                    qoqs.push([t, v.qoq])

                    if (yearRevenueg[v.year] === undefined) {
                        yearRevenueg[v.year] = {
                            1: 0,
                            2: 0,
                            3: 0,
                            4: 0,
                            5: 0,
                            6: 0,
                            7: 0,
                            8: 0,
                            9: 0,
                            10: 0,
                            11: 0,
                            12: 0
                        }
                    }

                    yearRevenueg[v.year][v.month] = v.value
                })

                revenues.reverse()
                total_increase.reverse()
                yoys.reverse()
                qoqs.reverse()

                seriesRevenues = []
                for (var [key, value] of Object.entries(yearRevenueg)) {
                    v = []
                    for (i = 1; i <= 12; i++) {
                        v.push(value[i])

                    }

                    seriesRevenues.push({
                        name: key + "年",
                        data: v,
                    })
                }

                axios.get('{{ route("price.month", ['code' => ':code']) }}'.replace(':code', $('#code').val())).then(function (response2) {
                    response2.data.forEach(function (v, index) {
                        let t = Date.parse(v.year + '-' + v.month + '-02')

                        revenues.forEach(function (r, i) {
                            if (r[0] === t) {
                                monthPrice.push([t, v.close])
                            }
                        })
                    })

                    monthPrice.reverse()

                    Highcharts.chart('month-revenue-bar', {
                        title: {
                            text: '月營收'
                        },
                        xAxis: {
                            type: 'datetime',
                            labels: {
                                formatter: function () {
                                    return Highcharts.dateFormat('%Y-%m', this.value);
                                }
                            }
                        },
                        yAxis: [{
                            title: {
                                text: '月營收'
                            },
                        }, {
                            title: {
                                text: '月收盤'
                            },
                            opposite: true
                        }],
                        tooltip: {
                            shared: true,
                            xDateFormat: '%Y-%m',
                            formatter: function () {
                                console.log(this)
                                return Highcharts.dateFormat('%Y-%m', this.x) + '<br>營收: ' + '<span style="color:#7dbbd2">' + amountText(this.y) + '</span>' +
                                    '<br>收盤: <span style="color:#7dbbd2">' + this.points[1].y
                            }
                        },
                        series: [{
                            name: '月營收',
                            type: 'column',
                            data: revenues,
                            color: '#45617d',
                            borderColor: '#45617d'
                        }, {
                            name: '月收盤',
                            yAxis: 1,
                            type: 'line',
                            data: monthPrice,
                        }]
                    });

                    Highcharts.chart('month-revenue-bar2', {
                        chart: {
                            type: 'column',
                        },
                        title: {
                            text: '月營收'
                        },
                        xAxis: {
                            categories: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
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
                        tooltip: {
                            formatter: function () {
                                return this.series.name + ': ' + '<span style="color:#7dbbd2">' + amountText(this.y) + '</span>'
                            }
                        },
                        series: seriesRevenues
                    });

                    Highcharts.chart('month-revenue-bar3', {
                        title: {
                            text: '累績年增/月收盤'
                        },
                        xAxis: {
                            type: 'datetime',
                            labels: {
                                formatter: function () {
                                    return Highcharts.dateFormat('%Y-%m', this.value);
                                }
                            }
                        },
                        yAxis: [{
                            title: {
                                text: '累績年增'
                            },
                        }, {
                            title: {
                                text: '月收盤'
                            },
                            opposite: true
                        }],
                        tooltip: {
                            shared: true,
                            xDateFormat: '%Y-%m',
                            formatter: function () {
                                console.log(this)
                                return Highcharts.dateFormat('%Y-%m', this.x) + '<br>累績年增: ' + '<span style="color:#7dbbd2">' + this.y + '</span>' +
                                    '<br>收盤: <span style="color:#7dbbd2">' + this.points[1].y
                            }
                        },
                        series: [{
                            name: '累積年增',
                            type: 'column',
                            data: total_increase,
                            color: '#45617d',
                            borderColor: '#45617d'
                        }, {
                            name: '月收盤',
                            yAxis: 1,
                            type: 'line',
                            data: monthPrice,
                        }]
                    });

                    Highcharts.chart('month-revenue-bar4', {
                        title: {
                            text: 'qoq/月收盤'
                        },
                        xAxis: {
                            type: 'datetime',
                            labels: {
                                formatter: function () {
                                    return Highcharts.dateFormat('%Y-%m', this.value);
                                }
                            }
                        },
                        yAxis: [{
                            title: {
                                text: 'qoq'
                            },
                        }, {
                            title: {
                                text: '月收盤'
                            },
                            opposite: true
                        }],
                        tooltip: {
                            shared: true,
                            xDateFormat: '%Y-%m',
                            formatter: function () {
                                console.log(this)
                                return Highcharts.dateFormat('%Y-%m', this.x) + '<br>累績年增: ' + '<span style="color:#7dbbd2">' + this.y + '</span>' +
                                    '<br>收盤: <span style="color:#7dbbd2">' + this.points[1].y
                            }
                        },
                        series: [{
                            name: 'qoq',
                            type: 'column',
                            data: qoqs,
                            color: '#45617d',
                            borderColor: '#45617d'
                        }, {
                            name: '月收盤',
                            yAxis: 1,
                            type: 'line',
                            data: monthPrice,
                        }]
                    });

                    toastr.success('月營收成功')
                })

                return true
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無月營收')
                return false
            })
        }

        function spanColor(value) {
            if (value > 0) {
                color = '#f33f7a'
            } else {
                color = '#2a9309'
            }

            return ' <span style="color:' + color + '">' + value + '</span>'
        }

        // 綜合損益表
        function profit(code, year, quarterly) {
            var url = '{{ route("profit.recent", ['code' => ':code', 'year' => ':year', 'quarterly' => ':quarterly']) }}'
            return axios.get(url.replace(':code', code).replace(':year', year).replace(':quarterly', quarterly)).then(function (response) {
                $("#quarterly-revenue>tbody>tr").remove()
                $("#profit-table>tbody>tr").remove()

                $('#quarterly').val(
                    response.data[0].year + '-Q' + response.data[0].quarterly
                )

                revenues = []
                gross = []
                fee = []
                grossSubFee = []
                profits = []
                profit_after = []
                datas = []
                let fees = []
                let research = []
                let depreciation = []
                let quarterlys = []
                let revenueqs = []
                let feeqs = []
                response.data.forEach(function (v, index) {
                    date = v.year + "-Q" + v.quarterly

                    if (index <= 24) {
                        let html =
                            "<tr>" +
                            "<td>" + date + "</td>" +
                            "<td>" + amountText(v.revenue) + "</td>" +
                            "<td>" + spanColor(v.revenue_yoy) + "</td>" +
                            "<td>" + v.revenue + "</td>" +
                            "</tr>"

                        $("#quarterly-revenue>tbody").append(html)

                        html =
                            "<tr>" +
                            "<td>" + date + "</td>" +
                            "<td>" + v.gross_ratio + "</td>" +
                            "<td>" + v.fee_ratio + "</td>" +
                            "<td>" + v.profit_ratio + "</td>" +
                            "<td>" + v.eps + "</td>" +
                            "<td>" + spanColor(v.revenue_yoy) + "</td>" +
                            "<td>" + spanColor(v.y_gross_ratio) + "</td>" +
                            "<td>" + spanColor(v.y_fee_ratio) + "</td>" +
                            "<td>" + spanColor(v.y_profit_ratio) + "</td>" +
                            "<td>" + spanColor(v.y_eps_ratio) + "</td>" +
                            "</tr>"

                        $("#profit-table>tbody").append(html)
                    }

                    let g = Math.round((v.gross / v.revenue) * 10000) / 100
                    let f = Math.round((v.fee / v.revenue) * 10000) / 100

                    quarterlys.push(date)
                    datas.push(date)
                    revenues.push([date, v.revenue])
                    gross.push([date, g])
                    fee.push([date, f])
                    fees.push([date, v.fee])
                    research.push([date, v.research])
                    depreciation.push([date, v.depreciation])
                    grossSubFee.push([date, Math.round((g - f) * 100) / 100])
                    profits.push([date, Math.round((v.profit / v.revenue) * 10000) / 100])
                    profit_after.push([date, Math.round((v.profit_after / v.revenue) * 10000) / 100])

                    revenueqs.push(v.revenue)
                    feeqs.push(v.fee)
                })

                revenues.reverse()
                gross.reverse()
                fee.reverse()
                grossSubFee.reverse()
                profits.reverse()
                profit_after.reverse()

                quarterlys.reverse()
                revenueqs.reverse()
                feeqs.reverse()
                fees.reverse()
                research.reverse()
                depreciation.reverse()

                Highcharts.chart('quarterly-revenue-bar', {
                    colors: ['#45617d'],
                    plotOptions: {
                        column: {
                            borderColor: '#45617d'
                        }
                    },
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '季營收'
                    },
                    xAxis: {
                        type: "category",
                    },
                    yAxis: {
                        title: {
                            text: null
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        formatter: function () {
                            return this.key + ': ' + '<span style="color:#7dbbd2">' + amountText(this.y) + '</span>'
                        }
                    },
                    series: [{
                        data: revenues
                    }]
                });

                q1 = []
                q2 = []
                q3 = []
                q4 = []
                q1e = []
                q2e = []
                q3e = []
                q4e = []
                q1g = []
                q2g = []
                q3g = []
                q4g = []
                q1p = []
                q2p = []
                q3p = []
                q4p = []
                q1o = []
                q2o = []
                q3o = []
                q4o = []
                q1f = []
                q2f = []
                q3f = []
                q4f = []
                q1fnrd = []
                q2fnrd = []
                q3fnrd = []
                q4fnrd = []
                q1r = []
                q2r = []
                q3r = []
                q4r = []
                q1d = []
                q2d = []
                q3d = []
                q4d = []
                q1c = []
                q2c = []
                q3c = []
                q4c = []
                q1gsf = []
                q2gsf = []
                q3gsf = []
                q4gsf = []
                q1fa = []
                q2fa = []
                q3fa = []
                q4fa = []
                years = []
                data = []
                response.data.forEach(function (v) {
                    if (!data.hasOwnProperty(v.year)) {
                        data[v.year] = []
                    }

                    let g = Math.round((v.gross / v.revenue) * 10000) / 100
                    let f = Math.round((v.fee / v.revenue) * 10000) / 100
                    v['gross_sub_fee'] = Math.round((g - f) * 100) / 100

                    data[v.year].push(v)
                })

                data.forEach(function (value, key) {
                    let b1, b2, b3, b4 = false
                    years.push(key)
                    value.forEach(function (v) {
                        switch (v.quarterly) {
                            case 1:
                                q1.push(v.revenue)
                                q1e.push(v.eps)
                                q1g.push(v.gross_ratio)
                                q1c.push(v.cost)
                                q1fa.push(v.fee)
                                q1p.push(v.profit_ratio)
                                q1o.push(v.outside)
                                q1f.push(v.fee_ratio)
                                q1gsf.push(v.gross_sub_fee)
                                q1r.push(v.research)
                                q1d.push(v.depreciation)
                                q1fnrd.push(v.fee - (v.research + v.depreciation))
                                b1 = true
                                break
                            case 2:
                                q2.push(v.revenue)
                                q2e.push(v.eps)
                                q2g.push(v.gross_ratio)
                                q2c.push(v.cost)
                                q2fa.push(v.fee)
                                q2p.push(v.profit_ratio)
                                q2o.push(v.outside)
                                q2f.push(v.fee_ratio)
                                q2gsf.push(v.gross_sub_fee)
                                q2r.push(v.research)
                                q2d.push(v.depreciation)
                                q2fnrd.push(v.fee - (v.research + v.depreciation))
                                b2 = true
                                break
                            case 3:
                                q3.push(v.revenue)
                                q3e.push(v.eps)
                                q3g.push(v.gross_ratio)
                                q3c.push(v.cost)
                                q3fa.push(v.fee)
                                q3p.push(v.profit_ratio)
                                q3o.push(v.outside)
                                q3f.push(v.fee_ratio)
                                q3gsf.push(v.gross_sub_fee)
                                q3r.push(v.research)
                                q3d.push(v.depreciation)
                                q3fnrd.push(v.fee - (v.research + v.depreciation))
                                b3 = true
                                break
                            case 4:
                                q4.push(v.revenue)
                                q4e.push(v.eps)
                                q4g.push(v.gross_ratio)
                                q4c.push(v.cost)
                                q4fa.push(v.fee)
                                q4p.push(v.profit_ratio)
                                q4o.push(v.outside)
                                q4f.push(v.fee_ratio)
                                q4gsf.push(v.gross_sub_fee)
                                q4r.push(v.research)
                                q4d.push(v.depreciation)
                                q4fnrd.push(v.fee - (v.research + v.depreciation))
                                b4 = true
                                break
                        }
                    })

                    if (!b1) {
                        q1.push(0)
                        q1e.push(0)
                        q1c.push(0)
                        q1g.push(0)
                        q1p.push(0)
                        q1o.push(0)
                        q1f.push(0)
                        q1r.push(0)
                        q1d.push(0)
                        q1gsf.push(0)
                        q1fnrd.push(0)
                        q1fa.push(0)
                    }

                    if (!b2) {
                        q2.push(0)
                        q2e.push(0)
                        q2c.push(0)
                        q2g.push(0)
                        q2p.push(0)
                        q2o.push(0)
                        q2f.push(0)
                        q2r.push(0)
                        q2d.push(0)
                        q2gsf.push(0)
                        q2fnrd.push(0)
                        q2fa.push(0)
                    }

                    if (!b3) {
                        q3.push(0)
                        q3e.push(0)
                        q3c.push(0)
                        q3g.push(0)
                        q3p.push(0)
                        q3o.push(0)
                        q3f.push(0)
                        q3r.push(0)
                        q3d.push(0)
                        q3gsf.push(0)
                        q3fnrd.push(0)
                        q3fa.push(0)
                    }

                    if (!b4) {
                        q4.push(0)
                        q4e.push(0)
                        q4c.push(0)
                        q4g.push(0)
                        q4p.push(0)
                        q4o.push(0)
                        q4f.push(0)
                        q4r.push(0)
                        q4d.push(0)
                        q4gsf.push(0)
                        q4fnrd.push(0)
                        q4fa.push(0)
                    }
                })

                Highcharts.chart('quarterly-revenue-bar2', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '季營收'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        formatter: function () {
                            return '<b>' + this.x + '</b><br/>' + this.series.name + ': ' + amountText(this.y)
                        }
                    },
                    plotOptions: {
                        column: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    return amountText(this.y)
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Q4',
                        data: q4
                    }, {
                        name: 'Q3',
                        data: q3
                    }, {
                        name: 'Q2',
                        data: q2
                    }, {
                        name: 'Q1',
                        data: q1
                    }]
                })

                Highcharts.chart('quarterly-revenue-bar3', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '季營收'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        formatter: function () {
                            return '<b>' + this.x + '</b><br/>' + this.series.name + ': ' + amountText(this.y)
                        }
                    },
                    series: [{
                        name: 'Q1',
                        data: q1
                    }, {
                        name: 'Q2',
                        data: q2
                    }, {
                        name: 'Q3',
                        data: q3
                    }, {
                        name: 'Q4',
                        data: q4
                    }]
                })

                // eps
                $('.form-group-quarterly-eps').each(function (index) {
                    v = response.data[index]
                    if (typeof v !== 'undefined') {
                        $(this).find('.input-date').html(v.year + "Q" + v.quarterly)
                        $(this).find('.input-value').val(Math.round(v.eps * 100) / 100)
                    }
                })

                // 總結
                $('.form-group-total').each(function () {
                    v = response.data[$(this).data('index')]

                    if (typeof v !== 'undefined') {
                        $(this).find('span').html(v.year + "Q" + v.quarterly)
                        $(this).find('input').each(function () {
                            name = $(this).data('name')

                            switch (name) {
                                case 'year':
                                    value = (v.year - 2000) + "Q" + v.quarterly
                                    break
                                case 'eps':
                                    value = Math.round(v[name] * 100) / 100
                                    break
                                case 'non_eps':
                                    if (v.profit < 0 && v.outside > 0) {
                                        value = v.eps
                                    } else {
                                        value = Math.round((v.eps * Math.round((v.outside / v.profit_main) * 10000) / 100)) / 100
                                    }
                                    break
                                case 'this':
                                    if (v.profit < 0) {
                                        value = 0
                                    } else if (v.eps > 0) {
                                        value = (1 - Math.round(((Math.round((v.eps * Math.round((v.outside / v.profit_main) * 10000) / 100)) / 100) / v.eps) * 100) / 100) * 100
                                    } else {
                                        value = 0
                                    }
                                    break;
                                default:
                                    value = amountText(Math.round(v[$(this).data('name')]))
                            }

                            $(this).val(value)
                        })
                    }
                })

                Highcharts.chart('profits-chat', {
                    title: {
                        text: '經營'
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
                        id: 'gross',
                        name: '毛利',
                        data: gross
                    }, {
                        id: 'profit',
                        name: '利益',
                        data: profits
                    }, {
                        id: 'profit_after',
                        name: '稅後',
                        data: profit_after,
                    }]
                });

                Highcharts.chart('eps-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: 'EPS'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1e
                    }, {
                        name: 'Q2',
                        data: q2e
                    }, {
                        name: 'Q3',
                        data: q3e
                    }, {
                        name: 'Q4',
                        data: q4e
                    }]
                });

                Highcharts.chart('cost-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '成本'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1c
                    }, {
                        name: 'Q2',
                        data: q2c
                    }, {
                        name: 'Q3',
                        data: q3c
                    }, {
                        name: 'Q4',
                        data: q4c
                    }]
                });

                Highcharts.chart('gross-fee-chat', {
                    title: {
                        text: '毛利/費用'
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
                        id: 'gross',
                        name: '毛利',
                        data: gross
                    }, {
                        id: 'fee',
                        name: '費用',
                        data: fee
                    }]
                });

                Highcharts.chart('gross-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '毛利'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1g
                    }, {
                        name: 'Q2',
                        data: q2g
                    }, {
                        name: 'Q3',
                        data: q3g
                    }, {
                        name: 'Q4',
                        data: q4g
                    }]
                });

                Highcharts.chart('fee-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '費用'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1f
                    }, {
                        name: 'Q2',
                        data: q2f
                    }, {
                        name: 'Q3',
                        data: q3f
                    }, {
                        name: 'Q4',
                        data: q4f
                    }]
                });

                Highcharts.chart('fee-amount-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '費用(金額)'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1fa
                    }, {
                        name: 'Q2',
                        data: q2fa
                    }, {
                        name: 'Q3',
                        data: q3fa
                    }, {
                        name: 'Q4',
                        data: q4fa
                    }]
                });

                Highcharts.chart('fee-detail-chat', {
                    title: {
                        text: '費用(結構)'
                    },
                    xAxis: {
                        type: "category"
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: '費用',
                        data: fees
                    }, {
                        name: '研發',
                        data: research
                    }, {
                        name: '折舊',
                        data: depreciation
                    }]
                });

                Highcharts.chart('research-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '研發'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1r
                    }, {
                        name: 'Q2',
                        data: q2r
                    }, {
                        name: 'Q3',
                        data: q3r
                    }, {
                        name: 'Q4',
                        data: q4r
                    }]
                });

                Highcharts.chart('depreciation-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '折舊'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1d
                    }, {
                        name: 'Q2',
                        data: q2d
                    }, {
                        name: 'Q3',
                        data: q3d
                    }, {
                        name: 'Q4',
                        data: q4d
                    }]
                });

                Highcharts.chart('fee-n-research-depreciation-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '費用(非研發跟折舊)'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1fnrd
                    }, {
                        name: 'Q2',
                        data: q2fnrd
                    }, {
                        name: 'Q3',
                        data: q3fnrd
                    }, {
                        name: 'Q4',
                        data: q4fnrd
                    }]
                });

                Highcharts.chart('profit-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '利益'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1p
                    }, {
                        name: 'Q2',
                        data: q2p
                    }, {
                        name: 'Q3',
                        data: q3p
                    }, {
                        name: 'Q4',
                        data: q4p
                    }]
                });

                Highcharts.chart('revenues-fee-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '營收/費用'
                    },
                    xAxis: {
                        categories: quarterlys
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
                        name: '營收',
                        data: revenueqs
                    }, {
                        name: '費用',
                        data: feeqs
                    }]
                });

                Highcharts.chart('outside-chat', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '業外'
                    },
                    xAxis: {
                        categories: years
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        name: 'Q1',
                        data: q1o
                    }, {
                        name: 'Q2',
                        data: q2o
                    }, {
                        name: 'Q3',
                        data: q3o
                    }, {
                        name: 'Q4',
                        data: q4o
                    }]
                });

                $('.form-group-quarterly').each(function () {
                    v = response.data[$(this).data('index')]
                    if (typeof v !== 'undefined') {
                        name = $(this).data('name')
                        value = v[name]
                        rate = Math.round((value / v.revenue) * 10000) / 100

                        if (typeof datas[name] !== 'undefined') {
                            datas[name].push(rate)
                        }

                        if (!isNaN(value)) {
                            setInput(
                                $(this),
                                v.year + "Q" + v.quarterly,
                                rate,
                                value
                            )
                        }
                    }
                })

                toastr.success('查綜合損益表成功')
                return true
            }).catch(function (error) {
                console.log(error)
                toastr.error('查綜合損益表無資料')
                return false
            })
        }

        // 年eps
        function getEps(code) {
            var url = '{{ route("profit.eps", ['code' => ':code']) }}'
            return axios.get(url.replace(':code', code)).then(function (response) {
                $('.form-group-eps').each(function (index) {
                    v = response.data[index + 1]

                    if (v !== undefined && v.eps !== '') {
                        $(this).find('.input-date').html(v.year)
                        $(this).find('.input-value').val(Math.round(v.eps * 100) / 100)
                    }
                })

                eps = []
                epsq = []

                $("#eps-year>thead>tr>th").remove()
                $("#eps-year>tbody>tr>td").remove()
                $("#eps-year>thead>tr").append('<th scope="col">季別/年度</th>')

                response.data.forEach(function (v) {
                    $("#eps-year>thead>tr").append("<th>" + v.year + "</th>")
                    $("#eps-year>tbody>tr:nth-child(1)").append("<td>" + (v.q1 !== undefined ? v.q1 : '') + "</td>")
                    $("#eps-year>tbody>tr:nth-child(2)").append("<td>" + (v.q2 !== undefined ? v.q2 : '') + "</td>")
                    $("#eps-year>tbody>tr:nth-child(3)").append("<td>" + (v.q3 !== undefined ? v.q3 : '') + "</td>")
                    $("#eps-year>tbody>tr:nth-child(4)").append("<td>" + (v.q4 !== undefined ? v.q4 : '') + "</td>")

                    if (v.eps !== '') {
                        eps.push([v.year, v.eps])
                    }

                    if (v.q4 !== '') {
                        epsq.push([v.year + '-Q4', v.q4])
                    }

                    if (v.q3 !== '') {
                        epsq.push([v.year + '-Q3', v.q3])
                    }

                    if (v.q2 !== '') {
                        epsq.push([v.year + '-Q2', v.q2])
                    }

                    if (v.q1 !== '') {
                        epsq.push([v.year + '-Q1', v.q1])
                    }
                })

                eps.reverse()
                epsq.reverse()

                Highcharts.chart('quarterly-eps-bar', {
                    colors: ['#45617d'],
                    chart: {
                        type: 'column',
                        height: 200
                    },
                    plotOptions: {
                        column: {
                            borderColor: '#45617d'
                        }
                    },
                    title: {
                        text: 'EPS'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: null
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        formatter: function () {
                            return this.key + ': ' + this.y + '</span>'
                        }
                    },
                    series: [{
                        data: eps,
                    }]
                });

                pe(code)
                dividend(code, response.data)

                toastr.success('查EPS成功')
                hint()
                return true
            }).catch(function (error) {
                console.log(error)
                toastr.error('查EPS無資料')
                return false
            })
        }

        // 股利
        function dividend(code, eps) {
            var url = '{{ route("profit.dividend", ['code' => ':code']) }}'
            return axios.get(url.replace(':code', code)).then(function (response) {
                dividends = []
                rates = []

                $('.form-group-dividend').each(function (index) {
                    if (eps[index + 1] !== undefined) {
                        v = response.data[index]
                        rate = Math.round((v.cash / eps[index + 1].eps) * 100)

                        dividends.push([v.year, v.cash])
                        rates.push([v.year, rate])

                        $(this).find('.input-date').html(v.year)
                        $(this).find('.input-rate').html(rate + '%')
                        $(this).find('.input-value').val(Math.round(v.cash * 100) / 100)
                    }
                })

                rates.reverse()
                dividends.reverse()

                Highcharts.chart('quarterly-dividend-bar', {
                    colors: ['#45617d'],
                    chart: {
                        type: 'column',
                        height: 200
                    },
                    plotOptions: {
                        column: {
                            borderColor: '#45617d'
                        }
                    },
                    title: {
                        text: '股利'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: null
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        formatter: function () {
                            return this.key + ': ' + this.y + '</span>'
                        }
                    },
                    series: [{
                        data: dividends,
                    }]
                });

                Highcharts.chart('quarterly-dividend-send-bar', {
                    colors: ['#45617d'],
                    chart: {
                        type: 'column',
                        height: 200
                    },
                    plotOptions: {
                        column: {
                            borderColor: '#45617d'
                        }
                    },
                    title: {
                        text: 'EPS現金配發率'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        title: {
                            text: null
                        },
                        min: 0,
                        max: 100,
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        formatter: function () {
                            return this.key + ': ' + this.y + '</span>'
                        }
                    },
                    series: [{
                        data: rates,
                    }]
                });

                toastr.success('查股利成功')
                return true
            }).catch(function (error) {
                console.log(error)
                toastr.error('查股利無資料')
                return false
            })
        }

        // 本益比
        function pe(code) {
            var url = '{{ route("profit.pe", ['code' => ':code']) }}'
            return axios.get(url.replace(':code', code)).then(function (response) {
                let max = []
                let min = []
                let avg = []
                let price_max = []
                let price_min = []
                let eps = []
                let gross = []

                response.data.forEach(function (v) {
                    max.push([v.year + '-Q' + v.quarterly, v.pes['max']])
                    min.push([v.year + '-Q' + v.quarterly, v.pes['min']])
                    avg.push([v.year + '-Q' + v.quarterly, v.pes['avg']])
                    price_max.push([v.year + '-Q' + v.quarterly, v.prices['max']])
                    price_min.push([v.year + '-Q' + v.quarterly, v.prices['min']])
                    eps.push([v.year + '-Q' + v.quarterly, v.eps])
                    gross.push([v.year + '-Q' + v.quarterly, v.gross])
                })

                max.reverse()
                min.reverse()
                avg.reverse()
                eps.reverse()
                price_max.reverse()
                price_min.reverse()
                gross.reverse()

                Highcharts.chart('pe-price-chat', {
                    title: {
                        text: 'PE-price'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: [{
                        title: {
                            text: 'pe'
                        },
                    }, {
                        title: {
                            text: '月均'
                        },
                        opposite: true
                    }],
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        id: 'max',
                        name: '最大',
                        data: max
                    }, {
                        id: 'min',
                        name: '最低',
                        data: min
                    }, {
                        id: 'avg',
                        name: '平均',
                        data: avg,
                    }, {
                        id: 'price_max',
                        name: '月max',
                        yAxis: 1,
                        type: 'line',
                        data: price_max,
                        color: '#47474c',
                    }, {
                        id: 'price_min',
                        name: '月min',
                        yAxis: 1,
                        type: 'line',
                        data: price_min,
                        color: '#6a6a73',
                    }]
                });

                Highcharts.chart('pe-eps-chat', {
                    title: {
                        text: 'PE-eps'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: [{
                        title: {
                            text: 'pe'
                        },
                    }, {
                        title: {
                            text: 'EPS'
                        },
                        opposite: true
                    }],
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        id: 'max',
                        name: '最大',
                        data: max
                    }, {
                        id: 'min',
                        name: '最低',
                        data: min
                    }, {
                        id: 'avg',
                        name: '平均',
                        data: avg,
                    }, {
                        id: 'eps',
                        name: 'eps',
                        yAxis: 1,
                        type: 'line',
                        data: eps,
                        color: '#47474c',
                    }]
                });

                Highcharts.chart('pe-gross-chat', {
                    title: {
                        text: 'PE-gross'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: [{
                        title: {
                            text: 'pe'
                        },
                    }, {
                        title: {
                            text: '毛利'
                        },
                        opposite: true
                    }],
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        id: 'max',
                        name: '最大',
                        data: max
                    }, {
                        id: 'min',
                        name: '最低',
                        data: min
                    }, {
                        id: 'avg',
                        name: '平均',
                        data: avg,
                    }, {
                        id: 'gross',
                        name: '毛利',
                        yAxis: 1,
                        type: 'line',
                        data: gross,
                        color: '#47474c',
                    }]
                });

                Highcharts.chart('price-eps-chat', {
                    title: {
                        text: 'price-eps'
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: [{
                        title: {
                            text: 'price'
                        },
                    }, {
                        title: {
                            text: 'eps'
                        },
                        opposite: true
                    }],
                    tooltip: {
                        crosshairs: true,
                        shared: true,
                    },
                    series: [{
                        id: 'price_max',
                        name: '月max',
                        type: 'line',
                        data: price_max,
                        color: '#47474c',
                    }, {
                        id: 'price_min',
                        name: '月min',
                        type: 'line',
                        data: price_min,
                        color: '#6a6a73',
                    }, {
                        id: 'eps',
                        name: 'eps',
                        yAxis: 1,
                        type: 'line',
                        data: eps,
                    }]
                });

                toastr.success('查pe成功')
                return true
            }).catch(function (error) {
                console.log(error)
                toastr.error('查pe無資料')
                return false
            })
        }

        // yoy提示
        function hint() {
            $('.form-group-month-revenue .input-rate, .form-group-quarterly-revenue .input-rate').each(function () {
                $(this).removeClass('span-decline')
                $(this).removeClass('span-growing')

                v = parseFloat($(this).html().replace('%', ''))

                if (v < 0) {
                    $(this).addClass('span-decline')
                } else if (v >= 20) {
                    $(this).addClass('span-growing')
                }
            })
        }

        function setInput(e, date, rate, value) {
            e.find('.input-date').html(date)
            e.find('.input-text').html(amountText(value))
            e.find('.input-rate').html(rate + '%')
            e.find('.input-value').val(Math.round(value))
        }
    </script>
@stop

@section('dropdown')
    <li><a href="#base-title" class="dropdown-item">基本</a></li>
    <li><a href="#revenue-title" class="dropdown-item">近12個月營收(百萬)</a></li>
    <li><a href="#quarterly-revenue-title" class="dropdown-item">近12季營收(百萬)</a></li>
    <li><a href="#eps-title" class="dropdown-item">EPS</a></li>
    <li><a href="#business-title" class="dropdown-item">經營績效</a></li>
    @foreach($business as $i => $value)
        <li><a href="#business-{{ $i }}-title" class="dropdown-item">{{ $value['title'] }}</a></li>
    @endforeach
@stop

@section('content')
    <div class="card card-default" id="base-title">
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
                                <span class="input-group-text">代號</span>
                            </div>
                            <input type="text" class="form-control" id="code">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">名稱</span>
                            </div>
                            <input type="text" class="form-control" id="name">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">股本</span>
                                <span class="input-group-text" id="capital_text">0億</span>
                            </div>
                            <input type="text" class="form-control" id="capital">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">季</span>
                            </div>
                            <select class="custom-select" id="quarterly">
                                @foreach($quarterlys as $v)
                                    <option value="{{ $v }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">月</span>
                            </div>
                            <select class="custom-select" id="year_month">
                                @foreach($yearMonths as $v)
                                    <option value="{{ $v }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default" id="revenue-title">
        <div class="card-header">
            <h3 class="card-title">近5年月營收(百萬)</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-12 card-body table-responsive p-0" style="height: 700px;">
                    <table id="month-revenue" class="table table-dark table-head-fixed text-nowrap">
                        <thead>
                        <tr>
                            <th scope="col">年月</th>
                            <th scope="col">營收</th>
                            <th scope="col">yoy</th>
                            <th scope="col">qoq</th>
                            <th scope="col">營收(千)</th>
                            <th scope="col">累積營收(千)</th>
                            <th scope="col">去年累積營收(千)</th>
                            <th scope="col">累積營收年增</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="month-revenue-bar"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="month-revenue-bar2"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="month-revenue-bar3"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="month-revenue-bar4"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default" id="quarterly-revenue-title">
        <div class="card-header">
            <h3 class="card-title">近12季營收(百萬)</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-12">
                    <table id="quarterly-revenue" class="table table-dark">
                        <thead>
                        <tr>
                            <th scope="col">年季</th>
                            <th scope="col">營收</th>
                            <th scope="col">yoy</th>
                            <th scope="col">營收(千)</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="quarterly-revenue-bar"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="quarterly-revenue-bar2"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="quarterly-revenue-bar3"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="quarterly-revenue-yoy-bar"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default" id="eps-title">
        <div class="card-header">
            <h3 class="card-title">EPS</h3>
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
                            <input type="text" class="input-group-text input-group-width" value="近12季EPS" disabled>
                        </div>
                    </div>
                    @for ($i = 1; $i <= 12; $i++)
                        <div class="form-group form-group-quarterly-eps">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-date"></span>
                                </div>
                                <input type="number" class="form-control input-value" readonly>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="input-group-text input-group-width" value="近8年EPS" disabled>
                        </div>
                    </div>
                    @for ($i = 1; $i <= 8; $i++)
                        <div class="form-group form-group-eps">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-date"></span>
                                </div>
                                <input type="number" class="form-control input-value" readonly>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="input-group-text input-group-width" value="近8年股利" disabled>
                        </div>
                    </div>
                    @for ($i = 1; $i <= 8; $i++)
                        <div class="form-group form-group-dividend">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-date"></span>
                                </div>
                                <input type="number" class="form-control input-value" readonly>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="col-md-5">
                    <div id="quarterly-eps-bar"></div>
                    <div id="quarterly-dividend-send-bar"></div>
                    <div id="quarterly-dividend-bar"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default" id="eps-history">
        <div class="card-header">
            <h3 class="card-title">歷史EPS</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-12">
                    <table id="eps-year" class="table table-dark">
                        <thead>
                        <tr>
                            <th scope="col">季別/年度</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row">Q1</th>
                        </tr>
                        <tr>
                            <th scope="row">Q2</th>
                        </tr>
                        <tr>
                            <th scope="row">Q3</th>
                        </tr>
                        <tr>
                            <th scope="row">Q4</th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="eps-chat"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="pe-price-chat"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="pe-eps-chat"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="pe-gross-chat"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="price-eps-chat"></div>
                </div>
            </div>
        </div>
        <div class="card card-default" id="business-title">
            <div class="card-header">
                <h3 class="card-title">經營績效</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                            class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                            class="fas fa-remove"></i></button>
                </div>
            </div>
            <div class="card-body" style="display: block;">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control input-width-1" value="季度" readonly>
                                <input type="text" class="form-control input-width-1" value="營" readonly>
                                <input type="text" class="form-control input-width-1" value="毛" readonly>
                                <input type="text" class="form-control input-width-1" value="費" readonly>
                                <input type="text" class="form-control input-width-1" value="業" readonly>
                                <input type="text" class="form-control input-width-1" value="其" readonly>
                                <input type="text" class="form-control input-width-1" value="利" readonly>
                                <input type="text" class="form-control input-width-1" value="稅後" readonly>
                                <input type="text" class="form-control input-width-1" value="所得" readonly>
                                <input type="text" class="form-control input-width-1" value="非" readonly>
                                <input type="text" class="form-control input-width-1" value="母" readonly>
                                <input type="text" class="form-control input-width-1" value="e" readonly>
                                <input type="text" class="form-control input-width-1" value="非e" readonly>
                                <input type="text" class="form-control input-width-1" value="本%" readonly>
                            </div>

                            @for ($i = 1; $i <= 12; $i++)
                                <div class="input-group form-group-total" data-index="{{ $i-1 }}">
                                    <input type="text" class="form-control input-width-1" data-name="year" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="revenue" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="gross" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="fee" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="outside" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="other" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="profit" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="profit_after"
                                           readonly>
                                    <input type="text" class="form-control input-width-1" data-name="tax" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="profit_non"
                                           readonly>
                                    <input type="text" class="form-control input-width-1" data-name="profit_main"
                                           readonly>
                                    <input type="text" class="form-control input-width-1" data-name="eps" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="non_eps" readonly>
                                    <input type="text" class="form-control input-width-1" data-name="this" readonly>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 card-body table-responsive p-0" style="height: 400px;">
                        <table id="profit-table" class="table table-dark table-head-fixed text-nowrap">
                            <thead>
                            <tr>
                                <th scope="col">季</th>
                                <th scope="col">毛利率</th>
                                <th scope="col">費用率</th>
                                <th scope="col">利益率</th>
                                <th scope="col">EPS</th>
                                <th scope="col">營收年增</th>
                                <th scope="col">毛利年增</th>
                                <th scope="col">費用年增</th>
                                <th scope="col">利益年增</th>
                                <th scope="col">EPS年增</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="profits-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="cost-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="gross-fee-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="gross-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="profit-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="fee-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="fee-amount-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="fee-detail-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="fee-n-research-depreciation-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="research-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="depreciation-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="revenues-fee-chat"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="outside-chat"></div>
                    </div>
                </div>
            </div>
        </div>
        @foreach($business as $i => $value)
            <div class="card card-default" id="business-{{ $i }}-title">
                <div class="card-header">
                    <h3 class="card-title">{{ $value['title'] }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-minus"></i></button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                class="fas fa-remove"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        @foreach($value['list'] as $v)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="text" class="input-group-text input-group-width"
                                               value="{{ $v['title'] }}" disabled>
                                    </div>
                                </div>
                                @for ($i = 1; $i <= 12; $i++)
                                    <div class="form-group form-group-quarterly" data-index="{{ $i-1 }}"
                                         data-name="{{ $v['name'] }}">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text input-date input-width"></span>
                                                <span class="input-group-text input-text input-width"></span>
                                                <span class="input-group-text input-rate input-width"></span>
                                            </div>
                                            <input type="number" class="form-control input-value" readonly>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
    @endforeach
@stop
