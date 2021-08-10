@extends('partials.table')

@section('table_css')
    <style>
        a:link {
            color: whitesmoke;
            background-color: transparent;
            text-decoration: none;
        }

        a:visited {
            color: cadetblue;
            background-color: transparent;
            text-decoration: none;
        }

        a:hover {
            color: whitesmoke;
            background-color: transparent;
            text-decoration: underline;
        }

        a:active {
            color: whitesmoke;
            background-color: transparent;
            text-decoration: underline;
        }
    </style>
@stop

@section('table_js')
    <script>
        $(document).ready(function () {
            NewTable({
                name: '#list',
                url: "{{ route('industry.list') }}",
                columns: [
                    {data: "code", width: '10%'},
                    {
                        data: "name",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + "{{ route('exponent.index') }}?tag=" + row.id + '" target="_blank">' + data + '</a>'
                        },
                    },
                    {
                        data: "increase",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return Math.round(data * 100) / 100
                        },
                    },
                    {
                        data: "volume",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return (Math.round(data / 1000000) / 100) + '億'
                        },
                    },
                    {data: "count", width: '10%'},
                ],
                buttons: [reloadBtn, selectBtn,],
                pageLength: 50,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<select id="search-order">' +
                '<option value="increase">漲幅</option>' +
                '<option value="volume">成交值</option>' +
                '</select>' +
                '<input type="date" id="start-date" value="">' +
                '</div>'
            )
        });
    </script>
@stop


