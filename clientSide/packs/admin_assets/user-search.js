import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

function checkSelection() {
    var sr = $("#custom-users-grid").yiiGridView("getSelectedRows");

    if (sr.length === 0) {
        alert("Операция не может быть выполнена, выберите пользователей");
    }

    return sr;
}

function sendAjax(url) {
    var urlParams = new URLSearchParams(window.location.search);
    $.ajax({
        url: url + "?" + urlParams.toString(),
        type: "POST",
        data: {
            arrayData: checkSelection(),
        }
    })
        .done((res) => {
            $("#custom-user-render").html(res);
        })
        .fail((res) => {
            let eventLocation = getCurrentLine();
            sendClientErrorToServer('error', res?.responseText, eventLocation);
        });
}

$("#to-archive").on("click", function (e) {
    if (checkSelection().length > 0) {
        if (confirm("Вы действительно хотите поместить выбранных пользователей в архив?"))
            sendAjax("move-to-archive");
    }

    e.preventDefault();
});

$("#from-archive").on("click", function (e) {
    if (checkSelection().length > 0) {
        if (confirm("Вы действительно ходите восстановить выбранных пользователей из архива?"))
            sendAjax("move-from-archive");
    }

    e.preventDefault();
});

//////////////////////////////////////////////////////////////////
$("#del-users").on("click", function (e) {
    if (checkSelection().length > 0) {
        if (confirm("Вы действительно хотите удалить выбранных пользователей?"))
            sendAjax("delete-users");
    }

    e.preventDefault();
});
$("#del-all-users").on("click", function (e) {
    if (
        confirm(
            "Вы действительно хотите удалить всех пользователей? ВНИМАНИЕ!!! Данное действие безвозвратно удалит всех пользователей не относящихся к числу модераторов или администраторов"
        )
    ) {
        location.href = "delete-all-users";
    }

    e.preventDefault();
});

$("#depersonalize-users").on("click", function (e) {
    if (checkSelection().length > 0) {
        if (
            confirm(
                "Операция заменит ФИО серию и номер паспорта и СНИЛС на произвольные значения у выбранных пользователей. Вы действительно хотите обезличить данные выбранных пользователей?"
            )
        ) {
            sendAjax("depersonalize-users");
        }
    }

    e.preventDefault();
});

$("#remove-applications-blocking").on("click", function (e) {
    if (checkSelection().length > 0) {
        if (
            confirm(
                "Операция уберёт статус блокировки со всех заявлений пользователя. Вы действительно хотите снять блокировку с заявлений выбранных пользователей?"
            )
        ) {
            sendAjax($(this).attr('id'));
        }
    }

    e.preventDefault();
});

///////////////////////////////////////////////////////////////////
// привязка к документа так как события отвязываются когда через ajax обновляется DOM
$(document).on("click", "#checkAll", function () {
    $("#custom-users-grid input:checkbox").not(".disabled_checkbox").prop("checked", true);
});

$(document).on("click", "#uncheckAll", function () {
    $("#custom-users-grid input:checkbox").prop("checked", false);
});
