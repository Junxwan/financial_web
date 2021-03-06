@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            NewTable({
                name: '#list',
                url: "{{ route('stock.price.list') }}",
                columns: [
                    {data: "code", width: '7%'},
                    {data: "name", width: '10%'},
                    {data: "date", width: '10%'},
                    {data: "close", width: '10%'},
                    {data: "increase", width: '5%'},
                    {
                        data: "value",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return (Math.round(data / 1000000) / 100) + '億'
                        },
                    },
                    {
                        data: "fund_value",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return (Math.round(data / 1000000) / 100) + '億'
                        },
                    },
                    {
                        data: "foreign_value",
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
                    {data: "increase_5", width: '5%'},
                    {data: "increase_23", width: '5%'},
                    {data: "increase_63", width: '5%'},
                ],
                buttons: [
                    reloadBtn,
                    selectBtn,
                    {
                        text: '匯出(XQ)',
                        className: "bg-gradient-primary",
                        action: function (e, dt, node, config) {
                            let csv = ''
                            $('#list tr').each(function () {
                                v = jQuery($(this).find('td')[0]).text()
                                if (v !== '') {
                                    csv += v + '.TW'
                                    csv += "\n";
                                }
                            })

                            var hiddenElement = document.createElement('a');
                            var blob = new Blob(["\ufeff" + csv], {type: 'text/csv;charset=utf-8;'})
                            hiddenElement.href = URL.createObjectURL(blob)
                            hiddenElement.target = '_blank';
                            hiddenElement.download = 'xq.csv';
                            hiddenElement.click();
                        }
                    }
                ],
                pageLength: 20,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '</select>' +
                '<select id="search-order">' +
                '<option value="date">日期</option>' +
                '<option value="increase">漲幅</option>' +
                '<option value="volume">成交量</option>' +
                '<option value="value">成交值</option>' +
                '<option value="increase_5">週%</option>' +
                '<option value="increase_23">月%</option>' +
                '<option value="increase_63">季%</option>' +
                '<option value="fund_value">投信金額</option>' +
                '<option value="foreign_value">外資金額</option>' +
                '</select>' +
                '<input type="date" id="start-date" value="">' +
                '<input type="search" id="search-input">' +
                '</div>'
            )
        });
    </script>
@stop


