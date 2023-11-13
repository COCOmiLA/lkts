$("body").on("click", ".passport-remove", function () {
    var passportId = $(this).data("passport_id");

    var successFunction = function successFunction(res) {
        $("#passport-grid").html(res);
    };

    ajaxSender(
        "/abiturient/delete-passport",
        "POST",
        { passportId: passportId },
        $(this),
        successFunction,
        null,
        globalTextForAjaxTooltip
    );
    return false;
});

$("body").on("click", ".btn-save-passport", function (e) {
    e.preventDefault();
    var passportId = $(this).data("id");

    var _form = $("#form-passport" + (passportId === undefined ? "" : passportId));

    _form.yiiActiveForm("validate", true);

    return false;
});

$("body").on("click", "#btn-add-passport", function (e) {
    e.preventDefault();
    var modal = $("#create_modal_passport");
    modal.find("h4").text(modalPassportHeaderCreate);
});

$("body").on("click", ".btn-edit-passport", function (e) {
    var model_id = $(this).data("id");
    var modal = $("#edit_modal_passport_" + model_id);
    modal.find("h4").text(modalPassportHeaderEdit);
    modal.modal("show");
});

$("#create_modal_passport").on("hidden.bs.modal", function (e) {
    $(this).find("select,input:not([name='_csrf']):not(:checkbox)").val("");
    $(this).find("form").yiiActiveForm("data").submitting = false;
});

// PARENT DATA
$("body").on("click", ".parent-data-remove", function (e) {
    e.preventDefault();
    var parentDataId = $(this).data("parent_data_id");
    var self = $(this);
    var confirmMsg = modalDeleteEntry;

    if (confirm(confirmMsg)) {
        var successFunction = function successFunction(res) {
            $("#div-parent-data").html(res);
        };

        ajaxSender(
            "/abiturient/delete-parent-data",
            "POST",
            {
                parentDataId: parentDataId,
            },
            self,
            successFunction,
            null,
            globalTextForAjaxTooltip
        );
    }

    return false;
});

$("body").on("click", ".btn-save-parent-data", function (e) {
    e.preventDefault();
    var parentDataId = $(this).attr("data-id");

    var _formParent = $("#form-parent-" + parentDataId);

    _formParent.yiiActiveForm("validate", true);

    return false;
});

var parentFormRendered = false;
$("body").on("click", "#btn-add-parent", function (e) {
    var parentDataId = 0;
    var modal = $("#edit_modal_parent");
    modal.find("h4").text(modalParentHeaderCreate);

    const url = modal.data("parent_form_url");

    if (!parentFormRendered) {
        ajaxSender(
            url,
            "POST",
            { parentDataId: parentDataId > 0 ? parentDataId : null },
            $(this),
            function (res) {
                $("#edit_modal_parent_form").html(res);
                $(modal).modal("show");
                parentFormRendered = true;
                parentAddressWidgetDataForInitialization.forEach(function (item) {
                    window.initAddress(item.prefix, item.formName);
                });
            },
            null,
            globalTextForAjaxTooltip,
            "top",
            { dataType: "html" }
        );
    } else {
        // не подгружать форму с сервера второй раз
        $(modal).modal("show");
    }
});

$("body").on("click", ".btn-edit-parent", function (e) {
    parentFormRendered = false;
    const self = $(this);
    const modal = $("#edit_modal_parent");
    const url = modal.data("parent_form_url");
    const parentDataId = $(this).attr("data-id");
    modal.find("h4").text(modalParentHeaderEdit);
    $("a.parent-data-remove").css("pointer-events", "none");
    ajaxSender(
        url,
        "POST",
        { parentDataId: parentDataId > 0 ? parentDataId : null },
        self,
        function (res) {
            $("#edit_modal_parent_form").html(res);
            $(modal).modal("show");
            $("a.parent-data-remove").css("pointer-events", "auto");
            parentAddressWidgetDataForInitialization.forEach(function (item) {
                window.initAddress(item.prefix, item.formName);
            });
        },
        function () {
            $("a.parent-data-remove").css("pointer-events", "auto");
        },
        globalTextForAjaxTooltip,
        "top",
        { dataType: "html" }
    );
});

$("#create_modal_parent").on("hidden.bs.modal", function (e) {
    $(this).find("select,input").val("");
    $(this).find("form").yiiActiveForm("data").submitting = false;
});

$("body").on("submit", ".form-parent", function (e) {
    e.preventDefault();
    var _formParent = $(this);

    var successFunction = function successFunction(res) {
        $("#div-parent-data").html(res);
        $("#create_modal_parent").modal("hide");
        $("#edit_modal_parent").modal("hide");
        parentFormRendered = false;
    };

    ajaxSender(
        "/abiturient/set-parent-data",
        "POST",
        _formParent.serialize(),
        $(this).find(".btn-save-parent-data"),
        successFunction,
        null,
        globalTextForAjaxTooltip
    );
    return false;
});

$(document).on("click", ".fill-address-same", function () {
    var from_selector = $(this).attr("data-from");
    var to_selector = $(this).attr("data-to");
    var to_elems = $(to_selector).find(":input");
    to_elems.each(function () {
        var current_to_elem = $(this);

        if (current_to_elem.attr("name") === undefined) {
            return;
        }

        var tag = current_to_elem.prop("tagName").toLowerCase();
        var type = current_to_elem.prop("type");
        var name_part_to_find = "[" + $(current_to_elem).attr("name").split("[").slice(1).join("[");
        var current_from_elem = $(from_selector)
            .find(":input")
            .filter(function (idx) {
                if ($(this).attr("name") === undefined) {
                    return false;
                }

                if (tag !== $(this).prop("tagName").toLowerCase()) {
                    return false;
                }

                if (type && type !== $(this).prop("type")) {
                    return false;
                }

                return $(this).attr("name").indexOf(name_part_to_find) !== -1;
            });

        if (current_from_elem.prop("type") === "checkbox") {
            current_to_elem.get(0).checked = current_from_elem.get(0).checked;
        } else {
            if (current_to_elem.is("select")) {
                current_to_elem.empty();
                var new_elements = current_from_elem.children().clone();
                new_elements.each(function () {
                    current_to_elem.append($(this));
                });
            }

            current_to_elem.val(current_from_elem.val());
        }

        current_to_elem.prop("disabled", current_from_elem.prop("disabled"));
        current_to_elem.change();
    });
    // перенос индекса в самом конце после триггеров
    to_elems
        .filter('[id$="-postal_index"]')
        .val($(from_selector).find(":input").filter('[id$="-postal_index"]').val())
        .change();
});

$(document).on("click", ".trigger-questionary-submit", function () {
    $("#" + formId).submit();
    $("#submit-confirm-modal").modal("hide");
});

// BEFOREUNLOAD
$(function () {
    $(window).off("beforeunload");
});

window.onload = function () {
    $(".panel-spinner-loader").hide();
    $(".hidden-behind-loader").removeClass("hidden-behind-loader");
};
