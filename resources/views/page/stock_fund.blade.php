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
        $('#select-btn').click(function () {
            var url = "{{ route('stock.fund.list', ['year' => ':year', 'code' => ':code']) }}"
            axios.get(url.replace(':year', $('#year').val()).replace(':code', $('#code').val())).then(function (response) {
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

                toastr.success('查詢成功')
            }).catch(function (error) {
                toastr.error('無資料')
            })
        })
    </script>
@stop

@section('content')
    <div class="card card-default" id="base-title">
        <div class="card-header">
            <h3 class="card-title">個股基金</h3>
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
            <h3 class="card-title">個股基金持股</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            @for($c = 0; $c < 4; $c++)
                <div class="row">
                    @for($i = 1; $i <= 3; $i++)
                        <div class="col-md-4">
                            <table data-m="{{ $i + ($c * 3) }}"
                                   class="table table-bordered table-striped dataTable dtr-inline"
                                   width="100%"
                                   cellspacing="0">
                                <thead>
                                <tr>
                                    @for($a = 0; $a < count($header); $a++)
                                        @if($a == 0)
                                            <th>{{ $i + ($c * 3) . '月' . $header[$a] }}</th>
                                        @else
                                            <th>{{ $header[$a] }}</th>
                                        @endif
                                    @endfor
                                </tr>
                                </thead>
                            </table>
                        </div>
                    @endfor
                </div>
            @endfor
        </div>
    </div>
@stop
