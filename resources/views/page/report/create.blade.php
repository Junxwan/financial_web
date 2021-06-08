@extends('page')

@section('css')
    <style>
        .container-fluid .card-header {
            background-color: #888f95;
            color: #0c0c0c;
        }

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

        // 月營收
        $('.form-group-r').on('change', 'input', function () {
            var m = $(this).data('m')
            var q = $(this).data('q')

            setRevenueText(m, $(this).val())
            reloadRevenueQ(q)
            reloadAll(q)
            readTotal()
        })

        // 毛利 費用 業外 其他 所得稅 利益 稅前 稅後 非控制
        $('.form-group-q').on('change', 'input', function () {
            var q = $(this).data('q')

            reload('#' + $(this).attr('id'), q, $(this).val())
            reloadAll(q)
            readTotal()
        })

        // 期初股本
        $('#start_capital').on('change', function () {
            $('#start_capital_s').html(roundText($(this).val()))
            readTotal()
        })

        // 期末股本
        $('#end_capital').on('change', function () {
            $('#end_capital_s').html(roundText($(this).val()))
            readTotal()
        })

        // 代碼
        $('#code').on('change', function () {
            var url = '{{ route("stock.search", ":code") }}';
            axios.get(url.replace(':code', $(this).val())).then(function (response) {
                $('#name').val(response.data.name)
                $('#s3q_eps').val(response.data.eps_3)
                $('#s4q_eps').val(response.data.eps_4)
                $('#end_capital').val(Math.round(response.data.capital / 1000))
                $('#start_capital').val(Math.round(response.data.start_capital / 1000))
                $('#end_capital_s').html(roundText(response.data.capital / 1000))
                $('#start_capital_s').html(roundText(response.data.start_capital / 1000))
                toastr.success('查訊成功')
            }).catch(function (error) {
                toastr.error('查無資料')
            })
        })

        // 新增
        $('#create-btn').click(function () {
            body = getBody()
            if (body.code === '' || body.name === '') {
                toastr.error("沒有個股資料")
                return
            }

            axios.post('{{ route("report.create") }}', body).then(function (response) {
                if (response.data.result) {
                    toastr.success('新增成功')
                } else {
                    toastr.error('新增失敗')
                }
            }).catch(function (error) {
                toastr.error('新增失敗')
            })
        })

        @if(isset($id))
        // 更新
        $('#update-btn').click(function () {
            body = getBody()

            if (body.code === '' || body.name === '') {
                toastr.error("沒有個股資料")
                return
            }

            var url = '{{ route("report.update", ":id") }}';
            axios.put(url.replace(':id', " {{ $id }}"), body).then(function (response) {
                if (response.data.result) {
                    toastr.success('更新成功')
                } else {
                    toastr.error('更新失敗')
                }
            }).catch(function (error) {
                toastr.error('更新失敗')
            })
        })
        @endif

        // 刷新
        $('#refresh-btn').click(function () {
            location.reload()
        })

        // 重算
        $('#reload-btn').click(function () {
            reloadAll(1)
            reloadAll(2)
            reloadAll(3)
            reloadAll(4)
            readTotal()
        })

        // 當前Q幾
        $('.checkbox-q').click(function () {
            $('.checkbox-q-group :checkbox').each(function () {
                $(this).prop("checked", false);
            })

            $(this).prop("checked", true);
            reloadSSpan($(this).data('q'))
        })

        // 當前月
        $('.checkbox-m').click(function () {
            $('.checkbox-m').each(function () {
                $(this).prop("checked", false);
            })

            $(this).prop("checked", true);

            reloadMSpan($(this).data('m'))
        })

        @if(isset($data))
        var data = @json($data)

        $('#title').val(data.title)
        $('#code').val(data.code)
        $('#name').val(data.name)
        $('#date').val(data.date)
        $('#value').val(data.value)
        $('#action').val(data.action)
        $('#market_eps_f').val(data.market_eps_f)
        $('#end_capital').val(data.capital)
        $('#end_capital_s').html(roundText(data.capital))
        $('#start_capital').val(data.start_stock)
        $('#start_capital_s').html(roundText(data.start_stock))
        $('#pe').val(data.pe)
        $('#id').val(data.id)
        $('#s3q_eps').val(data.eps_3)
        $('#s4q_eps').val(data.eps_4)
        $('#price_f').val(data.price_f)
        $('#evaluate').val(data.evaluate)

        console.log(data)

        // 月營收
        for (var i = 1; i <= 12; i++) {
            v = data['revenue_' + i]
            setRevenueText(i, v)

            if (v > 0) {
                $('#revenue_' + i).val(v)
            }

            if ((i % 3) == 0) {
                reloadRevenueQ(i / 3)
            }
        }

        for (var i = 1; i <= 4; i++) {
            let name = ['gross', 'fee', 'outside', 'tax', 'non', 'eps']

            name.forEach(function (n) {
                v = data[n + '_' + i]

                if (v > 0) {
                    $('#q_' + n + '_' + i).val(data[n + '_' + i])
                }
            })
        }

        $('#editor-desc').html(data.desc)
        $('#editor-total').html(data.desc_total)
        $('#editor-revenue').html(data.desc_revenue)
        $('#editor-gross').html(data.desc_gross)
        $('#editor-fee').html(data.desc_fee)
        $('#editor-outside').html(data.desc_outside)
        $('#editor-other').html(data.desc_other)
        $('#editor-tax').html(data.desc_tax)
        $('#editor-non').html(data.desc_non)

        document.getElementById('reload-btn').click();

        reloadMSpan(data.month)
        reloadSSpan(data.season)
        @endif

        function getBody() {
            var code = $('#code').val()
            var date = $('#date').val()
            var pe = $('#pe').val()
            var season = 0
            var month = 0


            $('.checkbox-q').each(function () {
                if ($(this).is(":checked")) {
                    season = $(this).data('q')
                }
            })

            $('.checkbox-m').each(function () {
                if ($(this).is(":checked")) {
                    month = $(this).data('m')
                }
            })

            var body = {
                code: code,
                date: date,
                season: season,
                month: month,
                title: $('#title').val(),
                action: $('#action').val(),
                market_eps_f: $('#market_eps_f').val(),
                price_f: $('#price_f').val(),
                pe: pe,
                evaluate: $('#evaluate').val(),
                value: $('#value').val(),
                revenue: {},
                gross: {},
                fee: {},
                outside: {},
                other: {},
                tax: {},
                non: {},
                profit: {},
                profitB: {},
                profitA: {},
                main: {},
                eps: {
                    eps_1: getValue('#eps_q_1', 0),
                    eps_2: getValue('#eps_q_2', 0),
                    eps_3: getValue('#eps_q_3', 0),
                    eps_4: getValue('#eps_q_4', 0),
                },
                desc: window.editor_desc.getData(),
                desc_total: window.editor_total.getData(),
                desc_revenue: window.editor_revenue.getData(),
                desc_gross: window.editor_gross.getData(),
                desc_fee: window.editor_fee.getData(),
                desc_outside: window.editor_outside.getData(),
                desc_other: window.editor_other.getData(),
                desc_tax: window.editor_tax.getData(),
                desc_non: window.editor_non.getData(),
            }

            $('.form-group-r').find('input').each(function () {
                v = parseInt($(this).val())
                if (!isNaN(v)) {
                    body['revenue'][$(this).attr('id')] = v
                }
            })

            $('.form-group-q').find('input').each(function () {
                v = parseInt($(this).val())
                if (!isNaN(v)) {
                    body[$(this).data('name')][$(this).attr('id')] = v
                }
            })

            return body
        }

        function reloadMSpan(m) {
            $('.span-m').each(function () {
                if (m >= $(this).data('m')) {
                    $(this).addClass('span-selected')
                } else {
                    $(this).removeClass('span-selected')
                }
            })
        }

        function reloadSSpan(q) {
            $('.span-q').each(function () {
                if (q >= $(this).data('q')) {
                    $(this).addClass('span-selected')
                } else {
                    $(this).removeClass('span-selected')
                }
            })
        }

    </script>
