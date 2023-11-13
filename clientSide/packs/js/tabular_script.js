window.TabularTable = {};

function dataCollector(screen_id) {
    if (!window.TabularTable[screen_id]) {
        window.TabularTable[screen_id] = [];
    }
    var data_buffer = $("form#_tabular_form" + screen_id).serializeArray();
    for (var i = 0; i < data_buffer.length; i++) {
        if (data_buffer[i].name.indexOf('new_table') > -1) {
            window.TabularTable[screen_id].push(data_buffer);
            break;
        }
    }
}

function initTabular(screening) {
    if (!window.TabularTable[screening]) {
        window.TabularTable[screening] = [];
    }

    $('#modalEditTabular').on('click', function (e) {
        $('#modalEdit').modal('hide');
        dataCollector(screening);
    });

    $(document).on('click', 'button#_tabular_button_save' + screening, function () {
        var data = $("form#_tabular_form" + screening).serializeArray();
        data.push({ name: "table_button_submit", value: "save" });

        var successFunction = function (response) {
            $("form#_tabular_form" + screening).html(response);
        };

        var ajaxResult = window.ajaxSender(
            "/site/tabular",
            "POST",
            data,
            $(this),
            successFunction,
            null,
            globalTextForAjaxTooltip
        );
        return false;
    });

    $(document).on('click', 'button#_tabular_button_delete' + screening, function () {
        var data = $("form#_tabular_form" + screening).serializeArray();
        data.push({ name: "table_button_submit", value: "delete" });

        var successFunction = function (response) {
            $("form#_tabular_form" + screening).html(response);
        };

        var ajaxResult = window.ajaxSender(
            "/site/tabular",
            "POST",
            data,
            $(this),
            successFunction,
            null,
            globalTextForAjaxTooltip
        );
        return false;
    });
    $(document).on('click', 'button#_tabular_button_add' + screening, function () {
        var data = $("form#_tabular_form" + screening).serializeArray();
        data.push({ name: "table_button_submit", value: "add" });

        var successFunction = function (response) {
            $("form#_tabular_form" + screening).html(response);
        };

        var ajaxResult = window.ajaxSender(
            "/site/tabular",
            "POST",
            data,
            $(this),
            successFunction,
            null,
            globalTextForAjaxTooltip
        );
        return false;
    });
}

$('button.close').on('click', function (e) {
    var form = $(this).parents('.modal-content').find('.tabular_form');
    if (form.length) {
        var screen_id = form.attr('id').replace('_tabular_form', '');
        dataCollector(screen_id);
        var wantExit = true;
        if (window.TabularTable && window.TabularTable[screen_id] && window.TabularTable[screen_id].length) {
            var first_array = window.TabularTable[screen_id][0];
            var last_array = window.TabularTable[screen_id][window.TabularTable.length - 1];
            for (var i = 0; i < first_array.length; i++) {
                if (first_array[i].value !== last_array[i].value) {
                    wantExit = confirm('Изменения не были сохранены. Закрыть страницу?');
                }
            }
        }
        if (wantExit) {
            window.TabularTable[screen_id] = [];
            return true;
        } else {
            return false;
        }
    }
    return true;
});

window.initTabular = initTabular;