$(function () {
    const firstSelectors = $(".first_selector");

    if (firstSelectors && firstSelectors.length > 0) {
        for (let I = 0; I < firstSelectors.length; I++) {
            const selector = $(firstSelectors[I]).find("select");
            checkDatePassing(selector);
        }
    }
});

/**
 * @param {object} $selector - это `jQuery`-объект селектора
 *
 * @returns {void}
 */
function checkDatePassing($this) {
    const thisId = $this.attr("id");
    const splits = thisId.split("-");
    const attrName = splits[splits.length - 1];
    const formName = splits.slice(0, splits.length - 1).join("-");
    const parentFormName = splits.slice(0, splits.length - 2).join("-");
    const isDisabled = $this.data("only_read");
    $(`[id^="${formName}-"][id!="${thisId}"]`).prop("disabled", true);
    const parentSelectorVal = $(`#${parentFormName}-${attrName}`).val();

    if (parentSelectorVal) {
        const relations = $this.data("relations")[parentSelectorVal];

        if (relations && relations.length > 0) {
            if (isDisabled != true) {
                $this
                    .parent()
                    .parent()
                    .find(`[id^="${formName}-"]`)
                    .prop("disabled", false);
            }

            if (!relations.includes(parseInt($this.val()))) {
                $this.val("");
            }

            $this.children('option[value!=""]').hide();

            for (let I = 0; I < relations.length; I++) {
                const availableValue = relations[I];
                $this.children(`option[value^="${availableValue}"]`).show();
            }
        } else {
            $this.val("");
            $this.children('option[value!=""]').hide();
        }
    }

    if (!parentSelectorVal && typeof parentSelectorVal == "string") {
        $this.val("");
        $this.children('option[value!=""]').hide();
    }

    const selectors = $(
        `[id^="${formName}-"][id*="-${attrName}"][id!="${thisId}"]`
    );

    if (selectors && selectors.length > 0) {
        const $selector = $(selectors[0]);
        checkDatePassing($selector);
    }
}

window.checkDatePassing = checkDatePassing;
