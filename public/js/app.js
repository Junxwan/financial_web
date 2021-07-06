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
                    'order': $('#search-order').val(),
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

function NewDualListBox(selector) {
    return $(selector).bootstrapDualListbox({
        nonSelectedListLabel: '標籤',
        selectedListLabel: '已選標籤',
        filterTextClear: '顯示所有',
        filterPlaceHolder: '搜尋',
        moveSelectedLabel: "新增",
        moveAllLabel: '全部新增',
        removeSelectedLabel: "移除",
        removeAllLabel: '全部移除',
        infoText: false,
        infoTextFiltered: '搜尋到{0}個 ,共{1}個',
        infoTextEmpty: '列表為空',
    })
}

// get ================================================================================

// 某季營收
function getRevenueQ(q) {
    return getValue('#revenue_' + q, 0)
}

// 某月營收
function getRevenueMonth(m) {
    return getValue('#revenue_month_' + m, 0)
}

// 某季毛利
function getGross(q) {
    return getValue('#gross_' + q, 0)
}

// 某季費用
function getFee(q) {
    return getValue('#fee_' + q, 0)
}

// 某季業外
function getOutside(q) {
    return getValue('#outside_' + q, 0)
}

// 某季其他收益
function getOther(q) {
    return getValue('#other_' + q, 0)
}

// 某季所得稅
function getTax(q) {
    return getValue('#tax_' + q, 0)
}

// 某季利益
function getProfit(q) {
    return getValue('#profit_' + q, 0)
}

// 某季稅前
function getProfitPre(q) {
    return getValue('#profit_pre_' + q, 0)
}

// 某季稅後
function getProfitAfter(q) {
    return getValue('#profit_after_' + q, 0)
}

// 某季非控制權益
function getNon(q) {
    return getValue('#profit_non_' + q, 0)
}

// 某季母控制權益
function getMain(q) {
    return getValue('#profit_main_' + q, 0)
}

function getValue(name, d) {
    v = $(name).val()

    if (isNaN(v) || v === '') {
        return d
    }

    return v
}

// 當前選擇的季度
function getQuarterly() {
    var season = 0
    $('.checkbox-quarterly').each(function () {
        if ($(this).is(":checked")) {
            season = $(this).data('q')
        }
    })

    return season
}

// 當前選擇的月
function getMonth() {
    var month = 0
    $('.checkbox-month').each(function () {
        if ($(this).is(":checked")) {
            month = $(this).data('m')
        }
    })

    return month
}

// set ================================================================================

function setInfo(data) {
    $('#title').val(data.title)
    $('#date').val(data.date)
    $('#value').val(data.value)
    $('#action').val(data.action)
    $('#eps3_sum').val(data.eps3_sum)
    $('#eps4_sum').val(data.eps4_sum)
    $('#market_eps_f').val(data.market_eps_f)
    $('#pe').val(data.pe)
    $('#evaluate').val(data.evaluate)
    $('#price_f').val(data.price_f)
    $('#start_capital').val(data.start_capital / 1000)
    $('#start_capital_text').val(roundText(data.start_capital / 1000))
    $('#end_capital').val(data.capital / 1000)
    $('#end_capital_text').val(roundText(data.capital / 1000))
}

// 月營收
function setRevenue(m, v) {
    v = (typeof v !== 'undefined') ? v : getRevenueMonth(m);
    setValue('#revenue_month', m, v)
}

// 重整毛利
function setGross(q, v) {
    v = (typeof v !== 'undefined') ? v : getGross(q);
    setValue('#gross', q, v)
}

// 重整費用
function setFee(q, v) {
    v = (typeof v !== 'undefined') ? v : getFee(q);
    setValue('#fee', q, v)
}

// 重整業外
function setOutside(q, v) {
    v = (typeof v !== 'undefined') ? v : getOutside(q);
    setValue('#outside', q, v)
}

// 重整其他收益
function setOther(q, v) {
    v = (typeof v !== 'undefined') ? v : getOther(q);
    setValue('#other', q, v)
}

// 設置利益
function setProfit(q, v) {
    v = (typeof v !== 'undefined') ? v : getGross(q) - getFee(q);
    setValue('#profit', q, v)
}

// 重整稅前
function setProfitPre(q, v) {
    v = (typeof v !== 'undefined') ? v : parseInt(getProfit(q)) + parseInt(getOther(q)) + parseInt(getOutside(q));
    setValue('#profit_pre', q, v)
}

