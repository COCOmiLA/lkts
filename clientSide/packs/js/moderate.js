import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

function openOverlaySpinner() {
    document.getElementById("submit-loader").style.display = "block";
}

function closeOverlaySpinner() {
    document.getElementById("submit-loader").style.display = "none";
}

// script 1
function removeYiiClientValidation(array) {
    array.forEach(function (field) {
        $("#questionary-form").yiiActiveForm("remove", field);
    });
}

$(".tooltip_with_image").on("inserted.bs.tooltip", function () {
    const tip = $(this).data("bs.tooltip").tip;
    if (tip) {
        $(tip).find(".tooltip-inner").addClass("tooltip_with_image p-2");
    }
});

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $(".with-tooltip").tooltip();

    let ch = $("#addressdata-homeless").prop("checked");

    if (ch === true) {
        $(".homeless-hide").hide();
    } else {
        $(".homeless-hide").show();
    }

    $("#addressdata-homeless").on("change", function () {
        let ch = $(this).prop("checked");

        if (ch === true) {
            $(".homeless-hide").hide();
        } else {
            $(".homeless-hide").show();
        }
    });

    $("#addressdata-not_found").on("change", function () {
        let ch = $(this).prop("checked");
        let code = $("#addressdata-country_id").children("option").filter(":selected").data("code");

        if (ch === true) {
            if (code == russiaCode) {
                $(".notfound-hide").hide();
                $(".notfound-show").show();
                $(".index-block").removeClass("required");
            }
        } else {
            if (code == russiaCode) {
                $(".notfound-show").hide();
                $(".notfound-hide").show();
                $(".index-block").addClass("required");
            }
        }
    });
    let code = $("#addressdata-country_id").children("option").filter(":selected").data("code");
    let notfound = $("#addressdata-not_found").prop("checked");

    if (code == russiaCode) {
        $(".foreigner-show").hide();
        $(".foreigner-hide").show();
    } else {
        $(".foreigner-hide").hide();
        $(".foreigner-show").show();
    }

    if (notfound === true && code == russiaCode) {
        $(".notfound-hide").hide();
        $(".notfound-show").show();
    } else if (notfound === false && code == russiaCode) {
        $(".notfound-show").hide();
        $(".notfound-hide").show();
    }

    let codes = [];

    $(".added-specs .card").each(function (index) {
        let code = $(this).data("code");

        if (codes.indexOf(code) === -1) {
            if (code) {
                codes.push(code);
            }
        }
    });

    $(".speciality-container .card .speciality-select:checked").each(function (index) {
        let code = $(this).closest(".card").data("code");

        if (codes.indexOf(code) === -1) {
            if (code) {
                codes.push(code);
            }
        }
    });

    $("form").areYouSure({
        message: unsavedLeave,
    });

    $("#apply-button").on("click", function () {
        $("#questionary-form").attr("action", approveActionUrl);
        openOverlaySpinner();
        $("#questionary-form").submit();
        return false;
    });

    $("#reject-button").on("click", function () {
        $("#questionary-form").attr("action", $(this).attr("href"));
        $("#questionary-form").submit();
        return false;
    });

    $("#questionary-form").on("afterValidate", function (event, messages, errorAttributes) {
        // если есть ошибки валидации то скрываем окна подтверждения и лоадер если открыт
        if (errorAttributes.length) {
            $("#confirm-decline-modal").modal("hide");
            $("#confirm-approve-modal").modal("hide");
            closeOverlaySpinner();
        }
    });

    $(".moderate-actor").on("click", function () {
        $(".moderate-actor").data("click", "0");
        $(this).data("click", "1");
    });

    $(".icon-submit").on("click", function () {
        $(this).children("form").submit();
        return false;
    });

    $(".reorder-spec").on("click", function () {
        $(this).children("form").submit();
        return false;
    });

    $(".speciality-select").on("change", function () {
        let codes = [];
        $(".added-specs .card").each(function (index) {
            const code = $(this).data("code");

            if (codes.indexOf(code) === -1) {
                if (code) {
                    codes.push(code);
                }
            }
        });
        $(".speciality-container .card .speciality-select:checked").each(function (index) {
            const code = $(this).closest(".card").data("code");

            if (codes.indexOf(code) === -1) {
                if (code) {
                    codes.push(code);
                }
            }
        });
        let specid = $(this).val();

        if (!$(this).prop("checked")) {
            $(".speciality-container .card[data-specid=" + specid + "]").removeClass(
                "checked-panel"
            );
        } else {
            $(".speciality-container .card[data-specid=" + specid + "]").addClass("checked-panel");
        }
    });

    $(".toggle-panel").on("click", function () {
        const specid = $(this).data("specid");

        if ($(this).hasClass("fa-caret-up")) {
            $(this).removeClass("fa-caret-up");
            $(this).addClass("fa-caret-down");
            $("#body-" + specid).hide();
        } else {
            $(this).removeClass("fa-caret-down");
            $(this).addClass("fa-caret-up");
            $("#body-" + specid).show();
        }
    });

    $(".save-ege").on("click", function () {
        const id = $(this).data("id"); //копируем все значения из блока вне формы

        $("#block-for-other-forms" + id)
            .find(":input")
            .filter(":enabled")
            .each(function () {
                $("<input />")
                    .attr("type", "hidden")
                    .attr("name", $(this).attr("name"))
                    .attr("value", $(this).val())
                    .appendTo("#form-update-ege-" + id);
            });
        $("#form-update-ege-" + id).submit();
        return false;
    });

    $("#add-ege").on("click", function () {
        $("#form-add-ege").submit();
        return false;
    });

    $(".moderate-actor").on("click", function () {
        $(".moderate-actor").data("click", "0");
        $(this).data("click", "1");
    });
});

