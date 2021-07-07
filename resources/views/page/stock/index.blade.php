@extends('partials.table')

@section('table_css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-duallistbox.min.css') }}">
@stop

@section('table_js')
    <script src="{{ asset('js/jquery.bootstrap-duallistbox.min.js') }}"></script>
    <script>
        var tags = @json($tags);

        $(document).ready(function () {
            var edit = function (data) {
                $('#modal-edit-code').val(data.code)
                $('#modal-edit-name').val(data.name)
                $('#modal-edit-market').val(data.market)
                $('#modal-edit-classification_id').val(data.classification_id)
                $('#modal-id').val(data.id)

                ids = []
                data.tags.forEach(function (v) {
                    ids.push(v.id)
                })

                $("#modal-edit-tag option").each(function () {
                    $(this).remove();
                });

                tags.forEach(function (v) {
                    if (ids.includes(v.id)) {
                        editTag.append('<option value="' + v.id + '" selected>' + v.name + '</option>')
                    } else {
                        editTag.append('<option value="' + v.id + '">' + v.name + '</option>')
                    }
                })

                editTag.bootstrapDualListbox('refresh');
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
                var market = $('#modal-create-market').val()
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
                    market: market,
                    tags: createTag.val()
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
                var code = $('#modal-edit-code').val()
                var name = $('#modal-edit-name').val()
                var market = $('#modal-edit-market').val()
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
                    market: market,
                    tags: editTag.val()
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

            var classification = @json($classification->pluck('name', 'value')->toArray())

            NewTable({
                name: '#list',
                url: "{{ route('stock.list') }}",
                columns: [
                    {data: 'code', width: '10%'},
                    {data: 'name', width: '10%'},
                    {
                        data: "classification_id",
                        width: '10%',
                        render: function (data, t, row, meta) {
                            if (data in classification) {
                                return classification[data]
                            }

                            return ''
                        },
                    },
                    {
                        data: "market",
                        width: '5%',
                        render: function (data, t, row, meta) {
                            if (data === 1) {
                                return "上市"
                            }

                            if (data === 1) {
                                return "上櫃"
                            }

                            return ''
                        },
                    },
                    {
                        data: "tags",
                        width: '35%',
                        render: function (data, t, row, meta) {
                            var html = ''

                            data.forEach(function (v) {
                                html += '<span class="badge badge-pill badge-dark">' + v.name + '</span>'
                            })

                            return html
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
                '<select id="search-select">' +
                '<option value=""></option>' +
                @foreach($tags as $v)
                    '<option value="{{ $v->id }}">{{ $v->name }}</option>' +
                @endforeach
                    '</select>' +
                '<input type="search" id="search-input">' +
                '</div>'
            )

            var createTag = NewDualListBox('#modal-create-tag')
            var editTag = NewDualListBox('#modal-edit-tag')
        });
    </script>
@stop
