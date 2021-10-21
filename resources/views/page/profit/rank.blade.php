@extends('partials.table')

@section('table_js')
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>

    <script>
        table = NewTable({
            name: '#list',
            url: getUrl(),
            columns: [
                {data: "code", width: '10%'},
                {data: "name", width: '20%'},
                {
                    data: "value",
                    width: '10%',
                    render: function (data, t, row, meta) {
                        if (data > 100000) {
                            return amountText(data)
                        }

                        return data
                    },
                },
                {data: "cName", width: '20%'},
                {
                    data: "tags",
                    width: '15%',
                    render: function (data, t, row, meta) {
                        var html = ''

                        data.forEach(function (v) {
                            html += '<span class="badge badge-pill badge-dark">' + v.name + '</span>'
                        })

                        return html
                    },
                },
            ],
            buttons: [
                reloadBtn,
                selectBtn,
            ],
            pageLength: 50,
        })

        $('#year,#season,#name').change(function () {
            table.ajax.url(getUrl())
        })

        function getUrl() {
            var url = "{{ route('profit.rank', ['year' => ':year', 'season' => ':season', 'name' => ':name']) }}"
            return url.replace(':year', $('#year').val()).replace(':season', $('#season').val()).replace(':name', $('#name').val()) + '?order=' + $('#order').val()
        }
    </script>
@stop

@section('content')
    <div class="card card-default" id="base-title">
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
                                <span class="input-group-text">年</span>
                            </div>
                            <select class="custom-select" id="year">
                                @for($i = 0; $i < 8;$i++)
                                    <option value="{{ $year - $i }}">{{ $year - $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">季度</span>
                            </div>
                            <select class="custom-select" id="season">
                                @for($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}">Q{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">排行</span>
                            </div>
                            <select class="custom-select" id="name">
                                @foreach($name as $k => $v)
                                    <option value="{{ $v }}">{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">排序</span>
                            </div>
                            <select class="custom-select" id="order">
                                <option value="desc">desc</option>
                                <option value="asc">asc</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default" id="base-title">
        <div class="card-header">
            <h3 class="card-title">列表</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body table-responsive">
                            <table id="list" class="table table-bordered table-striped dataTable dtr-inline"
                                   width="100%"
                                   cellspacing="0">
                                <thead>
                                <tr>
                                    @foreach($header as $v)
                                        <th>{{ $v }}</th>
                                    @endforeach
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