function findSpecialities(modelIdentification) {
    let title = $(`#SearchSpeciality--${modelIdentification}`)?.val()            // Направление подготовки (0, 0)
        ?.toLowerCase();
    let speccode = $(`#SearchCode--${modelIdentification}`)?.val();              // Шифр специальности     (0, 1)
    let dep = $(`#search-dep--${modelIdentification}`)?.val();                   // Подразделение
    let eduf = $(`#search-eduf--${modelIdentification}`)?.val();                 // Форма обучения         (1, 1)
    let fin = $(`#search-fin--${modelIdentification}`)?.val();                   // Полное возмещение затрат
    let detail_group = $(`#search-detail_group--${modelIdentification}`)?.val(); // Особенности приёма
    let special_law = $(`#search-special_law--${modelIdentification}`)?.val();   // Наличие особого права

    let selector = ".speciality-container .card";

    if (dep) {
        selector = `${selector}[data-dep="${dep}"]`;
    }
    if (fin) {
        selector = `${selector}[data-fin="${fin}"]`;
    }
    if (eduf) {
        selector = `${selector}[data-eduf="${eduf}"]`;
    }
    if (detail_group) {
        selector = `${selector}[data-detail_group="${detail_group}"]`;
    }
    if (special_law) {
        selector = `${selector}[data-special_right="${special_law}"]`;
    }
    if (title) {
        selector = `${selector}[data-title*="${title}"]`;
    }
    if (speccode) {
        let escaped_code = speccode.split(".").join("");
        selector = `${selector}[data-code*=${escaped_code}]`;
    }

    $(`.speciality-container .card#add_application_modal_panel--${modelIdentification}`).hide();
    $(`${selector}#add_application_modal_panel--${modelIdentification}`)?.show();
    return false;
}

[
    "[id^='SearchCode']",
    "[id^='SearchSpeciality']",
].forEach(selector => {
    $(selector).on("keyup", function () {
        findSpecialities($(this).data("model_identification"));
    });
});
[
    "[id^='search-dep']",
    "[id^='search-fin']",
    "[id^='search-eduf']",
    "[id^='search-special_law']",
    "[id^='search-detail_group']",
].forEach(selector => {
    $(selector).on("change", function () {
        findSpecialities($(this).data("model_identification"));
    });
});

