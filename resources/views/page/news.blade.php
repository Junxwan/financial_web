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
    </style>
@stop

@section('table_js')
    <script>
        $(document).ready(function () {
            var edit = function (data) {
                // $('#modal-edit-code').val(data.code)
                // $('#modal-edit-name').val(data.name)
                // $('#modal-edit-type').val(data.type)
                // $('#modal-edit-upper').val(data.upper)
                // $('#modal-edit-down').val(data.down)
                // $('#modal-edit-order').val(data.order)
                // $('#modal-edit-desc').val(data.desc)
                // $('#modal-edit-id').val(table.row(this).index())
            }

            var del = function (data) {
                {{--axios.post("{{ route('parameter.delete') }}", data).then(function (response) {--}}
                {{--    if (response.data.result) {--}}
                {{--        toastr.success('刪除成功')--}}
                {{--        table.ajax.reload(null, false);--}}
                {{--    } else {--}}
                {{--        if (response.data.message != '') {--}}
                {{--            toastr.error(response.data.message)--}}
                {{--        } else {--}}
                {{--            toastr.error('刪除失敗')--}}
                {{--        }--}}
                {{--    }--}}
                {{--}).catch(function (error) {--}}
                {{--    toastr.error('刪除失敗')--}}
                {{--}).finally(function () {--}}
                {{--})--}}
            }

            var update = function () {
                {{--var name = $('#modal-edit-name').val()--}}
                {{--var upper = $('#modal-edit-upper').val()--}}
                {{--var down = $('#modal-edit-down').val()--}}
                {{--var order = $('#modal-edit-order').val()--}}
                {{--var desc = $('#modal-edit-desc').val()--}}

                {{--if (name === '') {--}}
                {{--    toastr.error('名稱 欄位必填')--}}
                {{--    return--}}
                {{--}--}}

                {{--if (order === '') {--}}
                {{--    toastr.error('排序 欄位必填')--}}
                {{--    return--}}
                {{--}--}}

                {{--if (desc === '') {--}}
                {{--    toastr.error('說明 欄位必填')--}}
                {{--    return--}}
                {{--}--}}

                {{--axios.put("{{ route('parameter.update') }}", {--}}
                {{--    'code': $('#modal-edit-code').val(),--}}
                {{--    "type": $('#modal-edit-type').val(),--}}
                {{--    'name': name,--}}
                {{--    "upper": upper,--}}
                {{--    "down": down,--}}
                {{--    "order": order,--}}
                {{--    "desc": desc,--}}
                {{--}).then(function (response) {--}}
                {{--    if (response.data.result) {--}}
                {{--        table.row($('#modal-id').val()).remove().draw(false)--}}
                {{--        toastr.success('保存成功')--}}
                {{--    } else {--}}
                {{--        if (response.data.message != '') {--}}
                {{--            toastr.error(response.data.message)--}}
                {{--        } else {--}}
                {{--            toastr.error('更新失敗')--}}
                {{--        }--}}
                {{--    }--}}
                {{--}).catch(function (error) {--}}
                {{--    toastr.error('更新失敗')--}}
                {{--}).finally(function () {--}}
                {{--    $('#modal-edit').modal('toggle');--}}
                {{--})--}}
            }

            var table = NewTable({
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
                buttons: [reloadBtn, selectBtn],
                edit: edit,
                delete: del,
                update: update,
                search:@json($search),
            })

            $('.right').html(
                '<div id="example_filter" class="dataTables_filter">' +
                '<input id="start_date" type="date" value="">' +
                '<input id="end_date" type="date" value="">' +
                '<input type="search" id="search-input">' +
                '</div>'
            )
        });
    </script>
@stop
