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

        .ck-editor__editable {
            min-width: 765px;
            min-height: 310px;
        }

        .ck-content {
            color: #343a40;
        }

    </style>
@stop

@section('table_js')
    <script src="{{ asset('js/ckeditor.js') }}"></script>
    <script src="{{ asset('js/ckeditor.translations.zh.js') }}"></script>

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
                url: "{{ route('news.list') }}",
                columns: [
                    {
                        data: "title",
                        width: '65%',
                        render: function (data, t, row, meta) {
                            return '<a href="' + row.url + '" target="_blank">' + data + '</a>'
                        },
                    },
                    {data: "publish_time", width: '18%'},
                    editorEditBtn, editorDelete
                ],
                buttons: [reloadBtn, selectBtn,
                    {
                        text: '清除',
                        className: "bg-gradient-primary",
                        action: function (e, dt, node, config) {
                            $('#search-input').val('')
                            $('#start-date').val('')
                            $('#end-date').val('')
                        }
                    },
                    {
                        text: '整理',
                        className: "bg-gradient-primary",
                        action: function (e, dt, node, config) {
                            axios.post('{{ route("news.clear") }}').then(function (response) {
                                if (response.data.result) {
                                    toastr.success('整理成功: ' + response.data.count)
                                    table.ajax.reload(null, false);
                                } else {
                                    if (response.data.message != '') {
                                        toastr.error(response.data.message)
                                    } else {
                                        toastr.error('整理失敗')
                                    }
                                }
                            }).catch(function (error) {
                                toastr.error('整理失敗')
                            })
                        }
                    }
                ],
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

            ClassicEditor
                .create(document.querySelector('.modal-textarea'), {
                    language: 'zh'
                })
                .then(editor => {
                    window.editor = editor;
                })
                .catch(err => {
                    console.error(err.stack);
                });
        });
    </script>
@stop
