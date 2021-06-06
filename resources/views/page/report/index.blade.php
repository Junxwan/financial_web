@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            var edit = function (data) {
                $('#modal-edit-code').val(data.code)
                $('#modal-edit-name').val(data.name)
                $('#modal-edit-classification_id').val(data.classification_id)
                $('#modal-id').val(data.id)
            }

            var del = function (data) {
                var url = '{{ route("stock.delete", ":id") }}';
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

            var create = function () {
                var code = $('#modal-create-code').val()
                var name = $('#modal-create-name').val()
                var classification_id = $('#modal-create-classification_id').val()

                if (code === '') {
                    toastr.error('代碼 欄位必填')
                    return
                }

                if (name === '') {
                    toastr.error('名稱 欄位必填')
                    return
                }

                if (classification_id === '') {
                    toastr.error('產業 欄位必填')
                    return
                }

                axios.post('{{ route("stock.create") }}', {
                    code: code,
                    name: name,
                    classification_id: classification_id,
                }).then(function (response) {
                    if (response.data.result) {
                        toastr.success('新增成功')
                        table.ajax.reload(null, false);
                        $('#modal-create').modal('toggle')
                    } else {
                        if (response.data.message != '') {
                            toastr.error(response.data.message)
                        } else {
                            toastr.error('新增失敗')
                        }
                    }
                }).catch(function (error) {
                    toastr.error('新增失敗')
                })
            }

            var update = function () {
                var code = $('#modal-edit-code').val()
                var name = $('#modal-edit-name').val()
                var classification = $('#modal-edit-classification_id').val()

                if (code === '') {
                    toastr.error('代碼 欄位必填')
                    return
                }

                if (name === '') {
                    toastr.error('名稱 欄位必填')
                    return
                }

                if (classification === '') {
                    toastr.error('產業 欄位必填')
                    return
                }

                var url = '{{ route("stock.update", ":id") }}';
                axios.put(url.replace(':id', $('#modal-id').val()), {
                    code: code,
                    name: name,
                    classification_id: classification,
                }).then(function (response) {
                    if (response.data.result) {
                        table.row($('#modal-id').val()).remove().draw(false)
                        toastr.success('更新成功')
                    } else {
                        if (response.data.message != '') {
                            toastr.error(response.data.message)
                        } else {
                            toastr.error('更新失敗')
                        }
                    }
                }).catch(function (error) {
                    toastr.error('更新失敗')
                }).finally(function () {
                    $('#modal-edit').modal('toggle');
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
                            return row.eps_1 + row.eps_2 + row.eps_3 + row.eps_4
                        },
                    },
                    {
                        data: 'pe',
                        width: '10%',
                        render: function (data, t, row, meta) {
                            return (row.eps_1 + row.eps_2 + row.eps_3 + row.eps_4) * row.pe
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
                create: create,
                edit: edit,
                delete: del,
                update: update,
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
