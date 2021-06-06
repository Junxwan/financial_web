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

            // 全年營收
            total = 0;
            $('.input-group-q-r').find('input').each(function () {
                v = parseInt($(this).val())
                if (!isNaN(v)) {
                    total += v;
                }
            })

            $('#year_revenue').val(total)

            // 全年營收text
            $('#year_revenue_s').html(roundText(total))

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

        // 本益比
        $('#pe').on('change', reloadPriceF)

        // 代碼
        $('#code').on('change', function () {
            var url = '{{ route("stock.search", ":code") }}';
            axios.get(url.replace(':code', $(this).val())).then(function (response) {
                $('#name').val(response.data.name)
                $('#end_capital').val(response.data.capital)
                toastr.success('查訊成功')
            }).catch(function (error) {
                toastr.error('查無資料')
            })
        })

        // 新增
        $('#create-btn').click(function () {
            var code = $('#code').val()
            var date = $('#date').val()
            var open_date = $('#open_date').val()
            var pe = $('#pe').val()

            var body = {
                code: code,
                date: date,
                title: $('#title').val(),
                action: $('#action').val(),
                market_eps_f: $('#market_eps_f').val(),
                open_date: open_date,
                pe: pe,
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

            if (code === '' || name === '') {
                toastr.error("沒有個股資料")
                return
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

        // 更新
        $('#update-btn').click(function () {

        })

        // 刷新
        $('#refresh-btn').click(function () {

        })

        // 重算
        $('#reload-btn').click(function () {
            reloadAll(1)
            reloadAll(2)
            reloadAll(3)
            reloadAll(4)
            readTotal()
            reloadPriceF()
        })

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
                                <span class="input-group-text">財報公佈</span>
                            </div>
                            <input type="text" class="form-control" id="open_date">
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
                                <span class="input-group-text">預估股價(四季)</span>
                            </div>
                            <input type="text" class="form-control" id="price_f_4">
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
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="create-btn">
                                新增
                            </button>
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="update-btn">
                                更新
                            </button>
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="refresh-btn">
                                刷新
                            </button>
                            <button type="button" class="btn btn-block bg-gradient-secondary btn-lg" id="reload-btn">
                                重算
                            </button>
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
                <div class="col-md-3">
                    @for($i = 1; $i <= 4; $i++)
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Q{{ $i }}營收</span>
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
                <div class="col-md-2">
                    @for($i = 1; $i <= 4; $i++)
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Q{{ $i }} EPS</span>
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
                <div class="col-md-3">
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
                <div class="col-md-4">
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
                                        <span class="input-group-text">{{ $i+$a }}月</span>
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
                                    <span class="input-group-text">Q{{ ($a+3)/3 }}</span>
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
                                                <span class="input-group-text">Q{{ $i }}</span>
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
