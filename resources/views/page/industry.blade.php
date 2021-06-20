@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            NewTable({
                name: '#list',
                url: "{{ route('industry.list') }}",
                columns: [
                    {data: "code", width: '10%'},
                    {data: "name", width: '10%'},
                    {
                        data: "increase",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return Math.round(data * 100) / 100
                        },
                    },
                    {data: "volume_ratio", width: '10%'},
                    {
                        data: "volume",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return (Math.round(data / 1000000) / 100) + '億'
                        },
                    },
                ],
                buttons: [reloadBtn, selectBtn,],
                pageLength: 50,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<select id="search-select">' +
                '<option value="TSE">TSE</option>' +
                '<option value="OTC">OTC</option>' +
                '</select>' +
                '<select id="search-order">' +
                '<option value="increase">漲幅</option>' +
                '<option value="volume_ratio">成交占比</option>' +
                '</select>' +
                '<input type="date" id="start-date" value="">' +
                '</div>'
            )
        });
    </script>
@stop


