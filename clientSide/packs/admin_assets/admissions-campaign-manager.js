function setAllAdmissionsCampaign() {
    if (
        $('[id$="-application_type_id"]').length ===
        $('[id$="-application_type_id"]:checkbox:checked').length
    ) {
        $("#manage_all_ac").prop("checked", true);
    } else {
        $("#manage_all_ac").prop("checked", false);
    }
}

$(function () {
    setAllAdmissionsCampaign();

    $("#manage_all_ac").on("change", function () {
        if ($("#manage_all_ac").is(":checked")) {
            $('[id$="-application_type_id"]').prop("checked", true);
        } else {
            $('[id$="-application_type_id"]').prop("checked", false);
        }
    });

    $('[id$="-application_type_id"]').on("change", function () {
        setAllAdmissionsCampaign();
    });
});
