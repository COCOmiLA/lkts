function requiredRule(attribute, russiaCode, checkIfFount) {
    const parents = $(attribute.input)?.parents('[class*="address-wrapper"]');

    if (parents.length < 1) {
        return false;
    }

    const parent = $(parents[0]);
    const homelessCheckBox = parent?.find("[id$='-homeless']")?.prop("checked");
    const notFoundCheckBox = parent?.find("[id$='-not_found']")?.prop("checked");
    const code = parent
        .find("[id$='-country_id']")
        .children("option")
        .filter(":selected")
        .data("code");

    if (notFoundCheckBox !== checkIfFount && homelessCheckBox !== true && code == russiaCode) {
        return true;
    }

    return false;
}

window.requiredRule = requiredRule;
