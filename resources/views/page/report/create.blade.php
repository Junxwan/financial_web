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

        .ck-editor__editable {
            min-width: 100%;
            min-height: 200px;
        }

        .ck-content {
            color: #343a40;
        }

        .btn-tool {
            color: #0c0c0c;
        }

    </style>
@stop

@section('js')
    <script src="{{ asset('js/ckeditor.js') }}"></script>
    <script src="{{ asset('js/ckeditor.translations.zh.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        ClassicEditor.create(document.querySelector('#editor-desc'), {
            language: 'zh'
        }).then(editor => {
            window.editor_desc = editor;
        }).catch(err => {
            console.error(err.stack);
        });

        ClassicEditor.create(document.querySelector('#editor-total'), {
            language: 'zh'
        }).then(editor => {
            window.editor_total = editor;
        }).catch(err => {
            console.error(err.stack);
        });

        ClassicEditor.create(document.querySelector('#editor-revenue'), {
            language: 'zh'
        }).then(editor => {
            window.editor_revenue = editor;
        }).catch(err => {
            console.error(err.stack);
        });

        @foreach($qs as $v)
        @foreach($v as $a)
        @if(isset($a['editor']) && $a['editor'])
        ClassicEditor.create(document.querySelector('#editor-{{ $a['id'] }}'), {
            language: 'zh'
        }).then(editor => {
            window.editor_{{ $a['id'] }} = editor;
        }).catch(err => {
            console.error(err.stack);
        });
        @endif
        @endforeach
        @endforeach

        $('#date').val((new Date()).toLocaleDateString())

        // ?????????
        $('.form-group-month input').change(function () {
            computeRevenue()
            compute()
        })

        // ?????? ?????? ?????? ?????? ????????? ?????? ?????? ?????? ????????? ?????????
        $('.form-group-quarterly input').change(function () {
            compute()
        })

        // ??????
        $("#start_capital, #end_capital").change(function () {
            $('#' + $(this).attr('id') + '_text').html(amountText($(this).val()))
            compute()
        })

        // ??????Q???
        $('.checkbox-quarterly').click(function () {
            q = $(this).data('q')
            $('.checkbox-quarterly').each(function () {
                if ($(this).data('q') !== q) {
                    $(this).prop("checked", false)
                }
            })

            lockQuarterly()
        })

        // ?????????
        $('.checkbox-month').click(function () {
            m = $(this).data('m')
            $('.checkbox-month').each(function () {
                if ($(this).data('m') !== m) {
                    $(this).prop("checked", false)
                }
            })

            lockMonth()
        })

        // ??????
        $('#code').change(function () {
            var url = '{{ route("stock.search", ":code") }}';
            axios.get(url.replace(':code', $(this).val())).then(function (response) {
                $('#name').val(response.data.name)
                $('#eps3_sum').val(response.data.eps3_sum)
                $('#eps4_sum').val(response.data.eps4_sum)
                $('#start_capital').val(Math.round(response.data.start_capital / 1000))
                $('#start_capital_text').html(amountText(response.data.start_capital))
                $('#end_capital').val(Math.round(response.data.capital / 1000))
                $('#end_capital_text').html(amountText(response.data.capital))
                toastr.success('????????????')
            }).catch(function (error) {
                toastr.error('????????????')
            })
        })

        // ??????
        $('#create-btn').click(function () {
            body = getData()
            if (body.code === '' || body.name === '') {
                toastr.error("??????????????????")
                return
            }

            axios.post('{{ route("report.create") }}', body).then(function (response) {
                if (response.data.result) {
                    toastr.success('????????????')
                } else {
                    toastr.error('????????????')
                }
            }).catch(function (error) {
                toastr.error('????????????')
            })
        })

        @if(isset($id))
        // ??????
        $('#update-btn').click(function () {
            body = getData()

            if (body.code === '' || body.name === '') {
                toastr.error("??????????????????")
                return
            }

            var url = '{{ route("report.update", ":id") }}';
            axios.put(url.replace(':id', " {{ $id }}"), body).then(function (response) {
                if (response.data.result) {
                    toastr.success('????????????')
                } else {
                    toastr.error('????????????')
                }
            }).catch(function (error) {
                toastr.error('????????????')
            })
        })
        @endif

        // ??????
        $('#refresh-btn').click(function () {
            location.reload()
        })

        // ??????
        $('#reload-btn').click(function () {
            computeRevenue()
            compute()
        })

        // ?????????
        $('#get-btn').click(function () {
            url = '{{ route("revenue.year", ['code' => ':code', 'year' => ':year']) }}'
            url = url.replace(':code', $('#code').val()).replace(':year', $('#date').val().slice(0, 4))

            axios.get(url).then(function (response) {
                response.data.forEach(function (v) {
                    setRevenue(v.month, Math.round(v.value / 1000))
                })

                $('#checkbox_month_' + response.data[0].month).prop("checked", true);
                lockMonth()

                toastr.success('?????????????????????')

                url = '{{ route("profit.year", ['code' => ':code', 'year' => ':year']) }}'
                url = url.replace(':code', $('#code').val()).replace(':year', $('#date').val().slice(0, 4))

                axios.get(url).then(function (response) {
                    response.data.forEach(function (v) {
                        [
                            'revenue', 'gross', 'fee', 'outside', 'other', 'tax', 'profit_non', 'profit',
                            'profit_pre', 'profit_after', 'profit_main', 'eps'
                        ].forEach(function (name) {
                            if (name === 'eps') {
                                setValue('#' + name, v.quarterly, Math.round(v[name] * 100) / 100)
                            } else {
                                setValue('#' + name, v.quarterly, Math.round(v[name] / 1000))
                            }
                        })
                    })

                    $('#checkbox_quarterly_' + response.data[0].quarterly).prop("checked", true);
                    lockQuarterly()

                    toastr.success('???????????????????????????')
                    $('#reload-btn').click()
                }).catch(function (error) {
                    toastr.error('???????????????????????????')
                })

            }).catch(function (error) {
                toastr.error('?????????????????????')
            })
        })

        @if(isset($data))
        var data = @json($data);

        $('#title').val(data.title)
        $('#code').val(data.code)
        $('#name').val(data.name)
        $('#date').val(data.date)
        $('#value').val(data.value)
        $('#action').val(data.action)
        $('#eps3_sum').val(data.eps3_sum)
        $('#eps4_sum').val(data.eps4_sum)
        $('#market_eps_f').val(data.market_eps_f)
        $('#pe').val(data.pe)
        $('#evaluate').val(data.evaluate)
        $('#price_f').val(data.price_f)
        $('#start_capital').val(data.start_stock)
        $('#start_capital_text').html(amountText(data.start_stock))
        $('#end_capital').val(data.capital)
        $('#end_capital_text').html(amountText(data.capital))

        $('#editor-desc').html(data.desc)
        $('#editor-total').html(data.desc_total)
        $('#editor-revenue').html(data.desc_revenue)
        $('#editor-gross').html(data.desc_gross)
        $('#editor-fee').html(data.desc_fee)
        $('#editor-outside').html(data.desc_outside)
        $('#editor-other').html(data.desc_other)
        $('#editor-tax').html(data.desc_tax)
        $('#editor-profit_non').html(data.desc_non)

        // ?????????
        for (var i = 1; i <= 12; i++) {
            setRevenue(i, data['revenue_month_' + i])
        }

        $('.form-group-quarterly').each(function () {
            for (var i = 1; i <= 4; i++) {
                name = $(this).data('name')
                v = data[name + '_' + i]

                if (name === 'eps') {
                    v = Math.round(v * 100) / 100
                }

                setValue('#' + name, i, v)
            }
        })

        $('#checkbox_month_' + data.month).prop("checked", true);
        $('#checkbox_quarterly_' + data.quarterly).prop("checked", true);
        lockMonth()
        lockQuarterly()

        $('#reload-btn').click()

        @endif

        function getData() {
            var body = {
                code: $('#code').val(),
                date: $('#date').val(),
                quarterly: getQuarterly(),
                month: getMonth(),
                title: $('#title').val(),
                action: $('#action').val(),
                market_eps_f: $('#market_eps_f').val(),
                price_f: $('#price_f').val(),
                pe: $('#pe').val(),
                evaluate: $('#evaluate').val(),
                value: $('#value').val(),
                revenue: {},
                revenue_month: {},
                gross: {},
                fee: {},
                outside: {},
                other: {},
                tax: {},
                profit_non: {},
                profit: {},
                profit_pre: {},
                profit_after: {},
                profit_main: {},
                eps: {},
                desc: window.editor_desc.getData(),
                desc_total: window.editor_total.getData(),
                desc_revenue: window.editor_revenue.getData(),
                desc_gross: window.editor_gross.getData(),
                desc_fee: window.editor_fee.getData(),
                desc_outside: window.editor_outside.getData(),
                desc_other: window.editor_other.getData(),
                desc_tax: window.editor_tax.getData(),
                desc_non: window.editor_profit_non.getData(),
            }

            $('.form-group-quarterly input, .form-group-month input').each(function () {
                v = $(this).val()
                if (isNaN(v) || v === '') {
                    v = 0
                }

                body[$(this).data('name')][$(this).attr('id')] = v
            })

            return body
        }
    </script>