@stop

@section('content')
    <input type="hidden" id="id" value="">
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
                <div class="col-md-8">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">標題</span>
                            </div>
                            <input type="text" class="form-control" id="title">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 checkbox-q-group">
                    @for($i = 1; $i <= 4; $i++)
                        <label>Q{{ $i }}</label>
                        <input type="checkbox" class="checkbox-q"
                               @if(isset($data) && $data['season'] == $i) checked @endif
                               data-q="{{ $i }}">
                    @endfor
                </div>
            </div>
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
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">名稱</span>
                            </div>
                            <input type="text" class="form-control" id="name">
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
                                <span class="input-group-text">淨值</span>
                            </div>
                            <input type="text" class="form-control" id="value">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">動作</span>
                            </div>
                            <select class="custom-select" id="action">
                                <option value="1">多</option>
                                <option value="0">空</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">近三季EPS</span>
                            </div>
                            <input type="text" class="form-control" id="s3q_eps" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">近四季EPS</span>
                            </div>
                            <input type="text" class="form-control" id="s4q_eps" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">市場財測</span>
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
                                <span class="input-group-text">評估</span>
                            </div>
                            <select class="custom-select" id="evaluate">
                                <option value="1">預期</option>
                                <option value="2">預估</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">期初股本</span>
                                <span class="input-group-text" id="start_capital_s">0億</span>
                            </div>
                            <input type="text" class="form-control" id="start_capital">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">期末股本</span>
                                <span class="input-group-text" id="end_capital_s">0億</span>
                            </div>
                            <input type="text" class="form-control" id="end_capital">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">預估股價</span>
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
                                新增
                            </button>
                            @if(isset($id))
                                <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                                        id="update-btn">
                                    更新
                                </button>
                            @endif
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="refresh-btn">
                                刷新
                            </button>
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="reload-btn">
                                重算
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    @for($i = 1; $i <= 12; $i++)
                        <input type="checkbox" class="checkbox-m" data-m="{{ $i }}"
                               @if(isset($data) && $data['month'] == $i) checked @endif>
                        <label>{{ $i }}月</label>
                        @if($i%2 == 0)
                        </br>
                        @endif
                    @endfor
                </div>
            </div>
            <div class="row">
                <div class="card card-default collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">說明</h3>
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
            <h3 class="card-title">總結</h3>
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
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text span-q" data-q="{{ $i }}">Q{{ $i }}營收</span>
                                    <span class="input-group-text" id="r_q_{{ $i }}_s">0億</span>
                                </div>
                                <input type="text" class="form-control" id="r_q_{{ $i }}" readonly>
                            </div>
                        </div>
                    @endfor
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">年營收</span>
                                <span class="input-group-text" id="revenue_s">0億</span>
                            </div>
                            <input type="text" class="form-control" id="revenue">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    @for($i = 1; $i <= 4; $i++)
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text span-q" data-q="{{ $i }}">Q{{ $i }} EPS</span>
                                </div>
                                <input type="text" class="form-control" id="eps_q_{{ $i }}" readonly>
                            </div>
                        </div>
                    @endfor
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">EPS</span>
                            </div>
                            <input type="text" class="form-control" id="eps">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">毛利</span>
                                <span class="input-group-text" id="gross_t_s">0億</span>
                                <span class="input-group-text" id="gross_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="gross" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">費用</span>
                                <span class="input-group-text" id="fee_t_s">0億</span>
                                <span class="input-group-text" id="fee_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="fee" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">業外</span>
                                <span class="input-group-text" id="outside_t_s">0億</span>
                                <span class="input-group-text" id="outside_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="outside" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">其他</span>
                                <span class="input-group-text" id="other_t_s">0億</span>
                                <span class="input-group-text" id="other_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="other" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">稅</span>
                                <span class="input-group-text" id="tax_t_s">0億</span>
                                <span class="input-group-text" id="tax_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="tax" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">利益</span>
                                <span class="input-group-text" id="profit_t_s">0億</span>
                                <span class="input-group-text" id="profit_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profit" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">稅前</span>
                                <span class="input-group-text" id="profitB_t_s">0億</span>
                                <span class="input-group-text" id="profitB_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profitB" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">稅後</span>
                                <span class="input-group-text" id="profitA_t_s">0億</span>
                                <span class="input-group-text" id="profitA_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profitA" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">非控制</span>
                                <span class="input-group-text" id="non_t_s">0億</span>
                                <span class="input-group-text" id="non_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="non" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">母權益</span>
                                <span class="input-group-text" id="main_t_s">0億</span>
                                <span class="input-group-text" id="main_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="main" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="card card-default collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">說明</h3>
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
            <h3 class="card-title">月營收(百萬)</h3>
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
                            <div class="form-group form-group-r">
                                <div class="input-group input-group-q-{{ ($a+3)/3 }}">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text span-m" data-m={{ $i+$a }}
                                            data-q="{{ ($a+3)/3 }}">{{ $i+$a }}月</span>
                                        <span class="input-group-text" id="revenue_{{ $i+$a }}_s">0</span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           data-m="{{ $i+$a }}"
                                           data-q="{{ ($a+3)/3 }}"
                                           id="revenue_{{ $i+$a }}"
                                    >
                                </div>
                            </div>
                        @endfor
                        <div class="form-group">
                            <div class="input-group input-group-q-r">
                                <div class="input-group-prepend">
                                    <span class="input-group-text span-q" data-q="{{ ($a+3)/3 }}">Q{{ ($a+3)/3 }}</span>
                                    <span class="input-group-text" id="q_revenue_{{ ($a+3)/3 }}_s">0億</span>
                                </div>
                                <input type="number"
                                       class="form-control"
                                       data-q="{{ ($a+3)/3 }}"
                                       id="q_revenue_{{ ($a+3)/3 }}"
                                       value="0"
                                >
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="row">
                <div class="card card-default collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">說明</h3>
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
                                    <div class="form-group form-group-q form-group-{{ $a['id'] }}">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text span-q" data-q="{{ $i }}">Q{{ $i }}</span>
                                                <span class="input-group-text"
                                                      id="q_{{ $a['id'] . '_' . $i }}_s">0</span>
                                                <span class="input-group-text"
                                                      id="q_{{ $a['id'] . '_' . $i }}_b">0</span>
                                            </div>
                                            <input type="text"
                                                   class="form-control"
                                                   data-name="{{ $a['id'] }}"
                                                   data-q="{{ $i }}"
                                                   @if(isset($a['readonly']) && $a['readonly']) readonly @endif
                                                   id="q_{{ $a['id'] . '_' . $i }}"
                                            >
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            @if(isset($a['editor']) && $a['editor'])
                                <div class="row">
                                    <div class="card card-default collapsed-card">
                                        <div class="card-header">
                                            <h3 class="card-title">說明</h3>
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
