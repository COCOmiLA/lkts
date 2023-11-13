import { debounce } from 'lodash'
import getCurrentLine from 'get-current-line';
import sendClientErrorToServer from '../../packs/js/client-error-receiver.js';

function initAddress(prefix, formName) {
    setupHomelessBlock(prefix, formName);
    setupNotFound(prefix, formName, true);

    $("#" + prefix + formName + "-homeless").change(function () {
        setupHomelessBlock(prefix, formName);
    });

    $("#" + prefix + formName + "-not_found").change(function () {
        setupNotFound(prefix, formName);
    });

    $("#" + prefix + "street_id").change(function () {
        computePostalIndexDebounced(prefix, formName);
    });

    $("#" + prefix + formName + "-region_id").change(function () {
        $("#" + prefix + "area_id").val(null).trigger("change");
    });

    $([
        "#" + prefix + formName + "-region_id",
        "#" + prefix + "area_id"
    ].join(',')).change(function () {
        $("#" + prefix + "city_id").val(null).trigger("change");
    });

    $([
        "#" + prefix + formName + "-region_id",
        "#" + prefix + "area_id",
        "#" + prefix + "city_id"
    ].join(',')).change(function () {
        $("#" + prefix + "village_id").val(null).trigger("change");
    });

    $([
        "#" + prefix + formName + "-region_id",
        "#" + prefix + "area_id",
        "#" + prefix + "city_id",
        "#" + prefix + "village_id"
    ].join(',')).change(function () {
        $("#" + prefix + "street_id").val(null).trigger("change");
    });
    $("#" + prefix + formName + "-house_number, #" + prefix + formName + "-housing_number").keyup(
        function () {
            computePostalIndexDebounced(prefix, formName);
        }
    );
}

function setupHomelessBlock(prefix, formName) {
    let ch = $("#" + prefix + formName + "-homeless").prop("checked");

    if (ch === true) {
        $("." + prefix + "address-wrapper .homeless-hide").hide();
    } else {
        $("." + prefix + "address-wrapper .homeless-hide").show();
    }
}

function setupNotFound(prefix, formName, isInit = false) {
    let code = $("#" + prefix + formName + "-country_id")
        .children("option")
        .filter(":selected")
        .data("code");
    let notfound = $("#" + prefix + formName + "-not_found").prop("checked");
    let isRussia = code === russiaCode;
    let is_readonly = $("." + prefix + "address-wrapper").attr("data-readonly") === "1";
    let foreigner_show = $("." + prefix + "address-wrapper .foreigner-show");
    let foreigner_hide = $("." + prefix + "address-wrapper .foreigner-hide");
    if (isRussia) {
        foreigner_show.hide().find(":input").prop("disabled", true);
        foreigner_hide.show().find(":input").prop("disabled", is_readonly);
    } else {
        foreigner_hide.hide().find(":input").prop("disabled", true);
        foreigner_show.show().find(":input").prop("disabled", is_readonly);
    }
    if (isRussia) {
        if (notfound) {
            $("." + prefix + "address-wrapper .notfound-hide")
                .hide()
                .find(":input")
                .prop("disabled", true);
            $("." + prefix + "address-wrapper .notfound-show")
                .show()
                .find(":input")
                .prop("disabled", is_readonly);
            $("." + prefix + "address-wrapper .index-block").removeClass("required");
            $("#" + prefix + formName + "-postal_index").val(null);
        } else {
            $("." + prefix + "address-wrapper .notfound-show")
                .hide()
                .find(":input")
                .prop("disabled", true);
            $("." + prefix + "address-wrapper .notfound-hide")
                .show()
                .find(":input")
                .prop("disabled", is_readonly);
            $("." + prefix + "address-wrapper .index-block").addClass("required");
            if (!isInit) {
                computePostalIndexDebounced(prefix, formName);
            }
        }
    }
}

function computePostalIndex(prefix, formName) {
    let val = $("#" + prefix + "street_id")
        .filter(":enabled")
        .find("option:selected")
        .val();
    let house = $("#" + prefix + formName + "-house_number")
        .filter(":enabled")
        .val();
    let housing = $("#" + prefix + formName + "-housing_number")
        .filter(":enabled")
        .val();
    if (val) {
        $.post(
            postalIndexUrl,
            {
                sid: val,
                house: house,
                housing: housing,
            }
        )
            .done((data) => {
                let notfound = $("#" + prefix + formName + "-not_found").prop("checked");
                let postal_index = $("#" + prefix + formName + "-postal_index");
                if (!notfound && data && String(data) !== "0") {
                    postal_index.val(data);
                }
            })
            .fail((e) => {
                let eventLocation = getCurrentLine();
                sendClientErrorToServer('error', e?.responseText, eventLocation);
            })
    }
}

const computePostalIndexDebounced = debounce(computePostalIndex, 600);

function countryChecker(prefix, formName) {
    let code = $("#" + prefix + formName + "-country_id")
        .children("option")
        .filter(":selected")
        .data("code");

    if (code === russiaCode) {
        $("." + prefix + "address-wrapper .foreigner-hide").show();
        $("." + prefix + "address-wrapper .foreigner-show").hide();
        $("." + prefix + "address-wrapper .russia-block").addClass("required");
        $("#" + prefix + formName + "-not_found")
            .prop("checked", false)
            .change();
    } else {
        $("." + prefix + "address-wrapper .foreigner-hide").hide();
        $("." + prefix + "address-wrapper .foreigner-show").show();
        $("." + prefix + "address-wrapper .russia-block").removeClass("required");

        if ($("#" + prefix + formName + "-not_found").prop("checked") !== true) {
            $("#" + prefix + formName + "-not_found")
                .prop("checked", true)
                .change();
        }
    }
}

$(function () {
    addressWidgetDataForInitialization.forEach(function (item) {
        initAddress(item.prefix, item.formName);
    });
});

window.initAddress = initAddress;
window.countryChecker = countryChecker;
