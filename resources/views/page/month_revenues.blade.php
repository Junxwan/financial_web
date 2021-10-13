@extends('page')

@section('js')
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        $('#select-btn').click(function () {
            axios.get("{{ route('revenue.rank.month.list') }}?year=" + $('#year').val() + '&month=' + $('#month').val()).then(function (response) {
                $("#stock_list>tbody>tr").remove()
                r = $('#r').val()
                f = $('#f').val()

                response.data.forEach(function (v) {
                    if (f === 0 || f === '' || (f > 0 && v.yoy >= f) || (f < 0 && v.yoy <= f)) {
                        let html =
                            "<tr>" +
                            "<td>" + v.code + "</td>" +
                            "<td>" + v.name + "</td>" +
                            "<td>" + amountText(v.value) + "</td>" +
                            "<td>" + textR(v.yoy, r) + "</td>" +
                            "<td>" + textR(v.qoq, r) + "</td>" +
                            "<td>" + v.cname + "</td>" +
                            "<td>" + amountText(v.total) + "</td>" +
                            "<td>" + amountText(v.y_total) + "</td>" +
                            "<td>" + textR(v.total_increase) + "</td>" +
                            "</tr>"

                        $("#stock_list>tbody").append(html)
                    }
                })

                toastr.success('成功')
            }).catch(function (error) {
                toastr.error('無資料')
            })
        })

        $('#export-btn').click(function () {
            let header = []
            $('#stock_list>thead>tr>th').each(function () {
                header.push($(this).text())
            })

            let data = [header]
            $('#stock_list>tbody>tr').each(function () {
                let v = []
                $(this).children('td').each(function () {
                    v.push($(this).text().trim())
                })
                data.push(v)
            })

            var hiddenElement = document.createElement('a');
            var blob = new Blob(["\ufeff" + convertToCSV(data)], {type: 'text/csv;charset=utf-8;'})
            hiddenElement.href = URL.createObjectURL(blob)
            hiddenElement.target = '_blank';
            hiddenElement.download = $('#year').val() + '-' + $('#month').val() + '-revenues.csv';
            hiddenElement.click();
        })

        function textR(value, r) {
            if (value >= r) {
                color = '#f33f7a'
            } else if (value <= -r) {
                color = '#2a9309'
            } else {
                color = '#f2f5f1'
            }

            return '<span style="color:' + color + '"> ' + value + '%</span> '
        }

        function convertToCSV(objArray) {
            var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
            var str = '';

            for (var i = 0; i < array.length; i++) {
                var line = '';
                for (var index in array[i]) {
                    if (line != '') line += ','

                    line += array[i][index];
                }

                str += line + '\r\n';
            }

            return str;
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
                                @for($i = 0; $i < 8; $i++)
                                    <option value="{{ $year - $i }}">{{ $year - $i }}年</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">月</span>
                            </div>
                            <select class="custom-select" id="month">
                                @for($i = 1; $i <= 12; $i++)
                                    <option @if($i == $month) selected @endif value="{{ $i }}">{{ $i }}月</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">標示</span>
                            </div>
                            <input type="number" class="form-control" value="20" id="r">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">塞</span>
                            </div>
                            <input type="number" class="form-control" id="f">
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-btn">
                        查
                    </button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="export-btn">
                        匯出
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">月營收</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                        class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="remove"><i
                        class="fas fa-remove"></i></button>
            </div>
        </div>
        <div class="card-body" style="display: block;">
            <div id="stock" class="row">
            </div>
            <div class="row">
                <div class="col-md-12 card-body table-responsive p-0" style="height: 500px;">
                    <table id="stock_list" class="table table-dark table-head-fixed text-nowrap">
                        <thead>
                        <tr>
                            <th scope="col">代碼</th>
                            <th scope="col">名稱</th>
                            <th scope="col">營收</th>
                            <th scope="col">yoy</th>
                            <th scope="col">qoq</th>
                            <th scope="col">類別</th>
                            <th scope="col">累積</th>
                            <th scope="col">去年累積</th>
                            <th scope="col">累積成長</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
