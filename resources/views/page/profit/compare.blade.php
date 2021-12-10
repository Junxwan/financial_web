@extends('page')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-select.min.css') }}">
@stop

@section('js')
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('js/highcharts.js') }}"></script>
    <script src="{{ asset('js/highstock/themes/dark-unica.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        $('#stock-btn').click(function () {
            let url = "{{ route('stock.name', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                let data = response.data
                let tagsTd = []
                data.tags.forEach(function (v) {
                    tagsTd.push('<span class="badge badge-pill badge-dark">' + v + '</span>')
                })

                $("#stocks-table>tbody").append("<tr class='stock'>" +
                    "<td class='code'>" + data.code + "</td>" +
                    "<td class='name'>" + data.name + "</td>" +
                    "<td>" + amountText(data.capital) + "</td>" +
                    "<td>" + data.c_name + "</td>" +
                    "<td>" + tagsTd.join(' ') + "</td>" +
                    "</tr>")

                toastr.success('查詢成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無資料')
            })
        })

        $('#tag-btn').click(function () {
            let url = "{{ route('stock.names.tag', ['tag' => ':tag']) }}"
            axios.get(url.replace(':tag', $('#select-tag').val())).then(function (response) {
                response.data.forEach(function (v) {
                    let tagsTd = []
                    v.tags.forEach(function (n) {
                        tagsTd.push('<span class="badge badge-pill badge-dark">' + n + '</span>')
                    })

                    $("#stocks-table>tbody").append("<tr class='stock'>" +
                        "<td class='code'>" + v.code + "</td>" +
                        "<td class='name'>" + v.name + "</td>" +
                        "<td>" + amountText(v.capital) + "</td>" +
                        "<td>" + v.c_name + "</td>" +
                        "<td>" + tagsTd.join(' ') + "</td>" +
                        "</tr>")
                })

                toastr.success('查詢成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無資料')
            })
        })

        $('#delete-btn').click(function () {
            $("#stocks-table>tbody>tr").remove()
        })

        $('#financial-btn').click(function () {
            $("#month-revenue-table>tbody>tr").remove()
            $("#financial-table>tbody>tr").remove()

            let url = "{{ route('revenue.last', ['year' => ':year', 'month' => ':month']) }}"
            url = url.replace(':year', $('#year').val()).replace(':month', $('#month').val())
            url += '?code=' + codes().join(',')

            // 月營收
            axios.get(url).then(function (response) {
                response.data.forEach(function (v) {
                    $("#month-revenue-table>tbody").append("<tr>" +
                        "<td>" + v.code + "</td>" +
                        "<td>" + v.name + "</td>" +
                        "<td>" + amountText(v.value) + "</td>" +
                        "<td>" + textR(v.yoy, 30) + "</td>" +
                        "<td>" + v.qoq + "</td>" +
                        "<td>" + amountText(v.total) + "</td>" +
                        "<td>" + amountText(v.y_total) + "</td>" +
                        "<td>" + v.total_increase + "</td>" +
                        "</tr>")
                })

                toastr.success('查詢成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無資料')
            })

            url = "{{ route('profit.codes', ['year' => ':year', 'quarterly' => ':quarterly']) }}"
            url = url.replace(':year', $('#year').val()).replace(':quarterly', $('#quarterly').val())
            url += '?code=' + codes().join(',')

            // 季報
            axios.get(url).then(function (response) {
                response.data.forEach(function (v) {
                    $("#financial-table>tbody").append("<tr>" +
                        "<td>" + v.code + "</td>" +
                        "<td>" + v.name + "</td>" +
                        "<td>" + amountText(v.revenue) + "</td>" +
                        "<td>" + textR(v.gross_ratio, 50) + "</td>" +
                        "<td>" + v.fee_ratio + "</td>" +
                        "<td>" + textR(v.profit_ratio, 100) + "</td>" +
                        "<td>" + textR(v.profit_pre_ratio, 100) + "</td>" +
                        "<td>" + textR(v.profit_after_ratio, 100) + "</td>" +
                        "<td>" + textR(v.eps, 100) + "</td>" +
                        "</tr>")
                })

                toastr.success('查詢成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無資料')
            })
        })

        $('#month-revenues-btn').click(function () {
            let url = "{{ route('revenue.recents', ['year' => ':year', 'month' => ':month']) }}"
            url = url.replace(':year', $('#year').val()).replace(':month', $('#month').val())
            url += '?code=' + codes().join(',')

            // 月營收
            axios.get(url).then(function (response) {
                $("#month-revenues-table>thead>tr>th").remove()
                $("#month-revenues-table>tbody>tr").remove()

                let stock = stocks()
                let chat = {}

                $("#month-revenues-table>thead>tr").append('<th scope="col">年月</th>')

                response.data[0].forEach(function (v) {
                    $("#month-revenues-table>thead>tr").append('<th scope="col">' + stock[v.code] + '</th>')
                    chat[v.code] = []
                })

                response.data.forEach(function (value) {
                    let t = Date.parse(value[0].year + '-' + value[0].month + '-02')
                    let html = "<td>" + value[0].year + '-' + value[0].month + "</td>"

                    value.forEach(function (v) {
                        html += "<td>" + amountText(v.value) + ' (' + textR(v.yoy, 10) + ')' + '(' + textR(v.qoq, 0) + ')' + "</td>"

                        chat[v.code].push([t, v.yoy])
                    })

                    $("#month-revenues-table>tbody").append("<tr>" + html + "</tr>")
                })

                let series = []
                for (const [code, value] of Object.entries(chat)) {
                    value.reverse()

                    series.push({
                        name: stock[code],
                        data: value,
                        type: 'line'
                    })
                }

                console.log(series)

                Highcharts.chart('month-revenues-yoy-chat', {
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
                    yAxis: {
                        title: {
                            text: null
                        },
                        plotLines: [{
                            color: '#70285c',
                            width: 1,
                            value: 20,
                            zIndex: 0
                        }],
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
                    series: series,
                });

                toastr.success('查詢成功')
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無資料')
            })

        })

        function codes() {
            let codes = []
            $('.code').each(function (index, e) {
                codes.push(e.innerHTML)
            })
            return codes
        }

        function names() {
            let names = []
            $('.name').each(function (index, e) {
                names.push(e.innerHTML)
            })
            return names
        }

        function stocks() {
            let stock = {}
            $('.stock').each(function (index, e) {
                stock[$(this).find('.code')[0].innerHTML] = $(this).find('.name')[0].innerHTML
            })
            return stock
        }

        function textR(value, r) {
            if (value >= r) {
                color = '#f33f7a'
            } else if (value < 0) {
                color = '#2a9309'
            } else {
                color = '#f2f5f1'
            }

            return '<span style="color:' + color + '"> ' + value + '</span> '
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
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="stock-btn">
                        增
                    </button>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">標籤</span>
                            </div>
                            <select multiple class="selectpicker form-control" size="10" id="select-tag"
                                    data-container="body" data-live-search="true" title="Select"
                                    data-hide-disabled="true"
                                    data-actions-box="true" data-virtual-scroll="false">
                                @foreach($tags as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="tag-btn">
                        增
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="delete-btn">
                        刪
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">年</span>
                            </div>
                            <select class="custom-select" id="year">
                                @for($i = 0; $i <= 8; $i++)
                                    <option @if($i == 0) selected
                                            @endif value="{{ $year - $i }}">{{ $year - $i }}</option>
                                @endfor
                            </select>
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
                                @for($i = 1 ;$i <= 4; $i++)
                                    <option @if($quarterly == $i) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
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
                            <select class="custom-select" id="month">
                                @for($i = 1; $i <= 12; $i++)
                                    <option @if($i == $month) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="financial-btn">
                        查
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="month-revenues-btn">
                        全營查
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">個股</h3>
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
                    <table id="stocks-table" class="table table-dark">
                        <thead>
                        <tr>
                            <th scope="col" width="7%">代碼</th>
                            <th scope="col" width="10%">名稱</th>
                            <th scope="col" width="15%">股本</th>
                            <th scope="col" width="15%">產業</th>
                            <th scope="col" width="50%">標籤</th>
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
            <h3 class="card-title">月營收</h3>
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
                    <table id="month-revenue-table" class="table table-dark">
                        <thead>
                        <tr>
                            <th scope="col">代碼</th>
                            <th scope="col">名稱</th>
                            <th scope="col">營收</th>
                            <th scope="col">yoy</th>
                            <th scope="col">qoq</th>
                            <th scope="col">累績營收</th>
                            <th scope="col">去年累績營收</th>
                            <th scope="col">累績營收成長</th>
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
            <h3 class="card-title">季報</h3>
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
                    <table id="financial-table" class="table table-dark">
                        <thead>
                        <tr>
                            <th scope="col">代碼</th>
                            <th scope="col">名稱</th>
                            <th scope="col">營收</th>
                            <th scope="col">毛利率</th>
                            <th scope="col">費用率</th>
                            <th scope="col">利益率</th>
                            <th scope="col">稅前淨利率</th>
                            <th scope="col">稅後淨利率</th>
                            <th scope="col">eps</th>
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
                    <table id="month-revenues-table" class="table table-dark table-head-fixed text-nowrap">
                        <thead>
                        <tr>
                            <th scope="col">年月</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="month-revenues-yoy-chat"></div>
                </div>
            </div>
        </div>
    </div>
@stop
