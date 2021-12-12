@extends('page')

@section('css')
    <style>
        .input-group input, select {
            text-align: center;
            text-align-last: center;
        }

        .dark-mode .input-group-text {
            color: #8d8585;
            font-weight: bold;
            background: #343a40;
        }

        .dark-mode .bg-gradient-secondary {
            color: #0c0c0c;
        }

        .dark-mode .span-selected {
            color: #677483;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        $('.select-btn').click(function () {
            let url = '{{ route("profit.get", ['code' => ':code', 'year' => ':year', 'quarterly' => ':quarterly']) }}'
            let group = $(this).data('group')
            let code = $('#code-' + group).val()
            let year = $('#year-' + group).val()
            let quarterly = $('#quarterly-' + group).val()

            axios.get(url.replace(':code', code).replace(':year', year).replace(':quarterly', quarterly)).then(function (response) {
                for (const [key, value] of Object.entries(response.data)) {
                    if (key.search('_ratio') === -1) {
                        $('#' + key + '-' + group).val(value)
                        $('#' + key + '-text-' + group).html(amountText(value));
                        $('#' + key + '_ratio-' + group).html(response.data[key + '_ratio']);
                    }
                }

                toastr.success('成功')
                return true
            }).catch(function (error) {
                console.log(error)
                toastr.error('失敗')
                return false
            })
        })

        $('.input-value').change(function () {
            let group = $(this).data('group')
            let revenue = $('#revenue-' + group).val()
            let cost = $('#cost-' + group).val()
            let gross = $('#gross-' + group).val()
            let fee = $('#fee-' + group).val()
            let profit = parseInt(gross) - parseInt(fee)
            let outside = $('#outside-' + group).val()
            let other = $('#other-' + group).val()
            let tax = $('#tax-' + group).val()
            let profit_pre = parseInt(profit) + parseInt(outside)
            let profit_after = parseInt(profit_pre) - parseInt(tax)
            let profit_non = $('#profit_non-' + group).val()
            let profit_main = parseInt(profit_after) + parseInt(profit_non)
            let value = parseInt($('#value-' + group).val())

            $('#profit-' + group).val(profit)
            $('#profit_pre-' + group).val(profit_pre)
            $('#profit_after-' + group).val(profit_after)
            $('#profit_main-' + group).val(profit_main)

            $('#revenue-text-' + group).html(amountText(revenue));
            $('#cost-text-' + group).html(amountText(cost));
            $('#gross-text-' + group).html(amountText(gross));
            $('#fee-text-' + group).html(amountText(fee));
            $('#profit-text-' + group).html(amountText(profit));
            $('#outside-text-' + group).html(amountText(outside));
            $('#other-text-' + group).html(amountText(other));
            $('#profit_pre-text-' + group).html(amountText(profit_pre));
            $('#profit_after-text-' + group).html(amountText(profit_after));
            $('#profit_main-text-' + group).html(amountText(profit_main));
            $('#profit_non-text-' + group).html(amountText(profit_non));
            $('#tax-text-' + group).html(amountText(tax));
            $('#value-text-' + group).html(amountText(value));

            $('#gross_ratio-' + group).html(Math.round((gross / revenue) * 10000) / 100);
            $('#fee_ratio-' + group).html(Math.round((fee / revenue) * 10000) / 100);
            $('#profit_ratio-' + group).html(Math.round((profit / revenue) * 10000) / 100);
            $('#outside_ratio-' + group).html(Math.round((outside / revenue) * 10000) / 100);
            $('#other_ratio-' + group).html(Math.round((other / revenue) * 10000) / 100);
            $('#profit_pre_ratio-' + group).html(Math.round((profit_pre / revenue) * 10000) / 100);
            $('#profit_after_ratio-' + group).html(Math.round((profit_after / revenue) * 10000) / 100);
            $('#profit_main_ratio-' + group).html(Math.round((profit_main / revenue) * 10000) / 100);
            $('#profit_non_ratio-' + group).html(Math.round((profit_non / revenue) * 10000) / 100);
            $('#tax_ratio-' + group).html(Math.round((tax / profit_pre) * 10000) / 100);

            if (value > 0) {
                $('#eps-' + group).val(Math.round((profit_main / value) * 1000) / 100);
            }
        })
    </script>
@stop

@section('content')
    <div class="row">
        @foreach(['A', 'B', 'C'] as $group)
            <div class="col-md-4">
                <div class="card card-default ">
                    <div class="card-header">
                        <h3 class="card-title">{{ $group }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-minus"></i></button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                    class="fas fa-remove"></i></button>
                        </div>
                    </div>
                    <div class="card-body" style="display: block;">
                        <div class="row">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">代碼</span>
                                    </div>
                                    <input type="text" class="form-control " data-group="{{ $group }}"
                                           id="code-{{ $group }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary select-btn" data-group="{{ $group }}"
                                                type="button">查
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">年</span>
                                    </div>
                                    <select class="custom-select" id="year-{{ $group }}">
                                        @for($i = 0; $i <= 5; $i++)
                                            <option value="{{ $year - $i }}">{{ $year - $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">季</span>
                                    </div>
                                    <select class="custom-select" id="quarterly-{{ $group }}">
                                        <option value="1">Q1</option>
                                        <option value="2">Q2</option>
                                        <option value="3">Q3</option>
                                        <option value="4">Q4</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($column as $k => $v)
                                <div class="form-group" data-name="{{ $k }}">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ $v }}</span>
                                            @if(!in_array($k, ['eps']))
                                                <span class="input-group-text {{ $group }}" data-name="{{ $k }}"
                                                      id="{{ $k }}-text-{{ $group }}">0</span>
                                                <span class="input-group-text {{ $group }}" data-name="{{ $k }}"
                                                      id="{{ $k }}_ratio-{{ $group }}">0</span>
                                            @endif
                                        </div>
                                        <input type="text" class="form-control {{ $group }} input-value"
                                               data-group="{{ $group }}"
                                               data-name="{{ $k }}" id="{{ $k }}-{{ $group }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop
