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
            var url = "{{ route('fund.stocks', ['year' => ':year', 'fundId' => ':fundId']) }}"
            axios.get(url.replace(':year', $('#year').val()).replace(':fundId', $('#fund').val())).then(function (response) {
                $('.form-group-ym input').each(function () {
                    index = $(this).data('index') - 1
                    if (response.data[index] !== undefined) {
                        v = response.data[index][0]
                        $(this).val(v.year + '-' + (new String(v.month)).padStart(2, '0'))
                    }
                })

                $('.form-group-list input').each(function () {
                    index = $(this).data('index') - 1
                    order = $(this).data('order') - 1

                    if (response.data[index] !== undefined && response.data[index][order] !== undefined) {
                        v = response.data[index][order]

                        switch ($(this).data('name')) {
                            case 'name':
                                $(this).val(v.name)
                                break;
                            case 'ratio':
                                $(this).val(v.ratio)
                                break
                        }
                    }
                })

                $('.form-group-ym input, .form-group-list input').val('')

                toastr.success('查持股明細成功')
            }).catch(function (error) {
                toastr.error('查持股明細無資料')
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
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">年</span>
                            </div>
                            <select class="custom-select" id="year">
                                @foreach($year as $v)
                                    <option value="{{ $v }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
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
            <h3 class="card-title">持股明細</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            @for($c = 0; $c < 2; $c++)
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="input-group form-group-ym">
                                @for($i = 1; $i <= 6; $i++)
                                    <input type="text" class="form-control" data-index="{{ $i + ($c * 6) }}" readonly>
                                @endfor
                            </div>

                            @for($i = 1; $i <= 10; $i++)
                                <div class="input-group form-group-list">
                                    @for($a = 1; $a <= 12; $a++)
                                        <input type="text" class="form-control" data-index="{{ ceil($a/2) + ($c * 6) }}"
                                               data-order="{{ $i }}" data-name="{{ ($a)%2 == 1 ? 'name': 'ratio' }}"
                                               readonly>
                                    @endfor
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
@stop