@stop

@section('content')
    <input type="hidden" id="id" value="">
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">??????</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                            </div>
                            <input type="text" class="form-control" id="title">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 checkbox-quarterly-group">
                    @for($i = 1; $i <= 4; $i++)
                        <label>Q{{ $i }}</label>
                        <input type="checkbox" class="checkbox-quarterly" id="checkbox_quarterly_{{ $i }}"
                               @if(isset($data) && $data['quarterly'] == $i) checked @endif
                               data-q="{{ $i }}">
                    @endfor
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                            </div>
                            <input type="text" class="form-control" id="code" @if(isset($id)) readonly @endif>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                            </div>
                            <input type="text" class="form-control" id="name" @if(isset($id)) readonly @endif>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" class="form-control" id="date" placeholder="yyyy-mm-dd">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                            </div>
                            <input type="text" class="form-control" id="value">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                            </div>
                            <select class="custom-select" id="action" @if(isset($id)) disabled @endif>
                                <option value="1">???</option>
                                <option value="0">???</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">?????????EPS</span>
                            </div>
                            <input type="text" class="form-control" id="eps3_sum" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">?????????EPS</span>
                            </div>
                            <input type="text" class="form-control" id="eps4_sum" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">????????????</span>
                            </div>
                            <input type="text" class="form-control" id="market_eps_f">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">PE</span>
                            </div>
                            <input type="text" class="form-control" id="pe">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                            </div>
                            <select class="custom-select" id="evaluate">
                                <option value="1">??????</option>
                                <option value="2">??????</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">????????????</span>
                                <span class="input-group-text" id="start_capital_text">0???</span>
                            </div>
                            <input type="text" class="form-control" id="start_capital">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">????????????</span>
                                <span class="input-group-text" id="end_capital_text">0???</span>
                            </div>
                            <input type="text" class="form-control" id="end_capital">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">????????????</span>
                            </div>
                            <input type="text" class="form-control" id="price_f">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                                    id="create-btn">
                                ??????
                            </button>
                            @if(isset($id))
                                <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                                        id="update-btn">
                                    ??????
                                </button>
                            @endif
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="refresh-btn">
                                ??????
                            </button>
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="reload-btn">
                                ??????
                            </button>
                            @if(!isset($id))
                                <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="get-btn">
                                    ?????????
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    @for($i = 1; $i <= 12; $i++)
                        <input type="checkbox" class="checkbox-month" id="checkbox_month_{{ $i }}" data-m="{{ $i }}"
                               @if(isset($data) && $data['month'] == $i) checked @endif>
                        <label>{{ $i }}???</label>
                        @if($i%2 == 0)
                        </br>
                        @endif
                    @endfor
                </div>
            </div>
            <div class="row">
                <div class="card card-default collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">??????</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i></button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                    class="fas fa-remove"></i></button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <div class="row">
                            <div class="form-control" id="editor-desc"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">??????</h3>
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
                    @for($i = 1; $i <= 4; $i++)
                        <div class="form-group form-group-quarterly form-group-revenue" data-name="revenue">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text span-quarterly" data-q="{{ $i }}">Q{{ $i }}??????</span>
                                    <span class="input-group-text" id="revenue_{{ $i }}_text">0???</span>
                                </div>
                                <input type="text" class="form-control" data-name="revenue" data-q="{{ $i }}"
                                       id="revenue_{{ $i }}"
                                       readonly>
                            </div>
                        </div>
                    @endfor
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">?????????</span>
                                <span class="input-group-text" id="revenue_text">0???</span>
                            </div>
                            <input type="text" class="form-control" id="revenue">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    @for($i = 1; $i <= 4; $i++)
                        <div class="form-group form-group-quarterly form-group-eps" data-name="eps">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text span-quarterly" data-q="{{ $i }}">Q{{ $i }} EPS</span>
                                </div>
                                <input type="text" class="form-control" data-name="eps" data-q="{{ $i }}"
                                       id="eps_{{ $i }}">
                            </div>
                        </div>
                    @endfor
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">EPS</span>
                            </div>
                            <input type="text" class="form-control" id="eps" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                                <span class="input-group-text" id="gross_text">0???</span>
                                <span class="input-group-text" id="gross_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="gross" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                                <span class="input-group-text" id="fee_text">0???</span>
                                <span class="input-group-text" id="fee_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="fee" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                                <span class="input-group-text" id="outside_text">0???</span>
                                <span class="input-group-text" id="outside_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="outside" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                                <span class="input-group-text" id="other_text">0???</span>
                                <span class="input-group-text" id="other_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="other" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">???</span>
                                <span class="input-group-text" id="tax_text">0???</span>
                                <span class="input-group-text" id="tax_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="tax" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                                <span class="input-group-text" id="profit_text">0???</span>
                                <span class="input-group-text" id="profit_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profit" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                                <span class="input-group-text" id="profit_pre_text">0???</span>
                                <span class="input-group-text" id="profit_pre_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profit_pre" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">??????</span>
                                <span class="input-group-text" id="profit_after_text">0???</span>
                                <span class="input-group-text" id="profit_after_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profit_after" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">?????????</span>
                                <span class="input-group-text" id="profit_non_text">0???</span>
                                <span class="input-group-text" id="profit_non_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profit_non" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">?????????</span>
                                <span class="input-group-text" id="profit_main_text">0???</span>
                                <span class="input-group-text" id="profit_main_ratio">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profit_main" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="card card-default collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">??????</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i></button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                    class="fas fa-remove"></i></button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <div class="row">
                            <div class="form-control" id="editor-total">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">?????????(??????)</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                @for ($a = 0; $a < 12; $a+=3)
                    <div class="col-md-3">
                        @for ($i = 1; $i <= 3; $i++)
                            <div class="form-group form-group-month">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text span-month" data-m={{ $i+$a }}
                                            data-q="{{ ($a+3)/3 }}">{{ $i+$a }}???</span>
                                        <span class="input-group-text" id="revenue_month_{{ $i+$a }}_text">0</span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           data-m="{{ $i+$a }}"
                                           data-q="{{ ($a+3)/3 }}"
                                           data-name="revenue_month"
                                           id="revenue_month_{{ $i+$a }}"
                                    >
                                </div>
                            </div>
                        @endfor
                    </div>
                @endfor
            </div>
            <div class="row">
                <div class="card card-default collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">??????</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i></button>
                            <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                    class="fas fa-remove"></i></button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <div class="row">
                            <div class="form-control" id="editor-revenue">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($qs as $v)
        <div class="row">
            @foreach($v as $a)
                <div class="col-md-{{ 12/count($v) }}">
                    <div class="card card-default ">
                        <div class="card-header">
                            <h3 class="card-title">@if(isset($a['name'])) {{ $a['name'] }} @endif</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-minus"></i></button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                        class="fas fa-remove"></i></button>
                            </div>
                        </div>
                        <div class="card-body" style="display: block;">
                            <div class="row">
                                @for ($i = 1; $i <= 4; $i++)
                                    @if(count($a) == 0 )
                                        @continue
                                    @endif
                                    <div class="form-group form-group-quarterly form-group-{{ $a['id'] }}"
                                         data-name="{{ $a['id'] }}">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text span-quarterly"
                                                      data-q="{{ $i }}">Q{{ $i }}</span>
                                                <span class="input-group-text"
                                                      id="{{ $a['id'] . '_' . $i }}_text">0</span>
                                                <span class="input-group-text"
                                                      id="{{ $a['id'] . '_' . $i }}_ratio">0</span>
                                            </div>
                                            <input type="text"
                                                   class="form-control"
                                                   data-name="{{ $a['id'] }}"
                                                   data-q="{{ $i }}"
                                                   @if(isset($a['readonly']) && $a['readonly']) readonly @endif
                                                   id="{{ $a['id'] . '_' . $i }}"
                                            >
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            @if(isset($a['editor']) && $a['editor'])
                                <div class="row">
                                    <div class="card card-default collapsed-card">
                                        <div class="card-header">
                                            <h3 class="card-title">??????</h3>
                                            <div class="card-tools">
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                    <i
                                                        class="fas fa-plus"></i></button>
                                                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                                                        class="fas fa-remove"></i></button>
                                            </div>
                                        </div>
                                        <div class="card-body" style="display: none;">
                                            <div class="row">
                                                <div class="form-control" id="editor-{{ $a['id'] }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@stop
