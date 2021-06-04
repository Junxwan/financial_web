@extends('partials.table')

@section('table_js')
    <script>
        $(document).ready(function () {
            var edit = function (data) {
                $('#modal-edit-title').val(data.title)
                $('#modal-edit-publish_time').val(data.publish_time)
                if (data.remark !== null) {
                    window.editor.setData(data.remark)
                } else {
                    window.editor.setData("")
                }

                $('#modal-id').val(data.id)
            }

            var del = function (data) {
                var url = '{{ route("news.delete", ":id") }}';
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

            var update = function () {
                var url = '{{ route("news.update", ":id") }}';
                axios.put(url.replace(':id', $('#modal-id').val()), {
                    'remark': window.editor.getData(),
                }).then(function (response) {
                    if (response.data.result) {
                        table.row($('#modal-id').val()).remove().draw(false)
                        toastr.success('保存成功')
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
                    {data: 'name', width: '20%'},
                    {data: 'title', width: '50%'},
                    {data: "create_time", width: '10%'},
                    editorEditBtn, editorDelete
                ],
                buttons: [reloadBtn, selectBtn],
                edit: edit,
                delete: del,
                update: update,
                pageLength: 10,
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<input type="date" id="start-date" value="">' +
                '<input type="date" id="end-date" value="">' +
                '<input type="search" id="search-input">' +
                '</div>'
            )
        });
    </script>
@stop
