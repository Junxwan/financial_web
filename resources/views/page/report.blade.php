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
                <div class="col-md-3">
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
                                <span class="input-group-text">年營收</span>
                            </div>
                            <input type="text" class="form-control" id="year_revenue">
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
                            <input type="text" class="form-control" id="s3q_eps">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">近四季EPS</span>
                            </div>
                            <input type="text" class="form-control" id="s4q_eps">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">市場財測</span>
                            </div>
                            <input type="text" class="form-control" id="epsf" placeholder="yyyy-mm-dd">
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
                                <span class="input-group-text">股本(億)</span>
                            </div>
                            <input type="text" class="form-control" id="capital">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Q1 EPS</span>
                            </div>
                            <input type="text" class="form-control" id="q1_eps">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Q2 EPS</span>
                            </div>
                            <input type="text" class="form-control" id="q2_eps">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Q3 EPS</span>
                            </div>
                            <input type="text" class="form-control" id="q3_eps">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Q4 EPS</span>
                            </div>
                            <input type="text" class="form-control" id="q4_eps">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">全年EPS</span>
                            </div>
                            <input type="text" class="form-control" id="year_eps">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
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
                                <span class="input-group-text">預估股價</span>
                            </div>
                            <input type="text" class="form-control" id="pricef">
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
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">{{ $i+$a }}月</span>
                                        <span class="input-group-text" id="r{{ $i+$a }}_s">0</span>
                                    </div>
                                    <input type="number" class="form-control" id="r{{ $i+$a }}">
                                </div>
                            </div>
                        @endfor
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ ($a+3)/3 }}Q</span>
                                    <span class="input-group-text" id="qr{{ ($a+3)/3 }}_s">0</span>
                                </div>
                                <input type="number" class="form-control" id="qr{{ ($a+3)/3 }}">
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
                            <h3 class="card-title">{{ $a['name'] }}</h3>
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
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ $i }}Q</span>
                                                <span class="input-group-text" id="q{{ $a['id'] . $i }}_s">0</span>
                                                <span class="input-group-text" id="q{{ $a['id'] . $i }}_b">0</span>
                                            </div>
                                            <input type="text" class="form-control" id="q{{ $a['id'] . $i }}">
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
                                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
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
