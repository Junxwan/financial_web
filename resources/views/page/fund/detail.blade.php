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
        $('#company').change(function () {
            var url = "{{ route('fund.list', ':id') }}"
            axios.get(url.replace(':id', $(this).val())).then(function (response) {
                var html = ''
                response.data.forEach(function (v) {
                    html += '<option value="' + v.id + '">' + v.name + '</option>'
                })

                $('#fund')
                    .find('option')
                    .remove()
                    .end()
                    .append(html)

                toastr.success('查投信成功')
            }).catch(function (error) {
                toastr.error('查投信無資料')
            })
        })

        $('#select-btn').click(function () {
            let scale = []
            let value = []

            let url = "{{ route('fund.detail.scale', ['id' => ':id']) }}"
            axios.get(url.replace(':id', $('#fund').val())).then(function (response) {
                response.data.forEach(function (v, index) {
                    scale.push([v.year + '-' + v.month, v.scale])
                })

                toastr.success('查規模成功')

                url = "{{ route('fund.detail.value', ['id' => ':id']) }}"
                axios.get(url.replace(':id', $('#fund').val())).then(function (response) {
                    response.data.forEach(function (v, index) {
                        value.push([v.year + '-' + v.month, v.value])
                    })

                    scale.reverse()
                    value.reverse()

                    Highcharts.chart('scale-chat', {
                        title: {
                            text: '規模'
                        },
                        xAxis: {
                            type: "category"
                        },
                        yAxis: {
                            title: {
                                text: null
                            }
                        },
                        series: [{
                            type: 'line',
                            name: '規模',
                            data: scale,
                            color: '#2f99a3',
                        }]
                    });

                    Highcharts.chart('value-chat', {
                        title: {
                            text: '淨值'
                        },
                        xAxis: {
                            type: "category"
                        },
                        yAxis: {
                            title: {
                                text: null
                            }
                        },
                        series: [{
                            type: 'line',
                            data: value,
                            name: '淨值',
                            color: '#2f99a3',
                        }]
                    });

                    toastr.success('查淨值成功')
                }).catch(function (error) {
                    console.log(error)
                    toastr.error('查淨值失敗')
                })
            }).catch(function (error) {
                console.log(error)
                toastr.error('查規模失敗')
            })
        })
    </script>
@stop

@section('content')
    <div class="card card-default" id="base-title">
        <div class="card-header">
            <h3 class="card-title">基金</h3>
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
                                <span class="input-group-text">投信</span>
                            </div>
                            <select class="custom-select" id="company">
                                @foreach($company as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">基金</span>
                            </div>
                            <select class="custom-select" id="fund">
                                @foreach($fund as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
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
            <h3 class="card-title">基金</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div id="scale-chat" class="row">
            </div>
            <div id="value-chat" class="row">
            </div>
        </div>
    </div>
@stop
