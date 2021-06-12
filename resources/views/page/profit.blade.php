@extends('page')

@section('css')
    <style>
        .dark-mode .input-group-text {
            color: #8d8585;
            font-weight: bold;
            background: #343a40;
        }

        .dark-mode input {
            text-align: center;
        }

        .dark-mode .input-width {
            min-width: 70px;
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
        $('#code').change(function () {
            var url = '{{ route("stock.search", ":code") }}';
            axios.get(url.replace(':code', $(this).val())).then(function (response) {
                $('#name').val(response.data.name)
                $('#capital').val(Math.round(response.data.capital / 1000))
                $('#capital_text').html(roundText(response.data.capital / 1000))
                toastr.success('查訊成功')

                if (revenueMonth()) {

                }

            }).catch(function (error) {
                toastr.error('查無資料')
            })
        })

        // 月營收
        function revenueMonth() {
            code = $('#code').val()
            year = $('#year_month').val().slice(0, 4)
            month = $('#year_month').val().slice(5, 7)

            var url = '{{ route("revenue.recent", ['code' => ':code', 'year' => ':year', 'month' => ':month']) }}'
            url = url.replace(':code', code).replace(':year', year).replace(':month', month)

            axios.get(url).then(function (response) {
                $('.form-group-month-revenue').each(function (index) {
                    v = response.data[index]
                    value = v.value / 1000

                    $(this).find('.input-date').html(v.year + "-" + (new String(v.month)).padStart(2, '0'))
                    $(this).find('.input-text').html(roundText(value))
                    $(this).find('.input-yoy').html(v.yoy + '%')
                    $(this).find('.input-value').val(Math.round(value))
                })

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
                yoyHint()

                chartBar('month-revenue-bar', '月營收(百萬)', dates, revenues)
                chartLine('month-revenue-yoy-bar', '月營收yoy', dates, yoys)

                toastr.success('月營收成功')
                return true
            }).catch(function (error) {
                toastr.error('查無月營收')
            })

            return false
        }

        // yoy提示
        function yoyHint() {
            $('.input-yoy').each(function () {
                v = parseFloat($(this).html().replace('%', ''))

                if (v < 0) {
                    $(this).addClass('span-decline')
                } else if (v >= 20) {
                    $(this).addClass('span-growing')
                } else {
                    $(this).removeClass('span-decline')
                    $(this).removeClass('span-growing')
                }
            })
        }

        function chartBar(id, title, labels, data) {
            var ctx = document.getElementById(id).getContext('2d');
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
                            ticks: {
                                callback: function (value, index, values) {
                                    return new Intl.NumberFormat('en-IN', {maximumSignificantDigits: 3}).format(value);
                                }
                            }
                        }]
                    }
                }
            });
        }

        function chartLine(id, title, labels, data) {
            var ctx = document.getElementById(id).getContext('2d');
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
                }
            });
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
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">營收</h3>
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
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="input-group-text" value="近12個月營收(百萬)" disabled>
                        </div>
                    </div>
                    @for ($i = 1; $i <= 12; $i++)
                        <div class="form-group form-group-month-revenue">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-date input-width"></span>
                                    <span class="input-group-text input-text input-width"></span>
                                    <span class="input-group-text input-yoy input-width"></span>
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
@stop
