@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            var create = function () {
                var name = $('#modal-create-name').val()
                var keys = $('#modal-create-keys').val()

                if (name === '') {
                    toastr.error('名稱 欄位必填')
                    return
                }

                if (keys === '') {
                    toastr.error('關鍵字 欄位必填')
                    return
                }

                axios.post('{{ route("news.keyWord.create") }}', {
                    name: name,
                    keys: keys,
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
                var keys = $('#modal-edit-keys').val()

                if (name === '') {
                    toastr.error('名稱 欄位必填')
                    return
                }

                if (keys === '') {
                    toastr.error('關鍵字 欄位必填')
                    return
                }

                var url = '{{ route("news.keyWord.update", ":id") }}';
                axios.put(url.replace(':id', $('#modal-id').val()), {
                    name: name,
                    keys: keys,
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

            var edit = function (data) {
                $('#modal-edit-name').val(data.name)
                $('#modal-edit-keys').val(data.keys)
                $('#modal-id').val(data.id)
            };

            var del = function (data) {
                var url = '{{ route("news.keyWord.delete", ":id") }}';
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
                url: "{{ route('news.keyWord.list') }}",
                columns: [
                    {data: "name", width: '20%'},
                    {data: "keys", width: '50%'},
                    editorEditBtn, editorDelete
                ],
                buttons: [createBtn, reloadBtn, selectBtn],
                pageLength: 10,
                create: create,
                edit: edit,
                update: update,
                delete: del,
            })
        });
    </script>
@stop
