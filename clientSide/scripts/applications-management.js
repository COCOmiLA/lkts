import { isEmpty } from "lodash";

export function initAppsManagement() {
    $("form").submit(function () {
        window.issubmitting = true;
    });
    let codes = [];
    let spec_codes = [];
    $(".added-specs .card").each(function (index) {
        let code = $(this).data("code");

        if (codes.indexOf(code) === -1) {
            if (code) {
                codes.push(code);
            }
        }

        let specid_code = $(this).data("specid");

        if (spec_codes.indexOf(specid_code) === -1) {
            spec_codes.push(specid_code);
        }
    });

    $(".speciality-container .card .speciality-select:checked").each(function (index) {
        let code = $(this).closest(".card").data("code");

        if (codes.indexOf(code) === -1) {
            if (code) {
                codes.push(code);
            }
        }

        let specid_code = $(this).closest(".card").data("specid");

        if (spec_codes.indexOf(specid_code) === -1) {
            spec_codes.push(specid_code);
        }
    });

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

    $(".speciality-select").on("change", function () {
        let codes = [];
        let spec_codes = [];
        $(".added-specs .card").each(function (index) {
            let code = $(this).data("code");

            if (codes.indexOf(code) === -1) {
                if (code) {
                    codes.push(code);
                }
            }

            let specid_code = $(this).data("specid");

            if (spec_codes.indexOf(specid_code) === -1) {
                spec_codes.push(specid_code);
            }
        });
        $(".speciality-container .card .speciality-select:checked").each(function (index) {
            let code = $(this).closest(".card").data("code");

            if (codes.indexOf(code) === -1) {
                if (code) {
                    codes.push(code);
                }
            }

            let specid_code = $(this).closest(".card").data("specid");

            if (spec_codes.indexOf(specid_code) === -1) {
                spec_codes.push(specid_code);
            }
        });

        if (!$(this).prop("checked")) {
            $(this).parents(".card").removeClass("checked-panel");
        } else {
            $(this).parents(".card").addClass("checked-panel");
        }
    });

    $(".toggle-panel").on("click", function () {
        let specid = $(this).data("specid");

        if ($(this).hasClass("fa-caret-up")) {
            $(this).removeClass("fa-caret-up");
            $(this).addClass("fa-caret-down");
            $(this)
                .parents(".card")
                .find("#body-" + specid)
                .hide();
        } else {
            $(this).removeClass("fa-caret-down");
            $(this).addClass("fa-caret-up");
            $(this)
                .parents(".card")
                .find("#body-" + specid)
                .show();
        }
    });

    $(".decline-agree").on("click", function () {
        let id = $(this).data("id");
        $("#agreement_id_to_decline").val(id);
        $("#non_verified_agreement_id_to_decline").val(id);
    });

    $(".add-agree").on("click", function () {
        let id = $(this).data("id");
        $("#agreespec_id").val(id);
    });

    $(".add-agree").on("click", function () {
        $("#alert_null").hide();
        $("#file").prop("disabled", false);
        $("#add-ia").prop("disabled", false);
        $("#add_file_input").show();
    });

    $(".add-paid-modal-opener").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        let spec_id = $(this).attr("data-spec-id");
        let guid = $(this).attr("data-guid");

        if (spec_id && guid) {
            $("#paidContract")
                .find("#add-paid-contract-form")
                .attr("action", "/bachelor/add-paid-contract?spec_id=" + spec_id);
            $("#paidContract")
                .find("#download-paid-contract-template")
                .attr("href", "/site/download-paid-contract?spec_id=" + spec_id);
            $("#paidContract").modal("show");
        }
    });

    $(".agreement-decline-remove").on("click", function () {
        let id = $(this).data("id");

        $("#agreement_decline_id_to_remove").val(id);
    });

    $(".speciality-select").on("change", function () {
        const $this = $(this);
        const selectSpecialityId = $this.val();
        const specialityOrder = $this.parents(".modal-content")?.find(".speciality-order");

        let orderJson = parseJsonOrDefault(specialityOrder.val(), []);

        if ($this.prop("checked")) {
            orderJson.push(selectSpecialityId);
        } else {
            orderJson.splice(
                orderJson.indexOf(selectSpecialityId),
                1
            );
        }

        specialityOrder.val(JSON.stringify(orderJson));
    });
}

/**
 * @param {string} json
 * @param {*}      defaultValue
 *
 * @returns {*}
 */
function parseJsonOrDefault(json, defaultValue = {}) {
    try {
        return JSON.parse(json);
    } catch (error) {
        return defaultValue;
    }
}

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