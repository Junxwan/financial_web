@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            NewTable({
                name: '#list',
                url: "{{ route('cb.list') }}",
                columns: [
                    {
                        data: 'code',
                        width: '5%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + "{{ route('cb.price.index') }}?code=" + row.code + '" target="_blank">' + data + '</a>'
                        },
                    },
                    {data: 'name', width: '10%'},
                    {data: 'start_date', width: '10%'},
                    {data: 'end_date', width: '10%'},
                    {data: 'start_conversion_date', width: '10%'},
                    {data: 'conversion_price', width: '7%'},
                    {data: 'conversion_premium_rate', width: '10%'},
                    {
                        data: 'publish_total_amount',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + "{{ route('cb.stock.balance.index') }}?code=" + row.code + '" target="_blank">' + (data / 100000000) + '億' + '</a>'
                        },
                    },
                    {
                        data: 'is_collateral',
                        width: '5%',
                        render: function (data, t, row, meta) {
                            return data === 1 ? '是' : '否'
                        },
                    },
                    {
                        data: 'url',
                        width: '5%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + data + '" target="_blank">連結</a>'
                        },
                    },
                ],
                buttons: [reloadBtn, selectBtn],
                pageLength: 20,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<select id="search-select">' +
                '<option value=""></option>' +
                '<option value="conversion_premium_rate">轉換溢價率</option>' +
                '<option value="publish_total_amount">發行額度</option>' +
                '</select>' +
                '<input type="search" id="search-input">' +
                '</div>'
            )
        });
    </script>
@stop
