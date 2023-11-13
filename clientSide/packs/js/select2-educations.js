const { isEmpty } = require("lodash");

$('.select2-educations').on('select2:select', function (e) {
    const data = e.params.data;
    if (isEmpty(data) || data.id != data.text) { return; }

    const index = $(this)?.val().indexOf(data.id);
    if (index < 0) { return; }

    let selectedList = [];
    $(this)?.val().map(function (value) {
        if (value != data.id) {
            selectedList.push(value);
        }
    });

    $(this)?.val(selectedList).trigger('change');
});