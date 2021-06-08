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
                        'start_date': $('#start-date').val(),
                        'end_date': $('#end-date').val(),
                    },
                }
            }
        },
        columns: config.columns,
        dom: '<"wrapper"B<"right">frtip>',
        buttons: config.buttons,
        processing: true,
        serverSide: true,
        pageLength: config.pageLength,
        paging: true,
        lengthChange: false,
        searching: false,
        ordering: false,
        info: true,
        autoWidth: false,
        responsive: false,
        language: language,
    })

    // 編輯視窗
    $(config.name).on('click', 'td.editor-edit', function () {
        config.edit(table.row(this).data())
        $('#modal-edit').modal('toggle');
    })

    // 刪除
    $(config.name).on('click', 'td.editor-delete', function () {
        if (confirm("確定刪除?")) {
            config.delete(table.row(this).data())
        }
    })

    // 更新
    $('#modal-edit-btn').on('click', function () {
        config.update()
    })

    // 新增
    $('#modal-create-btn').on('click', function () {
        config.create()
    })

    return table
}

// 百萬轉億
function roundText(a) {
    var t = (a / 100)
    if (t >= 10 && t <= 99.99) {
        t = Math.round(t * 10) / 10
    } else if (t > 100) {
        t = Math.round(t)
    }
    return t + '億'
}

// 某季營收
function getRevenueQ(q) {
    return $('#q_revenue_' + q).val()
}

// 某季毛利
function getGross(q) {
    return $('#q_gross_' + q).val()
}

// 某季費用
function getFee(q) {
    return $('#q_fee_' + q).val()
}

// 某季業外
function getOutside(q) {
    return $('#q_outside_' + q).val()
}

// 某季其他收益
function getOther(q) {
    return $('#q_other_' + q).val()
}

// 某季所得稅
function getTax(q) {
    return $('#q_tax_' + q).val()
}

// 某季利益
function getProfit(q) {
    return $('#q_profit_' + q).val()
}

// 某季稅前
function getProfitB(q) {
    return $('#q_profitB_' + q).val()
}

// 某季稅後
function getProfitA(q) {
    return $('#q_profitA_' + q).val()
}

// 某季非控制權益
function getNon(q) {
    return $('#q_non_' + q).val()
}

// 某季母控制權益
function getMain(q) {
    return $('#q_main_' + q).val()
}

// 佔季營收比例
function revenueQProportion(q, v) {
    r = getRevenueQ(q)
    if (r === '' || r <= 0) {
        return 0
    }

    return Math.round((v / r) * 10000) / 100
}

// 設置月營收text
function setRevenueText(m, v) {
    $('#revenue_' + m + '_s').html(roundText(v))
}

// 設置各項值與Text
function reload(name, q, v) {
    if (isNaN(v) || v === '' || v === 0) {
        $(name + '_b').html('0%');
        return
    }

    $(name).val(v)
    $(name + '_s').html(roundText(v));
    $(name + '_b').html(revenueQProportion(q, v) + '%');
}

// 重整季營收
function reloadRevenueQ(q) {
    var total = 0;
    $('.input-group-q-' + q).find('input').each(function () {
        v = parseInt($(this).val())
        if (!isNaN(v)) {
            total += v;
        }
    })

    reload('#q_revenue_' + q, q, total)
}

// 重整毛利
function reloadGross(q) {
    reload('#q_gross_' + q, q, getGross(q))
}

// 重整費用
function reloadFee(q) {
    reload('#q_fee_' + q, q, getFee(q))
}

// 重整業外
function reloadOutside(q) {
    reload('#q_outside_' + q, q, getOutside(q))
}

// 重整其他收益
function reloadOther(q) {
    reload('#q_other_' + q, q, getOther(q))
}

// 設置利益
function reloadProfit(q) {
    reload('#q_profit_' + q, q, getGross(q) - getFee(q))
}

// 重整稅前
function reloadProfitB(q) {
    reload('#q_profitB_' + q, q, parseInt(getProfit(q)) + parseInt(getOutside(q)) + parseInt(getOther(q)))
}

// 重整稅後
function reloadProfitA(q) {
    reload('#q_profitA_' + q, q, getProfitB(q) - getTax(q))
}

// 重整季所得稅與Text
function reloadTax(q) {
    name = '#q_tax_' + q
    v = getTax(q)
    if (v === '' || v === 0) {
        $(name + '_b').html('0%');
        return;
    }

    v1 = getProfitB(q)
    if (v1 === '' || v1 === 0) {
        $(name + '_b').html('0%');
        return
    }

    reload(name, q, v)
    $(name + '_b').html((Math.round((v / v1) * 10000) / 100) + '%');
}

// 重整非控制權益
function reloadNon(q) {
    reload('#q_non_' + q, q, getNon(q))
}

// 重整母控制權益
function reloadMain(q) {
    reload('#q_main_' + q, q, getProfitA(q) - getNon(q))
}

// 整個重計算
function reloadAll(q) {
    reloadGross(q)
    reloadFee(q)
    reloadOutside(q)
    reloadOther(q)
    reloadProfit(q)
    reloadProfitB(q)
    reloadTax(q)
    reloadProfitA(q)
    reloadNon(q)
    reloadMain(q)
}

// 整理總結
function readTotal() {
    var totalRevenue = 0
    var totalEps = 0
    var capital = $('#end_capital').val()

    for (var i = 1; i <= 4; i++) {
        // 營收
        v = getRevenueQ(i)
        $('#r_q_' + i).val(v)
        $('#r_q_' + i + '_s').html(roundText(v));
        totalRevenue += parseInt(v)

        // eps
        v = getMain(i)

        if (capital === '' || capital === 0 || isNaN(capital) || v === '' || v === 0 || isNaN(v)) {
            continue
        }

        eps = Math.round((v / capital) * 1000) / 100
        $('#eps_q_' + i).val(eps)
        totalEps += eps
    }

    $('#revenue').val(totalRevenue)
    $('#revenue_s').html(roundText(totalRevenue))
    $('#eps').val(Math.round(totalEps * 100) / 100)

    var list = ['gross', 'fee', 'outside', 'other', 'tax', 'profit', 'profitB', 'profitA', 'non', 'main']

    list.forEach(function (name) {
        var total = 0

        $('.form-group-' + name).find('input').each(function () {
            v = parseInt($(this).val())
            if (!isNaN(v)) {
                total += v;
            }
        })

        $('#' + name).val(total)
        $('#' + name + '_t_s').html(roundText(total))
        $('#' + name + '_t_b').html((Math.round((total / totalRevenue) * 10000) / 100) + '%')
    })

    // 修正所得稅率
    $('#tax_t_b').html((Math.round(($('#tax').val() / $('#profitB').val()) * 10000) / 100) + '%')
}

function getValue(name, d) {
    v = $(name).val()

    if (isNaN(v) || v === '') {
        return d
    }

    return v
}
