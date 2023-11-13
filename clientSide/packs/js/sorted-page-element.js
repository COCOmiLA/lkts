$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

$("#sorted_element_form").submit(function () {
    var result = {};
    var sortedJsons = sortable(".sortable", "serialize");
    sortedJsons.forEach((json) => {
        var pageItems = $(json.container.node);

        var itemsResult = {};
        if (json.container.itemCount > 0) {
            json.items.forEach((element) => {
                var dataElement = $(element.html);
                itemsResult[element.index] = dataElement.data("element_id");
            });
        }

        result[pageItems.attr("id")] = itemsResult;
    });

    $('[id*="-sortablepageelements"]').val(btoa(JSON.stringify(result)));
    return true;
});
