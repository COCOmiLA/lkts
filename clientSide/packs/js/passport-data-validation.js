window.seriesIsCorrect = seriesIsCorrect;
window.numberIsCorrect = numberIsCorrect;

/**
 * @param {string} attribute
 *
 * @returns {string}
 */
function seriesIsCorrect(formName, modelId, attribute) {
    return attributeIsCorrect(formName, modelId, attribute, 4);
}

/**
 * @param {string} attribute
 *
 * @returns {string}
 */
function numberIsCorrect(formName, modelId, attribute) {
    return attributeIsCorrect(formName, modelId, attribute, 6);
}

/**
 * @param {string} attribute
 * @param {int} charsLength
 *
 * @returns {string}
 */
function attributeIsCorrect(formName, modelId, attribute, charsLength) {
    var docTypeField = $("#".concat(formName, "-document_type_id_").concat(modelId));

    if (!docTypeField) {
        return false;
    }

    var docType = docTypeField.val();
    var attrValueField = $("#".concat(formName, "-").concat(attribute, "_").concat(modelId));

    if (!attrValueField) {
        return false;
    }

    var attrValue = attrValueField.val().replace(/[ _]/gm, "");
    var docType = docTypeField.val();

    if (
        docType &&
        attrValue &&
        docType == documentTypePassportGuid &&
        attrValue.length != charsLength
    ) {
        return false;
    }

    return true;
}
