@extends('partials.table')

@section('content_header')
    <h1></h1>
@stop

@section('table_css')
    <style>
        a {
            color: #7d8294;
            background-color: transparent;
            text-decoration: none;
        }

        td {
            color: #7d8294;
        }
    </style>
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
                        width: '8%',
                        render: function (data, t, row, meta) {
                            if (row.is_collateral) {
                                data = '*' + data
                            }

                            return '<a href="' + row.url + '" target="_blank">' + data + '</a>'
                        },
                    },
                    {
                        data: 'start_date',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return data + "</br>" + row.end_date
                        },
                    },
                    {data: 'conversion_price', width: '7%'},
                    {data: 'price', width: '7%'},
                    {
                        data: 'off_price',
                        width: '7%',
                        render: function (data, t, row, meta) {
                            if (row.off_price >= 100) {
                                return "<span style='color: #c73333'>" + data + "</span>"
                            } else if (row.off_price <= 95 && row.off_price !== 0) {
                                return "<span style='color: #5a9e0f'>" + data + "</span>"
                            }

                            return data
                        }
                    },
                    {data: 'premium', width: '6%'},
                    {data: 'conversion_stock', width: '6%'},
                    {
                        data: 'publish_total_amount',
                        width: '7%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + "{{ route('cb.balance.index') }}?code=" + row.code + '" target="_blank">' + (data / 100000000) + '???' + '</a>'
                        },
                    },
                    {data: 'balance_rate', width: '5%'},
                ],
                buttons: [reloadBtn, selectBtn],
                pageLength: 10,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<input type="date" id="start-date" value="">' +
                '<input type="search" id="search-input">' +
                '</div>'
            )
        });
    </script>
@stop
