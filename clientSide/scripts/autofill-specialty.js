export function initAutofillEvents() {
    checkSpecialtyIsSelected();

    $("#autofill_specialty-cancel-button").on("click", function (e) {
        e.preventDefault();

        let allSpecialtyIds = [];
        let specialitySelectors = $("#posable-speciality .speciality-select");
        for (let I = 0; I < specialitySelectors.length; I++) {
            let specialitySelector = $(specialitySelectors[I]);

            allSpecialtyIds.push(specialitySelector.val());
        }

        startSubmittingProcess(allSpecialtyIds, []);
    });

    $("#autofill_specialty-confirm-button").on("click", function (e) {
        e.preventDefault();

        if ($(this).prop("disabled")) {
            showErrorMessage("Выберите направления.");
            return;
        }

        let allSpecialtyIds = [];
        let selectedSpecialtyIds = [];
        let specialitySelectors = $("#posable-speciality .speciality-select");
        for (let I = 0; I < specialitySelectors.length; I++) {
            let specialitySelector = $(specialitySelectors[I]);

            allSpecialtyIds.push(specialitySelector.val());
            if (specialitySelector.prop("checked")) {
                selectedSpecialtyIds.push(specialitySelector.val());
            }
        }

        startSubmittingProcess(allSpecialtyIds, selectedSpecialtyIds);
    });

    $("button.save-spec-btn[type='button']").on("click", function () {
        $("#autofill_specialty-confirm-modal").modal("show");

        $("#autofill_specialty-confirm-modal").data(
            "save_spec_form_identification",
            $(this).data("save_spec_form_identification")
        );
    });

    $("#posable-speciality .speciality-select").on("change", function () {
        checkSpecialtyIsSelected();
    });
}
/**
 * @returns {void}
 */
function checkSpecialtyIsSelected() {
    let confirmButton = $("#autofill_specialty-confirm-button");
    confirmButton.prop("disabled", true);

    let specialitySelectors = $("#posable-speciality .speciality-select");
    if (specialitySelectors.length < 1) {
        return;
    }

    for (let I = 0; I < specialitySelectors.length; I++) {
        let specialitySelector = $(specialitySelectors[I]);

        if (specialitySelector.prop("checked")) {
            confirmButton.prop("disabled", false);
            return;
        }
    }
}

/**
 * @param {array} allSpecialtyIds
 * @param {array} selectedSpecialtyIds
 *
 * @returns {void}
 */
function startSubmittingProcess(allSpecialtyIds, selectedSpecialtyIds) {
    $.ajax({
        type: "POST",
        url: autofill_specialty_url,
        data: {
            allSpecialtyIds: allSpecialtyIds,
            selectedSpecialtyIds: selectedSpecialtyIds,
        },
        beforeSend: function beforeSend() {
            startLoading();
        },
    })
        .done(function (response) {
            stopLoading();

            if (response.message.length > 0) {
                showErrorMessage(response.message);
            }

            const identification = $("#autofill_specialty-confirm-modal")?.data("save_spec_form_identification");
            $(`#save-spec-form--${identification}`).trigger("submit");
        })
        .fail(function (response) {
            stopLoading();

            showErrorMessage(response.responseJSON.error);
        });
}

/**
 * @returns {void}
 */
function autofillSpecialtyEraseAlert() {
    $("#autofill_specialty-confirm-modal").find("#place_for_alert").html("");
}

/**
 * @param {string} message
 *
 * @returns {void}
 */
function showErrorMessage(message) {
    $("#autofill_specialty-confirm-modal").find("#place_for_alert").html(message);

    // Установка таймера скрытия алерта, по истечении 15сек.
    setTimeout(autofillSpecialtyEraseAlert, 1000 * 15);
    // Интервал выбран интуитивна, что бы сообщение можно было прочесть и при этом оно не весело бесконечно долго
}

/**
 * @returns {void}
 */
function startLoading() {
    $("autofill_specialty-cancel-button").prop("disabled", true);
    $("#autofill_specialty-confirm-button").prop("disabled", true);
    $(".no-loading-elements").hide();
    $(".loading-elements").show();
}

/**
 * @returns {void}
 */
function stopLoading() {
    $(".no-loading-elements").show();
    $(".loading-elements").hide();
}
