$(".remove-speciality").on("click", function () {
    var spec_id = $(this).data("id");
    $("#remove-" + spec_id).trigger("submit");
    return false;
});

$(".reorder-spec").on("click", function () {
    var spec_id = $(this).data("id");
    var direction = $(this).data("direction");
    $("#reorder" + direction + "-" + spec_id).trigger("submit");
    return false;
}); 