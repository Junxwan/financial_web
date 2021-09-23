@extends('partials.table')

@section('table_js')
    <script>
        const params = Object.fromEntries(new URLSearchParams(window.location.search).entries());

        history.replaceState(null, null, "{{ route('cb.balance.index') }}");

        $(document).ready(function () {
            table = NewTable({
                name: '#list',
                url: "{{ route('cb.balance.list') }}",
                data: function (data, d) {
                    if (typeof params.code !== 'undefined') {
                        data['search']['value'] = params.code
                    }

                    return data
                },
                columns: [
                    {data: 'code', width: '5%'},
                    {data: 'name', width: '10%'},
                    {data: 'year', width: '5%'},
                    {data: 'month', width: '5%'},
                    {
                        data: 'change',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return data.toLocaleString('en-US')
                        },
                    },
                    {
                        data: 'balance',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return data.toLocaleString('en-US')
                        },
                    },
                    {
                        data: 'change_stock',
                        width: '15%',
                        render: function (data, t, row, meta) {
                            return data.toLocaleString('en-US')
                        },
                    },
                    {
                        data: 'balance_stock',
                        width: '15%',
                        render: function (data, t, row, meta) {
                            return data.toLocaleString('en-US')
                        },
                    },
                    {data: 'balance_rate', width: '10%'},
                ],
                buttons: [reloadBtn, selectBtn],
                pageLength: 20,
                drawCallback: function () {
                    if (typeof params.code !== 'undefined') {
                        $('#search-input').val(params.code)
                        params.code = undefined
                    }
                }
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<input type="search" id="search-input">' +
                '</div>'
            )
        });
    </script>
@stop
