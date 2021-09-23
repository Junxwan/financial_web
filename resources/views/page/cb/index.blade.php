@extends('partials.table')

@section('content_header')
    <h1></h1>
@stop

@section('table_js')
    <script>
        axios.get("{{ route('cb.price.last.date') }}").then(function (response) {
            $('.container-fluid>h1').html(response.data.date)
        }).catch(function (error) {
            console.log(error)
        })

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
                    {
                        data: 'name',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + row.url + '" target="_blank">' + data + '</a>'
                        },
                    },
                    {data: 'start_date', width: '10%'},
                    {data: 'end_date', width: '10%'},
                    {data: 'start_conversion_date', width: '10%'},
                    {data: 'conversion_price', width: '7%'},
                    {data: 'price', width: '7%'},
                    {data: 'off_price', width: '7%'},
                    {data: 'premium', width: '7%'},
                    {data: 'conversion_premium_rate', width: '8%'},
                    {data: 'conversion_stock', width: '8%'},
                    {
                        data: 'publish_total_amount',
                        width: '7%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + "{{ route('cb.balance.index') }}?code=" + row.code + '" target="_blank">' + (data / 100000000) + '億' + '</a>'
                        },
                    },
                    {
                        data: 'is_collateral',
                        width: '5%',
                        render: function (data, t, row, meta) {
                            return data === 1 ? '是' : '否'
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
