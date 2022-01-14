@extends('page')

@section('js')
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/app.js?_=' . $time) }}"></script>
    <script>
        $('#profit-btn').click(function () {
            var url = "{{ route('profit.download', ['year' => ':year', 'quarterly' => ':quarterly']) }}"
            axios.get(url.replace(':year', $('#year').val()).replace(':quarterly', $('#quarterly').val())).then(function (response) {
                var hiddenElement = document.createElement('a');
                var blob = new Blob(["\ufeff" + response.data], {type: 'text/csv;charset=utf-8;'})
                hiddenElement.href = URL.createObjectURL(blob)
                hiddenElement.target = '_blank';
                hiddenElement.download = $('#year').val() + '-Q' + $('#quarterly').val() + '-profit.csv';
                hiddenElement.click();

                toastr.success('下載成功')
            }).catch(function (error) {
                toastr.error('下載失敗')
            })
        })

        $('#month-revenue-btn').click(function () {
            var url = "{{ route('revenue.rank.download', ['year' => ':year', 'month' => ':month']) }}"
            axios.get(url.replace(':year', $('#year').val()).replace(':month', $('#month').val())).then(function (response) {
                var hiddenElement = document.createElement('a')
                var blob = new Blob(["\ufeff" + response.data], {type: 'text/csv;charset=utf-8;'})
                hiddenElement.href = URL.createObjectURL(blob)
                hiddenElement.target = '_blank'
                hiddenElement.download = $('#year').val() + '-' + $('#month').val() + '-revenues.csv'
                hiddenElement.click()

                toastr.success('下載成功')
            }).catch(function (error) {
                toastr.error('下載失敗')
            })
        })

        $('#all-profit-btn').click(function () {
            var url = "{{ route('profit.download.all', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                var hiddenElement = document.createElement('a')
                var blob = new Blob(["\ufeff" + response.data], {type: 'text/csv;charset=utf-8;'})
                hiddenElement.href = URL.createObjectURL(blob)
                hiddenElement.target = '_blank'
                hiddenElement.download = $('#code').val() + '-損益.csv'
                hiddenElement.click()

                toastr.success('下載成功')
            }).catch(function (error) {
                toastr.error('下載失敗')
            })
        })

        $('#all-month-revenue-btn').click(function () {
            var url = "{{ route('revenue.download.all', ['code' => ':code']) }}"
            axios.get(url.replace(':code', $('#code').val())).then(function (response) {
                var hiddenElement = document.createElement('a')
                var blob = new Blob(["\ufeff" + response.data], {type: 'text/csv;charset=utf-8;'})
                hiddenElement.href = URL.createObjectURL(blob)
                hiddenElement.target = '_blank'
                hiddenElement.download = $('#code').val() + '-月營收.csv'
                hiddenElement.click()

                toastr.success('下載成功')
            }).catch(function (error) {
                toastr.error('下載失敗')
            })
        })
    </script>
@stop

@section('content')
    <div class="card card-default" id="base-title">
        <div class="card-header">
            <h3 class="card-title">匯出</h3>
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
                                @foreach($years as $v)
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
                                <span class="input-group-text">月</span>
                            </div>
                            <select class="custom-select" id="month">
                                @for($i = 1; $i <= 12; $i++)
                                    <option @if($month == $i) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">季</span>
                            </div>
                            <select class="custom-select" id="quarterly">
                                @for($i = 1; $i <= 4; $i++)
                                    <option @if($quarterly == $i) selected @endif value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="profit-btn">
                        財報
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="month-revenue-btn">
                        月營收
                    </button>
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
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="all-profit-btn">
                        全財
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-block bg-gradient-secondary btn-lg"
                            id="all-month-revenue-btn">
                        全月營
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop
