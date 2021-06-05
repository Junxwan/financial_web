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
        ClassicEditor.create(document.querySelector('#editor-base'), {
            language: 'zh'
        }).then(editor => {
            window.editor_base = editor;
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

        @foreach($q as $v)
        @foreach($v as $a)
        @if(isset($a['editor']))
        ClassicEditor.create(document.querySelector('#editor-{{ $a['editor'] }}'), {
            language: 'zh'
        }).then(editor => {
            window.editor_{{ $a['editor'] }} = editor;
        }).catch(err => {
            console.error(err.stack);
        });
        @endif
        @endforeach
        @endforeach

        // 某季營收
        function getRevenueQ(q) {
            return $('#qr' + q).val()
        }

        // 某季毛利
        function getGross(q) {
            return $('#qg' + q).val()
        }

        // 某季費用
        function getCost(q) {
            return $('#qc' + q).val()
        }

        // 某季業外
        function getOutside(q) {
            return $('#qo' + q).val()
        }

        // 某季其他收益
        function getOther(q) {
            return $('#qi' + q).val()
        }

        // 某季所得稅
        function getTax(q) {
            return $('#qt' + q).val()
        }

        // 某季利益
        function getProfit(q) {
            return $('#qp' + q).val()
        }

        // 某季稅前
        function getProfitB(q) {
            return $('#qpb' + q).val()
        }

        // 某季稅後
        function getProfitA(q) {
            return $('#qpa' + q).val()
        }

        // 某季非控制權益
        function getNon(q) {
            return $('#qn' + q).val()
        }

        // 某季母控制權益
        function getMain(q) {
            return $('#qm' + q).val()
        }

        // 佔季營收比例
        function revenueQProportion(q, v) {
            return Math.round((v / getRevenueQ(q)) * 10000) / 100
        }

        // 設置月營收text
        function setRevenueText(m, v) {
            $('#r' + m + '_s').html(roundText(v))
        }

        // 設置各項值與Text
        function reload(name, q, v) {
            if (isNaN(v) || v === '' || v === 0) {
                return
            }

            $(name).val(v)
            $(name + '_s').html(roundText(v));
            $(name + '_b').html(revenueQProportion(q, v) + '%');
        }

        // 重整季營收
        function reloadRevenueQ(q) {
            var total = 0;
            $('.input-group-q-' + q).find('input').each(function () {
                v = parseInt($(this).val())
                if (!isNaN(v)) {
                    total += v;
                }
            })

            reload('#qr' + q, q, total)
        }

        // 重整毛利
        function reloadGross(q) {
            reload('#qg' + q, q, getGross(q))
        }

        // 重整費用
        function reloadCost(q) {
            reload('#qc' + q, q, getCost(q))
        }

        // 重整業外
        function reloadOutside(q) {
            reload('#qo' + q, q, getOutside(q))
        }

        // 重整其他收益
        function reloadOther(q) {
            reload('#qi' + q, q, getOther(q))
        }

        // 設置利益
        function reloadProfit(q) {
            reload('#qp' + q, q, getGross(q) - getCost(q))
        }

        // 重整稅前
        function reloadProfitB(q) {
            reload('#qpb' + q, q, parseInt(getProfit(q)) + parseInt(getOutside(q)) + parseInt(getOther(q)))
        }

        // 重整稅後
        function reloadProfitA(q) {
            reload('#qpa' + q, q, getProfitB(q) - getTax(q))
        }

        // 重整季所得稅與Text
        function reloadTax(q) {
            name = '#qt' + q
            v = getTax(q)

            if (v === '' || v === 0) {
                return
            }

            reload(name, q, v)
            $(name + '_b').html((Math.round((v / getProfitB(q)) * 10000) / 100) + '%');
        }

        // 重整非控制權益
        function reloadNon(q) {
            reload('#qn' + q, q, getNon(q))
        }

        // 重整母控制權益
        function reloadMain(q) {
            reload('#qm' + q, q, getProfitA(q) - getNon(q))
        }

        // 重整預估價格
        function reloadPriceF() {
            pe = $('#pe').val()
            if (pe === '' || pe === 0 || isNaN(pe)) {
                return
            }

            eps = $('#eps').val()
            if (eps === '' || eps === 0 || isNaN(eps)) {
                return
            }

            $('#pricef').val(Math.round((pe * eps) * 100) / 100)
        }

        // 整個重計算
        function reloadAll(q) {
            reloadGross(q)
            reloadCost(q)
            reloadOutside(q)
            reloadProfit(q)
            reloadProfitB(q)
            reloadTax(q)
            reloadProfitA(q)
            reloadNon(q)
            reloadMain(q)
            readTotal()
        }

        // 整理總結
        function readTotal() {
            var totalRevenue = 0
            var totalEps = 0
            var capital = $('#end_capital').val()

            for (var i = 1; i <= 4; i++) {
                // 營收
                v = getRevenueQ(i)
                $('#q' + i + 'rt').val(v)
                $('#rq' + i + '_s_t').html(roundText(v));
                totalRevenue += parseInt(v)

                // eps
                v = getMain(i)

                if (capital === '' || capital === 0 || isNaN(capital) || v === '' || v === 0 || isNaN(v)) {
                    continue
                }

                eps = Math.round((v / capital) * 1000) / 100
                $('#epsq' + i + 'rt').val(eps)
                totalEps += eps
            }

            $('#revenue').val(totalRevenue)
            $('#revenue_s').html(roundText(totalRevenue))
            $('#eps').val(totalEps)

            var list = ['gross', 'cost', 'outside', 'other', 'tax', 'profit', 'profitb', 'profita', 'non', 'main']

            list.forEach(function (name) {
                var total = 0

                $('.form-group-' + name).find('input').each(function () {
                    v = parseInt($(this).val())
                    if (!isNaN(v)) {
                        total += v;
                    }
                })

                $('#' + name).val(total)
                $('#' + name + '_t_s').html(roundText(total))
                $('#' + name + '_t_b').html((Math.round((total / totalRevenue) * 10000) / 100) + '%')
            })

            // 修正所得稅率
            $('#tax_t_b').html((Math.round(($('#tax').val() / $('#profitb').val()) * 10000) / 100) + '%')

            // 預估股價
            reloadPriceF()
        }

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
        })

        // 毛利 費用 業外 其他 所得稅 利益 稅前 稅後 非控制
        $('.form-group-q').on('change', 'input', function () {
            var q = $(this).data('q')
            var id = $(this).data('id')

            reload('#q' + id + q, q, $(this).val())
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
                            <input type="text" class="form-control" placeholder="yyyy-mm-dd">
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
                                <option value="2">空</option>
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
                            <input type="text" class="form-control" id="epsf">
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
                            <input type="text" class="form-control" id="pricef4">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">預估股價</span>
                            </div>
                            <input type="text" class="form-control" id="pricef">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">

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
                            <div class="form-control" id="editor-base">
                            </div>
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
                                    <span class="input-group-text" id="rq{{ $i }}_s_t">0億</span>
                                </div>
                                <input type="text" class="form-control" id="q{{ $i }}rt" readonly>
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
                                <input type="text" class="form-control" id="epsq{{ $i }}rt" readonly>
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
                                <span class="input-group-text" id="cost_t_s">0億</span>
                                <span class="input-group-text" id="cost_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="cost" readonly>
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
                                <span class="input-group-text" id="profitb_t_s">0億</span>
                                <span class="input-group-text" id="profitb_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profitb" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">稅後</span>
                                <span class="input-group-text" id="profita_t_s">0億</span>
                                <span class="input-group-text" id="profita_t_b">0%</span>
                            </div>
                            <input type="text" class="form-control" id="profita" readonly>
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
                                        <span class="input-group-text" id="r{{ $i+$a }}_s">0</span>
                                    </div>
                                    <input type="number"
                                           class="form-control"
                                           data-m="{{ $i+$a }}"
                                           data-q="{{ ($a+3)/3 }}"
                                           id="r{{ $i+$a }}"
                                    >
                                </div>
                            </div>
                        @endfor
                        <div class="form-group">
                            <div class="input-group input-group-q-r">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ ($a+3)/3 }}Q</span>
                                    <span class="input-group-text" id="qr{{ ($a+3)/3 }}_s">0億</span>
                                </div>
                                <input type="number"
                                       class="form-control"
                                       data-q="{{ ($a+3)/3 }}"
                                       id="qr{{ ($a+3)/3 }}"
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
    @foreach($q as $v)
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
                                    <div class="form-group form-group-q form-group-{{ $a['value'] }}">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ $i }}Q</span>
                                                <span class="input-group-text" id="q{{ $a['id'] . $i }}_s">0</span>
                                                <span class="input-group-text" id="q{{ $a['id'] . $i }}_b">0</span>
                                            </div>
                                            <input type="text"
                                                   class="form-control"
                                                   data-name="{{ $a['value'] }}"
                                                   data-id="{{ $a['id'] }}"
                                                   data-q="{{ $i }}"
                                                   @if(isset($a['readonly']) && $a['readonly']) readonly @endif
                                                   id="q{{ $a['id'] . $i }}"
                                            >
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            @if(isset($a['editor']))
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
                                                <div class="form-control" id="editor-{{ $a['editor'] }}">
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