$(window).on("load", function () {
    $("#spinner_on_loading").hide();

    $("#hide_on_loading").show();
});

// script 3
$(function () {
    let unblockedCheckInterval;

    function isBlocked() {
        $.get(
            "is-blocked",
            {
                id: applicationId,
            },
            function (data) {
                if (!data.isBlocked) {
                    $("#create_modal_is_unblocked")
                        .modal({ show: true })
                        .on("hidden.bs.modal", function (e) {
                            $(window).off("beforeunload");
                            location.href = "/sandbox/index";
                        });
                    clearInterval(unblockedCheckInterval);
                }
            },
            "json"
        );
    }

    unblockedCheckInterval = setInterval(isBlocked, 5000);

    // BEFOREUNLOAD
    $(window).off("beforeunload");

    if (window.history && window.history.pushState) {
        window.history.pushState("forward", null, window.location.href + "#forward");
        $(window).on("popstate", function () {
            clearInterval(unblockedCheckInterval);
            window.location.href = unblockUrl;
        });
    }
});

// PASSPORT DATA
$("body").on("click", ".passport-remove", function () {
    const passportId = $(this).data("passport_id");
    const confirm_deletion = confirm(passportDeletionConfirmation);
    if (confirm_deletion) {
        $.ajax({
            url: "/sandbox/delete-passport",
            type: "POST",
            data: {
                passportId: passportId,
                appId: applicationId,
            },
            dataType: "html",
        })
            .done((res) => {
                $("#passport-grid").html(res);
            })
            .fail((err) => {
                let eventLocation = getCurrentLine();
                sendClientErrorToServer('error', err?.responseText, eventLocation);
            });
    }
    return false;
});

$("body").on("click", ".btn-save-passport", function (e) {
    e.preventDefault();
    const passportId = $(this).data("id");

    const _form = $("#form-passport" + (passportId === undefined ? "" : passportId));

    _form.yiiActiveForm("validate", true);

    return false;
});

$("body").on("click", "#btn-add-passport", function (e) {
    e.preventDefault();
    const modal = $("#create_modal_passport");
    // todo localize
    modal.find("h4").text("Создать");
});

$("body").on("click", ".btn-edit-passport", function (e) {
    const model_id = $(this).data("id");
    const modal = $("#edit_modal_passport_" + model_id);
    modal.find("h4").text(editModalHeaderlabel);
    modal.modal("show");
});

$("#create_modal_passport").on("hidden.bs.modal", function (e) {
    $(this).find("select,input").val("");

    $(this).find("form").yiiActiveForm("data").submitting = false;
});

$(document).on("beforeSubmit", '[id^="form-passport"]', function (e) {
    const input = $("<input />").attr("type", "hidden").attr("name", "appId").val(applicationId);

    $(this).append(input);
    return true;
});

$(".ia-save-form, .save-benefit-form, .save-olympiad-form, .save-target-form").on(
    "submit",
    function (e) {
        e.preventDefault();

        let _form = $(this);

        $.ajax({
            url: _form.attr("action"),
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function success(res) {
                if (!res.error) {
                    _form.find(".error-presenter").hide();

                    let row = $("#" + _form.attr("data-presentation-table-id")).find(
                        '[data-model-id="' + res.data.id + '"]'
                    );

                    for (let attr_name in res.data) {
                        if (res.data.hasOwnProperty(attr_name)) {
                            row.find('[data-attr-name="' + attr_name + '"]').html(
                                res.data[attr_name]
                            );
                        }
                    }

                    $("#" + _form.attr("data-modal-to-close")).modal("hide");
                } else {
                    _form.find(".error-presenter").html(res.error).show();
                }
            },
            error: function error(res) {
                _form.find(".error-presenter").html(ajaxErrorText).show();
            },
        });
        return false;
    }
);

