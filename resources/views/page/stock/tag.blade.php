@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            var edit = function (data) {
                $('#modal-edit-name').val(data.name)
                $('#modal-edit-isExponent').prop("checked", data.stock_id !== null)
                $('#modal-id').val(data.id)
            };

            var del = function (data) {
                var url = '{{ route("tag.delete", ":id") }}';
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
                var name = $('#modal-create-name').val()

                if (name === '') {
                    toastr.error('名稱 欄位必填')
                    return
                }

                axios.post('{{ route("tag.create") }}', {
                    name: name,
                    isExponent: $('#modal-create-isExponent').is(':checked'),
                }).then(function (response) {
                    if (response.data.result) {
                        toastr.success('新增成功')
                        table.ajax.reload(null, false);
                        $('.input-text').val('')
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
                var name = $('#modal-edit-name').val()
                if (name === '') {
                    toastr.error('名稱 欄位必填')
                    return
                }

                var url = '{{ route("tag.update", ":id") }}';
                axios.put(url.replace(':id', $('#modal-id').val()), {
                    name: name,
                    isExponent: $('#modal-edit-isExponent').is(':checked'),
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
                url: "{{ route('tag.list') }}",
                columns: [
                    {data: 'name', width: '90%'},
                    {
                        data: 'stock_id',
                        width: '90%',
                        render: function (data, t, row, meta) {
                            return data !== null ? '是' : '否'
                        },
                    },
                    editorEditBtn, editorDelete
                ],
                buttons: [reloadBtn, createBtn, selectBtn],
                create: create,
                edit: edit,
                delete: del,
                update: update,
                pageLength: 20,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<input type="search" id="search-input">' +
                '</div>'
            )
        });
    </script>
@stop