// 重整稅後
function setProfitAfter(q, v) {
    v = (typeof v !== 'undefined') ? v : parseInt(getProfitPre(q)) - parseInt(getTax(q));
    setValue('#profit_after', q, v)
}

// 重整季所得稅
function setTax(q, v) {
    tax = getTax(q)
    profitPre = getProfitPre(q)
    setValue('#tax', q, v)

    ratio = 0
    if (tax !== 0 && profitPre !== 0) {
        ratio = (tax / profitPre)

        if (1 < ratio) {
            ratio = Math.round(ratio * 10000) / 100
        } else {
            ratio = Math.round(ratio * 1000) / 100
        }
    }

    $('#tax_' + q + '_ratio').html(ratio + '%');
}

// 重整非控制權益
function setNon(q, v) {
    v = (typeof v !== 'undefined') ? v : getNon(q);
    setValue('#profit_non', q, v)
}

// 重整母控制權益
function setMain(q, v) {
    v = (typeof v !== 'undefined') ? v : getProfitAfter(q) - getNon(q);
    setValue('#profit_main', q, v)
}

// 設置總值
function setTotal(name) {
    var total = 0
    $('.form-group-' + name + ' input').each(function () {
        if (name === 'eps') {
            v = parseFloat($(this).val())
        } else {
            v = parseInt($(this).val())
        }
        if (!isNaN(v)) {
            total += v
        }
    })

    name = '#' + name
    $(name).val(total)
    $(name + '_text').html(roundText(total));
    $(name + '_ratio').html((Math.round((total / $('#revenue').val()) * 10000) / 100) + '%')
}

// 設置各項值與Text
function setValue(name, q, v) {
    if (isNaN(v) || v === '' || v === 0) {
        $(name + '_ratio').html('0%');
        return
    }

    name = name + '_' + q
    $(name).val(v)
    $(name + '_text').html(roundText(v));
    $(name + '_ratio').html(revenueQProportion(q, v) + '%');
}

function lockQuarterly() {
    q = getQuarterly()
    $('.span-quarterly, .form-group-quarterly input').each(function () {
        if (q >= $(this).data('q') && q !== 0) {
            $(this).addClass('span-selected')
            $(this).attr('readonly', 'readonly')
        } else {
            $(this).removeClass('span-selected')
            $(this).removeAttr('readonly')
        }
    })
}

function lockMonth() {
    m = getMonth()
    $('.span-month, .form-group-month input').each(function () {
        if (m >= $(this).data('m') && m !== 0) {
            $(this).addClass('span-selected')
            $(this).attr('readonly', 'readonly')
        } else {
            $(this).removeClass('span-selected')
            $(this).removeAttr('readonly')
        }
    })
}

// 計算 ================================================================================

function compute() {
    q = getQuarterly()

    for (var i = 1; i <= 4; i++) {
        if (i <= q) {
            continue
        }

        setGross(i)
        setFee(i)
        setOutside(i)
        setOther(i)
        setProfit(i)
        setProfitPre(i)
        setProfitAfter(i)
        setTax(i)
        setNon(i)
        setMain(i)

        $('#eps_' + i).val(
            Math.round((getMain(i) / $('#start_capital').val()) * 1000) / 100
        )
    }

    $('.form-group-quarterly').each(function () {
        setTotal($(this).data('name'))
    })
}

function computeRevenue() {
    total = 0
    totals = {
        1: 0,
        2: 0,
        3: 0,
        4: 0,
    }

    m = getMonth()

    $('.form-group-month input').each(function () {
        if ($(this).data('m') > m) {
            setRevenue($(this).data('m'), $(this).val())
        }

        v = parseInt($(this).val())
        if (!isNaN(v)) {
            totals[$(this).data('q')] += v
        }
    })

    q = getQuarterly()

    for (var i = 1; i <= 4; i++) {
        if (i > q) {
            setValue('#revenue', i, totals[i])
        }
    }

    $('.form-group-revenue').each(function (v) {
        total += parseInt(v)
    })

    $('#revenue').val(total)
    $('#revenue_text').html(roundText(total))
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

// 佔季營收比例
function revenueQProportion(q, v) {
    r = getRevenueQ(q)
    if (r === '' || r <= 0) {
        return 0
    }

    return Math.round((v / r) * 10000) / 100
}
