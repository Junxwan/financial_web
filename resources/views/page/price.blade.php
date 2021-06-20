@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            NewTable({
                name: '#list',
                url: "{{ route('price.list') }}",
                columns: [
                    {data: "code", width: '7%'},
                    {data: "name", width: '10%'},
                    {data: "open", width: '10%'},
                    {data: "close", width: '10%'},
                    {data: "high", width: '10%'},
                    {data: "low", width: '10%'},
                    {data: "increase", width: '5%'},
                    {
                        data: "volume",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return data
                        },
                    },
                    {
                        data: "value",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return (Math.round(data / 1000000) / 100) + '億'
                        },
                    },
                    {
                        data: "market",
                        width: '5%',
                        render: function (data, t, row, meta) {
                            if (data === 1) {
                                return "上市"
                            }

                            if (data === 2) {
                                return "上櫃"
                            }
                        },
                    },
                    {data: "cName", width: '10%'},
                ],
                buttons: [reloadBtn, selectBtn],
                pageLength: 20,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<select id="search-select">' +
                '<option value=""></option>' +
                '<option value="TSE">上市</option>' +
                '<option value="OTC">上櫃</option>' +
                '</select>' +
                '<select id="search-order">' +
                '<option value="increase">漲幅</option>' +
                '<option value="volume">成交量</option>' +
                '<option value="value">成交金額</option>' +
                '</select>' +
                '<input type="date" id="start-date" value="">' +
                '</div>'
            )
        });
    </script>
@stop


