import IntlTel from './intlTelInput';

$(function () {
    intlFlag();
});

function setMask(thisInput, mask = "") {
    thisInput.inputmask({ mask: mask });
    if (thisInput.hasClass("phone_code_field")) {
        const countrySelector = $("#personaldata-country_id").find(":selected").val();
        let country = "auto";
        if (countrySelector == citizenId) {
            country = "ru";
        }

        setFlag(thisInput[0], country);
    }
}

function intlFlag() {
    let country = $("#personaldata-country_id").find(":selected").val();
    setCodePhoneValue(country == citizenId);
}

function setFlag(phone, country = "auto") {
    const settings = {
        autoPlaceholder: "off",
        initialCountry: country,
        preferredCountries: ["ru", "by", "ua", "kz"], // TODO вынести список "избранных стран" в админку
    };
    const keys = Object.keys(intlTelInputGlobals.instances);
    if (keys.length > 0) {
        let hasSamePhone = false;
        for (let I = 0; I < keys.length; I++) {
            let telInput = intlTelInputGlobals.instances[I].telInput;

            const telCode = $(telInput).val().replace("+", "").replace("_", "");
            if (
                telCode.length > 0 &&
                (JSON.stringify(intlTelInputGlobals.instances[I].getSelectedCountryData()) ===
                    JSON.stringify({}) ||
                    intlTelInputGlobals.instances[I].getSelectedCountryData().dialCode != telCode)
            ) {
                const countryCode = intlTelInputGlobals.instances[I].countryCodes[telCode]?.shift();
                if (countryCode) {
                    intlTelInputGlobals.instances[I].setCountry(countryCode);
                }
            }

            if (telInput == phone) {
                if (
                    $(telInput).val().replace("+", "").replace("_", "").length < 1 &&
                    JSON.stringify(intlTelInputGlobals.instances[I].getSelectedCountryData()) !==
                    JSON.stringify({})
                ) {
                    $(telInput).val(
                        intlTelInputGlobals.instances[I].getSelectedCountryData().dialCode
                    );
                }
                hasSamePhone = true;
                break;
            }
        }
        if (!hasSamePhone) {
            let intlTel = new IntlTel(phone, settings);
            intlTel._init();
            intlTelInputGlobals.instances[intlTel.id] = intlTel
        }
    } else {
        let intlTel = new IntlTel(phone, settings);
        intlTel._init();
        intlTelInputGlobals.instances[intlTel.id] = intlTel
    }
}

function checkIfPhoneOtnEmpty(incomingPhone) {
    let phones = incomingPhone.parents("div.row").find("input");
    if (phones.length > 0) {
        let phoneVal = "";
        for (let I = 0; I < phones.length; I++) {
            let phone = $(phones[I]);

            let phoneVal =
                phoneVal + phone.val().replace("+", "").replace("(", "").replace(")", "");
        }

        return phoneVal.length > 1;
    }
}

function setCodePhoneValue(isCitizen = true) {
    let phones = $(".phone_code_field");
    if (phones.length > 0) {
        for (let I = 0; I < phones.length; I++) {
            let country = "auto";
            let phone = $(phones[I]);
            const phoneVal = phone.val().replace("+", "");
            if (isCitizen && (phoneVal.length < 1 || phoneVal == "7")) {
                country = "ru"; // TODO вынести настройку в админку
            }
            setFlag(phones[I], country);

            if (phoneVal.length < 1 || phoneVal == "7") {
                setMask(phone, phone.data("mask"));
                if (isCitizen || checkIfPhoneOtnEmpty(phone)) {
                    phone.val("7");
                } else {
                    phone.val("");
                }
            }
        }
    }
}
