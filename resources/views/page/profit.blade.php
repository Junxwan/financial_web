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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        $('#code, #quarterly, #year_month').change(function () {
            var url = '{{ route("stock.search", ":code") }}';
            code = $('#code').val()

            axios.get(url.replace(':code', code)).then(function (response) {
                $('#name').val(response.data.name)
                $('#capital').val(Math.round(response.data.capital / 1000))
                $('#capital_text').html(roundText(response.data.capital / 1000))
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
                $('.form-group-month-revenue').each(function (index) {
                    v = response.data[index]
                    setInput($(this), v.year + "-" + (new String(v.month)).padStart(2, '0'), v.yoy, v.value / 1000)
                })

                $('#year_month').val(
                    response.data[0].year + '-' + (new String(response.data[0].month)).padStart(2, '0')
                )

                dates = []
                revenues = []
                yoys = []
                response.data.forEach(function (v) {
                    if (v.yoy == 0) {
                        return
                    }

                    dates.push(v.year + "-" + (new String(v.month)).padStart(2, '0'))
                    revenues.push(Math.round(v.value / 1000))
                    yoys.push(v.yoy)
                })

                dates.reverse()
                revenues.reverse()
                yoys.reverse()

                chartBar('month-revenue-bar', '月營收(百萬)', dates, revenues)
                chartLine('month-revenue-yoy-bar', '月營收yoy', dates, yoys)

                toastr.success('月營收成功')
                return true
            }).catch(function (error) {
                console.log(error)
                toastr.error('查無月營收')
                return false
            })
        }

        // 綜合損益表
        function profit(code, year, quarterly) {
            var url = '{{ route("profit.recent", ['code' => ':code', 'year' => ':year', 'quarterly' => ':quarterly']) }}'
            url = url.replace(':code', code).replace(':year', year).replace(':quarterly', quarterly)

            return axios.get(url).then(function (response) {
                $('.form-group-quarterly-revenue').each(function (index) {
                    v = response.data[index]
                    setInput($(this), v.year + "-Q" + v.quarterly, v.revenue_yoy, v.revenue / 1000)
                })

                $('#quarterly').val(
                    response.data[0].year + '-Q' + response.data[0].quarterly
                )

                dates = []
                revenues = []
                yoys = []
                response.data.forEach(function (v) {
                    if (v.revenue_yoy == 0) {
                        return
                    }

                    dates.push(v.year + "-Q" + v.quarterly)
                    revenues.push(Math.round(v.revenue / 1000))
                    yoys.push(v.revenue_yoy)
                })

                dates.reverse()
                revenues.reverse()
                yoys.reverse()

                chartBar('quarterly-revenue-bar', '季營收(百萬)', dates, revenues)
                chartLine('quarterly-revenue-yoy-bar', '季營收yoy', dates, yoys)

                // eps
                $('.form-group-quarterly-eps').each(function (index) {
                    v = response.data[index]
                    $(this).find('.input-date').html(v.year + "Q" + v.quarterly)
                    $(this).find('.input-value').val(Math.round(v.eps * 100) / 100)
                })

                // 總結
                $('.form-group-total').each(function () {
                    v = response.data[$(this).data('index')]

                    $(this).find('span').html(v.year + "Q" + v.quarterly)
                    $(this).find('input').each(function () {
                        name = $(this).data('name')

                        switch (name) {
                            case 'year':
                                value = v.year + "Q" + v.quarterly
                                break
                            case 'eps':
                                value = Math.round(v[name] * 100) / 100
                                break
                            case 'non_eps':
                                value = Math.round((v.outside / v.profit_main) * v.eps * 100) / 100
                                break
                            case 'this':
                                value = Math.round(100 - (Math.round((v.outside / v.profit_main) * v.eps * 100) / 100) / v.eps * 100);
                                break;
                            default:
                                value = roundText(Math.round(v[$(this).data('name')] / 1000))
                        }

                        $(this).val(value)
                    })
                })

                datas = {
                    gross: []
                }

                $('.form-group-quarterly').each(function () {
                    v = response.data[$(this).data('index')]
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
                            Math.round(value / 1000)
                        )
                    }
                })

                dates = dates.reverse().slice(0, 12)

                chartLine('gross-bar', '毛利', dates.reverse(), datas['gross'].reverse())

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
                    v = response.data[index]

                    if (v.eps !== '') {
                        $(this).find('.input-date').html(v.year)
                        $(this).find('.input-value').val(Math.round(v.eps * 100) / 100)
                    }
                })

                dates = []
                eps = []
                response.data.forEach(function (v) {
                    $("#eps-year>thead>tr").append("<th>" + v.year + "</th>")
                    $("#eps-year>tbody>tr:nth-child(1)").append("<td>" + v.q1 + "</td>")
                    $("#eps-year>tbody>tr:nth-child(2)").append("<td>" + v.q2 + "</td>")
                    $("#eps-year>tbody>tr:nth-child(3)").append("<td>" + v.q3 + "</td>")
                    $("#eps-year>tbody>tr:nth-child(4)").append("<td>" + v.q4 + "</td>")

                    if (v.eps !== '') {
                        dates.push(v.year)
                        eps.push(v.eps)
                    }
                })

                dates.reverse()
                eps.reverse()

                chartBar('quarterly-eps-bar', 'EPS', dates, eps)
                dividend(code, response.data)

                toastr.success('查EPS成功')
                hint()
                return true
            }).catch(function (error) {
                toastr.error('查EPS無資料')
                return false
            })
        }

        // 股利
        function dividend(code, eps) {
            var url = '{{ route("profit.dividend", ['code' => ':code']) }}'
            return axios.get(url.replace(':code', code)).then(function (response) {
                dates = []
                dividends = []
                rates = []

                $('.form-group-dividend').each(function (index) {
                    v = response.data[index]
                    rate = Math.round((v.cash / eps[index + 1].eps) * 100)

                    dividends.push(v.cash)
                    dates.push(v.year)
                    rates.push(rate)

                    $(this).find('.input-date').html(v.year)
                    $(this).find('.input-rate').html(rate + '%')
                    $(this).find('.input-value').val(Math.round(v.cash * 100) / 100)
                })

                dates.reverse()
                rates.reverse()
                dividends.reverse()

                chartBar('quarterly-dividend-bar', '股利', dates, dividends)
                chartBar('quarterly-dividend-send-bar', 'EPS現金配發率', dates, rates, 100)

                toastr.success('查股利成功')
                return true
            }).catch(function (error) {
                toastr.error('查股利無資料')
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
            e.find('.input-text').html(roundText(value))
            e.find('.input-rate').html(rate + '%')
            e.find('.input-value').val(Math.round(value))
        }

        function chartBar(id, title, labels, data, yMin, yMax) {
            var ctx = document.getElementById(id).getContext('2d');
            var ticks = {
                min: 0,
                callback: function (value, index, values) {
                    return new Intl.NumberFormat('en-IN', {maximumSignificantDigits: 3}).format(value);
                }
            }

            if (typeof yMax !== 'undefined') {
                ticks.max = yMax
                ticks.stepSize = 20
            }

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: title,
                        backgroundColor: 'rgb(52,118,173)',
                        borderColor: 'rgb(52,118,173)',
                        data: data,
                    }]
                },
                options: {
                    legend: {
                        labels: {
                            fontColor: '#ffffff',
                        },
                    },
                    scales: {
                        yAxes: [{
                            ticks: ticks
                        }]
                    }
                }
            });
        }

        function chartLine(id, title, labels, data, yMin, yMax) {
            var ctx = document.getElementById(id).getContext('2d');
            var ticks = {
                callback: function (value, index, values) {
                    return new Intl.NumberFormat('en-IN', {maximumSignificantDigits: 3}).format(value);
                }
            }

            if (typeof yMin !== 'undefined') {
                ticks.min = yMin
                ticks.stepSize = 20
            }

            if (typeof yMax !== 'undefined') {
                ticks.max = yMax
                ticks.stepSize = 20
            }

            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: title,
                        fill: false,
                        backgroundColor: 'rgb(52,118,173)',
                        borderColor: 'rgb(52,118,173)',
                        data: data,
                        tension: 0.1
                    }]
                },
                options: {
                    legend: {
                        labels: {
                            fontColor: '#ffffff',
                        },
                    },
                    scales: {
                        yAxes: [{
                            ticks: ticks
                        }]
                    }
                }
            });
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
            <h3 class="card-title">近12個月營收(百萬)</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-5">
                    @for ($i = 1; $i <= 12; $i++)
                        <div class="form-group form-group-month-revenue">
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
                <div class="col-md-7">
                    <canvas id="month-revenue-bar"></canvas>
                    <canvas id="month-revenue-yoy-bar"></canvas>
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
                <div class="col-md-5">
                    @for ($i = 1; $i <= 12; $i++)
                        <div class="form-group form-group-quarterly-revenue">
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
                <div class="col-md-7">
                    <canvas id="quarterly-revenue-bar"></canvas>
                    <canvas id="quarterly-revenue-yoy-bar"></canvas>
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
                    <canvas id="quarterly-eps-bar"></canvas>
                    <canvas id="quarterly-dividend-send-bar"></canvas>
                    <canvas id="quarterly-dividend-bar"></canvas>
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
                            <input type="text" class="form-control input-width-1" value="稅前" readonly>
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
                                <input type="text" class="form-control input-width-1" data-name="profit_pre" readonly>
                                <input type="text" class="form-control input-width-1" data-name="profit_after" readonly>
                                <input type="text" class="form-control input-width-1" data-name="tax" readonly>
                                <input type="text" class="form-control input-width-1" data-name="profit_non" readonly>
                                <input type="text" class="form-control input-width-1" data-name="profit_main" readonly>
                                <input type="text" class="form-control input-width-1" data-name="eps" readonly>
                                <input type="text" class="form-control input-width-1" data-name="non_eps" readonly>
                                <input type="text" class="form-control input-width-1" data-name="this" readonly>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <canvas id="gross-bar"></canvas>
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
