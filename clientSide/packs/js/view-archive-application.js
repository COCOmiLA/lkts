$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

window.addEventListener("resize", function () {
    widthSetter();
});

function widthSetter() {
    var nodeList = $(".node-view_archive");
    if (nodeList.length > 0) {
        for (var index = 0; index < nodeList.length; index++) {
            var node = $(nodeList[index]);

            var nodeId = node.data("nodeid");
            if (typeof nodeId != "string") {
                nodeId = nodeId.toString();
            }

            var child = node.find("span.expand-icon");

            var nodeDepended = nodeId.split(".");
            //                                         👇🏾 учёт отступа
            var widthChild = node.width() - (15 * 2 + (nodeDepended.length - 2) * 20 + 3 * 2);
            //                               👆🏾 учёт двух паддингов ноды               👆🏾 учёт двух бордеров панели

            child = node.find("div.card");
            if (child.length > 0) {
                $(child).width(widthChild);
            }

            child = node.find("span.expand-icon");
            if (child.length > 0) {
                //                            👇🏾 учёт половинки иконки раскрытия
                var top = node.height() / 2 - 7;
                $(child).css({ top: `${top}px` });
            }
        }
        return true;
    }
    return false;
}

window.widthSetter = widthSetter;
