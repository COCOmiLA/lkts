/**
 * @param {string} formName
 * @param {string} attribute
 * @param {int} maxLength
 *
 * @return {bool}
 */
function isLengthMoreThan(formName, attribute, maxLength) {
    var fieldVal = $(`#${formName}-${attribute}`).val();
    if (fieldVal.length > maxLength) {
        return true;
    }
    return false;
}
