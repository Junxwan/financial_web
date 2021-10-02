@extends('partials.table')

@section('content_header')
    <h1></h1>
@stop

@section('table_js')
    <script>
        axios.get("{{ route('price.last.date') }}").then(function (response) {
            $('.container-fluid>h1').html(response.data.date)
        }).catch(function (error) {
            console.log(error)
        })

        $(document).ready(function () {
            NewTable({
                name: '#list',
                url: "{{ route('price.list') }}",
                columns: [
                    {data: "code", width: '7%'},
                    {data: "name", width: '10%'},
                    {data: "close", width: '10%'},
                    {data: "increase", width: '5%'},
                    {data: "volume", width: '10%'},
                    {data: "y_volume_b", width: '10%'},
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
                    {
                        data: "tags",
                        width: '15%',
                        render: function (data, t, row, meta) {
                            var html = ''

                            data.forEach(function (v) {
                                html += '<span class="badge badge-pill badge-dark">' + v.name + '</span>'
                            })

                            return html
                        },
                    },
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
                '<select id="search-select">' +
                '<option value=""></option>' +
                '<option value="TSE">上市</option>' +
                '<option value="OTC">上櫃</option>' +
                '</select>' +
                '<select id="search-order">' +
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
                '</div>'
            )
        });
    </script>
@stop


