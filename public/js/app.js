var language = {
    "processing": "處理中...",
    "loadingRecords": "載入中...",
    "lengthMenu": "顯示 _MENU_ 項結果",
    "zeroRecords": "沒有符合的結果",
    "info": "顯示第 _START_ 至 _END_ 項結果，共 _TOTAL_ 項",
    "infoEmpty": "顯示第 0 至 0 項結果，共 0 項",
    "infoFiltered": "(從 _MAX_ 項結果中過濾)",
    "search": "搜尋:",
    "paginate": {
        "first": "第一頁",
        "previous": "上一頁",
        "next": "下一頁",
        "last": "最後一頁"
    },
    "aria": {
        "sortAscending": ": 升冪排列",
        "sortDescending": ": 降冪排列"
    },
    "select": {
        "1": "%d 列已選擇",
        "2": "%d 列已選擇",
        "_": "%d 列已選擇"
    },
    "emptyTable": "目前沒有資料",
    "searchPlaceholder": "請輸入關鍵字",
    "datetime": {
        "previous": "上一頁",
        "next": "下一頁",
        "hours": "時",
        "minutes": "分",
        "seconds": "秒",
        "amPm": [
            "上午",
            "下午"
        ]
    },
    "searchBuilder": {
        "add": "新增條件",
        "clearAll": "清除",
        "condition": "條件",
        "deleteTitle": "刪除過濾條件",
        "leftTitle": "升級",
        "rightTitle": "降級"
    },
    "editor": {
        "close": "關閉",
        "create": {
            "button": "新增",
            "title": "建立新項目",
            "submit": "建立"
        },
        "edit": {
            "button": "編輯",
            "title": "編輯項目",
            "submit": "更新"
        },
        "remove": {
            "button": "刪除",
            "title": "刪除",
            "submit": "刪除",
            "confirm": {
                "_": "您確定要刪除 %d 筆資料嗎？",
                "1": "您確定要刪除 %d 筆資料嗎？"
            }
        },
        "multi": {
            "restore": "回復修改"
        }
    }
}

var editorEditBtn = {
    className: "dt-center editor-edit",
    defaultContent: '<a class="btn bg-gradient-secondary btn-sm btn-block"> <i class="fas fa-edit"></i></a>',
    orderable: false,
}

var editorDelete = {
    className: "dt-center editor-delete",
    defaultContent: '<a class="btn bg-gradient-secondary btn-sm btn-block"> <i class="fas fa-trash"></i></a>',
    orderable: false,
}

var createBtn = {
    text: '新增',
    className: "bg-gradient-primary",
    action: function (e, dt, node, config) {
        $('#modal-create').modal('toggle');
    }
}

var selectBtn = {
    text: '查詢',
    className: "bg-gradient-primary",
    action: function (e, dt, node, config) {
        table.ajax.reload(null, true);
    }
}

var reloadBtn = {
    text: '刷新',
    className: "bg-gradient-primary",
    action: function (e, dt, node, config) {
        table.ajax.reload(null, true);
    }
}

function NewTable(config) {
    table = $(config.name).DataTable({
        ajax: {
            url: config.url,
            data: function (d) {
                return {
                    'draw': d.draw,
                    'start': d.start,
                    'limit': d.length,
                    'search': {
                        'name': $('#search-select').val(),
                        'value': $('#search-input').val(),
                        'start_date': $('#start_date').val(),
                        'end_date': $('#end_date').val(),
                    },
                }
            }
        },
        columns: config.columns,
        dom: '<"wrapper"B<"right">frtip>',
        buttons: config.buttons,
        processing: true,
        serverSide: true,
        pageLength: 10,
        paging: true,
        lengthChange: false,
        searching: false,
        ordering: false,
        info: false,
        autoWidth: false,
        responsive: false,
        language: language,
    })

    // 編輯視窗
    $(name).on('click', 'td.editor-edit', function () {
        config.edit(table.row(this).data())
        $('#modal-edit').modal('toggle');
    })

    // 刪除
    $(name).on('click', 'td.editor-delete', function () {
        config.delete(table.row(this).data())
    })

    // 更新
    $('#modal-edit-btn').on('click', function () {
        config.update()
    })

    // 新增
    $('#modal-create-btn').on('click', function () {
        config.create()
    })

    if (config.search && config.search.length > 0) {
        var option = ''

        config.search.forEach(function (item) {
            option += "<option value=" + '"' + item['value'] + '">' + item['name'] + "</option>"
        })

        $('.search').html('<div id="example_filter" class="dataTables_filter">' +
            '<label>欄位 ' +
            '<select id="search-select" name="search-select">' +
            '<option selected value=""></option>' +
            option +
            '</select>' +
            '</label>' +
            '<input type="search" id="search-input"></div>')
    }

    return table
}
