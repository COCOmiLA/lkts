import {initAppsManagement} from "../../scripts/applications-management.js";
import {initAutofillEvents} from "../../scripts/autofill-specialty.js";
import {checkForPossibleGroups} from "../../scripts/applications-groupping";

function disableHref(e) {
    e.preventDefault();
    return null;
}

$(() => {
    $("[data-toggle='tooltip']").tooltip();
    $("[data-toggle='popover']").popover();

    initAppsManagement();

    if (enableAutofillSpecialtyOnAUniversalBasis) {
        initAutofillEvents();
    }
    checkForPossibleGroups();
});

