@extends('page')

@section('js')
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        function textR(value, r) {
            if (value >= r) {
                color = '#f33f7a'
            } else if (value < 0) {
                color = '#2a9309'
            } else {
                color = '#f2f5f1'
            }

            return '<span style="color:' + color + '"> ' + value + '%</span> '
        }

        $('#select-btn').click(function () {
            axios.get("{{ route('cb.rank.list') }}" + '?date=' + $('#date').val() + '&order=' + $('#order').val()).then(function (response) {
                $("#stock_list>tbody>tr").remove()

                response.data.forEach(function (v) {
                    let html =
                        "<tr>" +
                        "<td>" + '<a href="' + "{{ route('cb.price.index') }}?code=" + v.code + '" target="_blank">' + v.code + '</a>' + "</td>" +
                        "<td>" + v.name + "</td>" +
                        "<td>" + v.start_date + '</br>' + v.end_date + "</td>" +
                        "<td>" + v.conversion_price + "</td>" +
                        "<td>" + v.cb_close + "</td>" +
                        "<td>" + textR(v.increase, 0.1) + "</td>" +
                        "<td>" + textR(v.off_price, 100) + "</td>" +
                        "<td>" + textR(v.premium, 10) + "</td>" +
                        "<td>" + (v.publish_total_amount / 100000000) + '億' + "</td>" +
                        "<td>" + v.balance_rate + "</td>" +
                        "</tr>"

                    $("#stock_list>tbody").append(html)
                })

                toastr.success('成功')
            }).catch(function (error) {
                toastr.error('無資料')
            })
        })
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
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">日期</span>
                            </div>
                            <input type="date" class="form-control" id="date">
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
                                <option value=""></option>
                                <option value="increase-desc">市價漲幅</option>
                                <option value="cb_close-asc">市價</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-sm"
                            id="select-btn">
                        查
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">可轉債</h3>
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
                            <th scope="col">開始/結束</th>
                            <th scope="col">轉換價</th>
                            <th scope="col">市價</th>
                            <th scope="col">市價漲幅</th>
                            <th scope="col">理論價</th>
                            <th scope="col">折溢</th>
                            <th scope="col">金額</th>
                            <th scope="col">餘</th>
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
