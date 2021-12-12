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
                yoys = []
                qoqs = []
                yearRevenueg = {}
                monthPrice = []

                response.data.forEach(function (v, index) {
                    if (index <= 48) {
                        let html =
                            "<tr>" +
                            "<td>" + v.year + "-" + v.month + "</td>" +
                            "<td>" + amountText(v.value) + "</td>" +
                            "<td>" + spanColor(v.yoy) + "</td>" +
                            "<td>" + spanColor(v.qoq) + "</td>" +
                            "<td>" + v.value + "</td>" +
                            "</tr>"

                        $("#month-revenue>tbody").append(html)
                    }

                    let t = Date.parse(v.year + '-' + v.month + '-02')
                    revenues.push([t, v.value])
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
                        legend: {
                            enabled: false
                        },
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
                        legend: {
                            enabled: false
                        },
                        tooltip: {
                            formatter: function () {
                                return this.series.name + ': ' + '<span style="color:#7dbbd2">' + amountText(this.y) + '</span>'
                            }
                        },
                        series: seriesRevenues
                    });

                    Highcharts.chart('month-revenue-yoy-bar', {
                        title: {
                            text: '成長'
                        },
                        xAxis: {
                            type: 'datetime',
                            labels: {
                                formatter: function () {
                                    return Highcharts.dateFormat('%Y-%m', this.value);
                                }
                            }
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
                            xDateFormat: '%Y-%m',
                        },
                        series: [{
                            name: 'yoy',
                            data: yoys
                        }, {
                            name: 'qoq',
                            data: qoqs
                        }]
                    });

                    Highcharts.chart('month-revenue-yoy-bar2', {
                        title: {
                            text: 'yoy'
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
                                text: 'yoy'
                            },
                            plotLines: [{
                                color: '#70285c',
                                width: 1,
                                value: 20,
                                zIndex: 0
                            }],
                        }, {
                            title: {
                                text: '月收盤'
                            },
                            opposite: true
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
                            xDateFormat: '%Y-%m',
                        },
                        series: [{
                            name: 'yoy',
                            data: yoys,
                            type: 'column',
                        }, {
                            name: '月收盤',
                            yAxis: 1,
                            type: 'line',
                            data: monthPrice,
                        }]
                    });

                    Highcharts.chart('month-revenue-qoq-bar', {
                        title: {
                            text: 'qoq'
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
                            plotLines: [{
                                color: '#70285c',
                                width: 1,
                                value: 20,
                                zIndex: 0
                            }],
                        }, {
                            title: {
                                text: '月收盤'
                            },
                            opposite: true
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
                            xDateFormat: '%Y-%m',
                        },
                        series: [{
                            name: 'qoq',
                            data: qoqs,
                            type: 'column',
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
                $('#quarterly').val(
                    response.data[0].year + '-Q' + response.data[0].quarterly
                )

                revenues = []
                gross = []
                profits = []
                profit_after = []
                datas = []
                response.data.forEach(function (v, index) {
                    date = v.year + "-Q" + v.quarterly
                    if (index <= 12) {
                        let html =
                            "<tr>" +
                            "<td>" + date + "</td>" +
                            "<td>" + amountText(v.revenue) + "</td>" +
                            "<td>" + spanColor(v.revenue_yoy) + "</td>" +
                            "<td>" + v.revenue + "</td>" +
                            "</tr>"

                        $("#quarterly-revenue>tbody").append(html)
                    }

                    datas.push(date)
                    revenues.push([date, v.revenue])
                    gross.push([date, Math.round((v.gross / v.revenue) * 10000) / 100])
                    profits.push([date, Math.round((v.profit / v.revenue) * 10000) / 100])
                    profit_after.push([date, Math.round((v.profit_after / v.revenue) * 10000) / 100])
                })

                revenues.reverse()
                gross.reverse()
                profits.reverse()
                profit_after.reverse()

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
                years = []
                data = []
                response.data.forEach(function (v) {
                    if (!data.hasOwnProperty(v.year)) {
                        data[v.year] = []
                    }

                    data[v.year].push(v)
                })

                data.forEach(function (value, key) {
                    let b1, b2, b3, b4 = false
                    years.push(key)
                    value.forEach(function (v) {
                        switch (v.quarterly) {
                            case 1:
                                q1.push(v.revenue)
                                q1g.push(v.gross_ratio)
                                q1p.push(v.profit_ratio)
                                q1o.push(v.outside)
                                q1f.push(v.fee_ratio)
                                b1 = true
                                break
                            case 2:
                                q2.push(v.revenue)
                                q2g.push(v.gross_ratio)
                                q2p.push(v.profit_ratio)
                                q2o.push(v.outside)
                                q2f.push(v.fee_ratio)
                                b2 = true
                                break
                            case 3:
                                q3.push(v.revenue)
                                q3g.push(v.gross_ratio)
                                q3p.push(v.profit_ratio)
                                q3o.push(v.outside)
                                q3f.push(v.fee_ratio)
                                b3 = true
                                break
                            case 4:
                                q4.push(v.revenue)
                                q4g.push(v.gross_ratio)
                                q4p.push(v.profit_ratio)
                                q4o.push(v.outside)
                                q4f.push(v.fee_ratio)
                                b4 = true
                                break
                        }
                    })

                    if (!b1) {
                        q1.push(0)
                        q1g.push(0)
                        q1p.push(0)
                        q1o.push(0)
                        q1f.push(0)
                    }

                    if (!b2) {
                        q2.push(0)
                        q2g.push(0)
                        q2p.push(0)
                        q2o.push(0)
                        q2f.push(0)
                    }

                    if (!b3) {
                        q3.push(0)
                        q3g.push(0)
                        q3p.push(0)
                        q3o.push(0)
                        q3f.push(0)
                    }

                    if (!b4) {
                        q4.push(0)
                        q4g.push(0)
                        q4p.push(0)
                        q4o.push(0)
                        q4f.push(0)
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

                Highcharts.chart('eps-bar', {
                    colors: ['#45617d'],
                    chart: {
                        type: 'column'
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
                        data: epsq,
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
            <h3 class="card-title">近4年月營收(百萬)</h3>
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
                    <div id="month-revenue-yoy-bar"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="month-revenue-yoy-bar2"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="month-revenue-qoq-bar"></div>
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
                    <div id="eps-bar"></div>
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
                    <div class="col-md-12">
                        <div id="profits-chat"></div>
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
