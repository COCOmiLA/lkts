import { isEmptyValue } from "../../components/chat/common";

$(document).on("ready", function () {
    $("#modal-change-history-content-wrapper").fadeOut();
});

$("#changeHistoryModalButton").on("click", function (event) {
    renderChangeHistory();
});

$("#changeHistoryModal").on("hidden.bs.modal", function (event) {
    cleanChangeHistoryModal();
});

$("#update-history").on("click", function (event) {
    cleanChangeHistoryModal();

    let url = new URL(`${window.location.protocol}//${window.location.host}${pjaxUrl}`);
    url.searchParams.set('sort_direction', $("#history-sort-direction").val());
    url.searchParams.set('date_start', getDateBySelector('#history-date-start'));
    url.searchParams.set('date_end', getDateBySelector('#history-date-end'));
    url.searchParams.set('limit', infiniteScrollOffset);

    pjaxUrl = `${url.pathname}${url.search}`;

    renderChangeHistory();
});

$("#history-sort-direction").on("click", function (event) {
    const $this = $(this);
    const sortDirection = $this.val() == $this.data('value_asc') ? 'desc' : 'asc';

    $this.val($this.data(`value_${sortDirection}`));
    $this.html($this.data(`label_${sortDirection}`));
});

$("[id^='history-date-']").on("change", function (event) {
    const $this = $(this);

    const historyDateStart = getDateBySelector('#history-date-start');
    const historyDateEnd = getDateBySelector('#history-date-end');
    if (
        isEmptyValue(historyDateEnd) ||
        isEmptyValue(historyDateStart) ||
        historyDateStart <= historyDateEnd
    ) { return }

    $this.datepicker('clearDates');
});

$('#changeHistoryModal').on("scroll", function () {
    const $this = $(this);

    const scrollToEnd = $('#changeHistoryModal>.modal-dialog').height() - $(this).height();
    if (scrollToEnd > $this.scrollTop() || infiniteScrollInProcess) {
        return;
    }

    const data = {
        dateEnd: getDateBySelector('#history-date-end'),
        dateStart: getDateBySelector('#history-date-start'),
        sortDirection: $("#history-sort-direction").val(),
        infiniteScrollLimit: infiniteScrollLimit,
        infiniteScrollOffset: infiniteScrollOffset
    }

    $.ajax({
        type: "POST",
        url: infiniteScrollUrl,
        data: data,
        beforeSend: function (xhr) {
            $("#modal-change-history-loader").fadeIn();
            infiniteScrollInProcess = true;
        }
    })
        .done(function (result) {
            infiniteScrollOffset = parseInt(infiniteScrollOffset) + parseInt(infiniteScrollLimit);

            $(".change-history-ajax").append(result.appendRender);
        })
        .always(function () {
            $("#modal-change-history-loader").fadeOut();
            infiniteScrollInProcess = false;
        });
});

function renderChangeHistory() {
    $.ajax({
        type: "GET",
        url: pjaxUrl,
        data: {}
    })
        .done(function success(data) {
            $("#modal-change-history-loader").fadeOut("slow", function () {
                $("#changeHistoryModal > .modal-dialog").addClass("modal-lg");
                $("#changeHistoryModal > .modal-dialog > .modal-change-body").removeClass("min-height-modal");
                setTimeout(function () {
                    $("#modal-change-history-content").html(data);
                    $("#modal-change-history-content-wrapper").fadeIn("slow");
                }, 500);
            });
        });
}

function cleanChangeHistoryModal() {
    $("#modal-change-history-content-wrapper").fadeOut();
    $("#changeHistoryModal > .modal-dialog").removeClass("modal-lg");
    $("#changeHistoryModal > .modal-dialog > .modal-change-body").addClass("min-height-modal");
    $("#modal-change-history-loader").fadeIn();
    $("#modal-change-history-content").html("");
}

function getDateBySelector(selector) {
    const someDate = $(selector).val();

    if (isEmptyValue(someDate)) {
        return '';
    }

    return Math.floor(
        (new Date(
            someDate
        )).getTime() / 1000
    );
}