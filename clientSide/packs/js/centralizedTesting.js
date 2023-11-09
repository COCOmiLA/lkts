$(function () {
    var collapseList = $("div[id^=collapse-centralized-testing_][id$=-collapse1]");
    for (var index = 0; index < collapseList.length; index++) {
        centralizedTestingDisabler($(collapseList[index]), true);
    }

    var centralizedTestingFieldList = $('[class*="field-bachelorresultcentralizedtesting"]');
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            warningAlertProcessor($(mutation.target));
        });
    });
    for (var index = 0; index < centralizedTestingFieldList.length; index++) {
        observer.observe(centralizedTestingFieldList[index], {
            attributes: true,
            attributeFilter: ["class"],
        });
    }
});

$("div[id^=collapse-centralized-testing_][id$=-collapse1]").on(
    "show.bs.collapse",
    function (event) {
        // Такая проверка необходима из-за того что данное прерывание срабатывает и на закрытие\открытие "датетайме пикера"
        if ($(event.target).attr("class") != "input-group date") {
            centralizedTestingDisabler($(this), false);
        }
    }
);

$("div[id^=collapse-centralized-testing_][id$=-collapse1]").on(
    "hide.bs.collapse",
    function (event) {
        // Такая проверка необходима из-за того что данное прерывание срабатывает и на закрытие\открытие "датетайме пикера"
        if ($(event.target).attr("class") != "input-group date") {
            centralizedTestingDisabler($(this), true);
        }
    }
);

/**
 * @param {object} $this - это `jQuery`-объект
 * @param {bool} disable
 *
 * @return {bool}
 */
function centralizedTestingDisabler($this, disable) {
    var formName = $this.parents("div[id^=collapse-centralized-testing_]").data("form_name");
    var formIndex = $this.parents("div[id^=collapse-centralized-testing_]").data("form_index");

    if (!formName || !formIndex) {
        return false;
    }

    $(`[id^=${formName.toLowerCase()}-${formIndex}-]`).prop("disabled", disable);

    return true;
}

/**
 * @param {object} $this - это `jQuery`-объект
 *
 * @return {bool}
 */
function warningAlertProcessor($this) {
    var formIndex = $this
        .parents("div:not([id$=-collapse1])[id^=collapse-centralized-testing_]")
        .data("form_index");

    if (!formIndex) {
        return false;
    }

    var warningAlert = $(`#warning-message-${formIndex}-centralized_testing`);
    if (warningAlert) {
        if ($this.hasClass("has-error")) {
            warningAlert.show();
        } else {
            warningAlert.hide();
        }
    } else {
        return false;
    }

    return true;
}