// отправка данных которым не нужна доп. перерисовка
$("#ege-form").on("submit", function (e) {
    e.preventDefault();
    processSendBlockForm(this);
});
$("#others-data-form").on("submit", function (e) {
    e.preventDefault();
    processSendBlockForm(this);
});
$("#address-data-form").on("submit", function (e) {
    e.preventDefault();
    processSendBlockForm(this);
});
$("#all-preferences-form").on("submit", function (e) {
    e.preventDefault();
    processSendBlockForm(this);
});
$("#actual-address-data-form").on("submit", function (e) {
    e.preventDefault();
    processSendBlockForm(this);
});
$("#questionary-main-data-form").on("submit", function (e) {
    e.preventDefault();
    processSendBlockForm(this);
});

function processSendBlockForm(form) {
    let $form = $(form);

    $.ajax({
        url: $form.attr("action"),
        type: "POST",
        data: new FormData(form),
        processData: false,
        contentType: false,
    })
        .done(function success(res) {
            if (res.status) {
                $form.find(".error-while-saving").hide();

                $form.find(".saved-success-alert").html(res.message).show();
            } else {
                $form.find(".saved-success-alert").hide();

                $form.find(".error-while-saving").html(res.message).show();
            }
        })
        .fail(function error(res) {
            $form.find(".saved-success-alert").hide();

            $form.find(".error-while-saving").html(ajaxErrorText).show();
        })
        .always(function () {
            if (typeof checkAllApplication === "function") {
                checkAllApplication();
            }
        });
    return false;
}

// PARENT DATA
$("body").on("click", ".parent-data-remove", function (e) {
    e.preventDefault();
    let self = $(this);
    const parentDataId = $(this).data("parent_data_id");
    self.button("loading");

    if (confirm("Удалить запись?")) {
        ajaxSender(
            "/sandbox/delete-parent-data",
            "POST",
            {
                parentDataId: parentDataId,
                appId: applicationId,
            },
            self,
            function (res) {
                $("#div-parent-data").html(res);
            },
            null,
            globalTextForAjaxTooltip,
            "top",
            {
                dataType: "html",
            }
        );
    } else {
        self.button("reset");
    }

    return false;
});

$("body").on("click", ".btn-save-parent-data", function (e) {
    e.preventDefault();
    let parentDataId = $(this).attr("data-id");

    let _formParent = $("#form-parent-" + parentDataId);

    _formParent.yiiActiveForm("validate", true);

    return false;
});
let parentFormRendered = false;

$("body").on("click", "#btn-add-parent", function (e) {
    let parentDataId = 0;
    let modal = $("#edit_modal_parent");
    modal.find("h4").text(editModalHeaderlabel);

    if (!parentFormRendered) {
        ajaxSender(
            "/sandbox/parent-form",
            "POST",
            {
                parentDataId: parentDataId > 0 ? parentDataId : null,
                appId: applicationId,
            },
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
    let parentDataId = $(this).attr("data-id");
    let modal = $("#edit_modal_parent");
    modal.find("h4").text(editModalHeaderlabel);
    ajaxSender(
        "/sandbox/parent-form",
        "POST",
        {
            parentDataId: parentDataId > 0 ? parentDataId : null,
            appId: applicationId,
        },
        $(this),
        function (res) {
            $("#edit_modal_parent_form").html(res);
            $(modal).modal("show");
            $("a.parent-data-remove").css("pointer-events", "auto");
            parentAddressWidgetDataForInitialization.forEach(function (item) {
                window.initAddress(item.prefix, item.formName);
            });
        },
        function (res) {
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

    $(".btn-save-parent-data").button("loading");

    let _formParent = $(this);

    ajaxSender(
        "/sandbox/set-parent-data",
        "POST",
        _formParent.serialize() + "&appId=" + applicationId,
        $(this).find(".btn-save-parent-data"),
        function (res) {
            $("#div-parent-data").html(res);
            $("#create_modal_parent").modal("hide");
            $("#edit_modal_parent").modal("hide");
            parentFormRendered = false;
        },
        function (res) {
            $(".div-parent-errors").html(res?.responseText);
        },
        globalTextForAjaxTooltip,
        "top",
        {
            dataType: "html",
        }
    );
    return false;
});

window.onload = function () {
    $(".panel-spinner-loader").hide();
    $(".hidden-behind-loader").removeClass("hidden-behind-loader");
};
