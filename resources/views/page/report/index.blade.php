@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            var edit = function (data) {
                var url = '{{ route("report.edit", ":id") }}';
                location.href = url.replace(':id', data.id)
            }

            var del = function (data) {
                var url = '{{ route("report.delete", ":id") }}';
                axios.delete(url.replace(':id', data.id), data).then(function (response) {
                    if (response.data.result) {
                        toastr.success('刪除成功')
                        table.ajax.reload(null, false);
                    } else {
                        if (response.data.message != '') {
                            toastr.error(response.data.message)
                        } else {
                            toastr.error('刪除失敗')
                        }
                    }
                }).catch(function (error) {
                    toastr.error('刪除失敗')
                }).finally(function () {
                })
            }

            NewTable({
                name: '#list',
                url: "{{ route('report.list') }}",
                columns: [
                    {data: 'code', width: '10%'},
                    {data: 'name', width: '10%'},
                    {data: 'title', width: '30%'},
                    {
                        data: 'eps_1',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return Math.round((row.eps_1 + row.eps_2 + row.eps_3 + row.eps_4)*100)/100
                        },
                    },
                    {
                        data: 'pe',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return Math.round(((row.eps_1 + row.eps_2 + row.eps_3 + row.eps_4) * row.pe)*100)/100
                        },
                    },
                    {
                        data: 'action',
                        width: '5%',
                        render: function (data, t, row, meta) {
                            if (data === 0) {
                                return '空'
                            } else if (data === 1) {
                                return '多'
                            }
                            return ''
                        },
                    },
                    {data: 'date', width: '10%'},
                    editorEditBtn, editorDelete
                ],
                buttons: [reloadBtn, {
                    text: '新增',
                    className: "bg-gradient-primary",
                    action: function (e, dt, node, config) {
                        location.href = '{{ route('report.create.view') }}'
                    }
                }, selectBtn,
                    {
                        text: '清除',
                        className: "bg-gradient-primary",
                        action: function (e, dt, node, config) {
                            $('#search-select').val('')
                            $('#search-input').val('')
                            $('#start-date').val('')
                            $('#end-date').val('')
                        }
                    },
                ],
                edit: edit,
                delete: del,
                pageLength: 20,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<input type="date" id="start-date" value="">' +
                '<input type="date" id="end-date" value="">' +
                '  ' +
                '<select id="search-select" name="search-select">' +
                '<option selected value="">' +
                '<option value="code">代碼</option>' +
                '<option value="title">標題</option>' +
                '</select>' +
                '<input type="search" id="search-input">' +
                '</>'
            )
        });
    </script>
@stop
