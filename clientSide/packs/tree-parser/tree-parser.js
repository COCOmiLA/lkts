window.addEventListener("resize", function (event) {
    widthSetter();
});

function highlighter() {
    var canStop = false;
    var hash = window.location.hash;
    if (hash.length > 0) {
        var link = $("#tree_stage").treeview("search", hash);
        if (link.length > 0) {
            canStop = true;
            $("#tree_stage").treeview("selectNode", link);

            if (widthSetter()) {
                optimizeTree();
            }
        }
    } else {
        widthSetter();
    }
}

function widthSetter() {
    var nodeList = $(".node-tree_stage");
    if (nodeList.length > 0) {
        for (var index = 0; index < nodeList.length; index++) {
            var node = $(nodeList[index]);

            var nodeId = node.data("nodeid");
            if (typeof nodeId != "string") {
                nodeId = nodeId.toString();
            }

            var nodeDepended = nodeId.split(".");
            if (nodeDepended.length > 2) {
                var widthChild = node.width() - ((nodeDepended.length - 2) * 20 + 17 * 2);

                var child = node.find("div");
                if (child.length > 0) {
                    $(child).width(widthChild);
                }
            }
        }
        return true;
    }
    return false;
}

function optimizeTree() {
    $("#tree_stage").treeview("collapseAll");
    var parentNode = $("#tree_stage").treeview(
        "getParents",
        $("#tree_stage").treeview("getSelected")
    );
    var nodeList = [];

    while (parentNode.length > 0) {
        nodeList.push(parentNode);
        parentNode = $("#tree_stage").treeview("getParents", nodeList[nodeList.length - 1]);
    }

    // Если вызвать `.treeview("expandNode", nodeList);`,
    // то дерево развернётся некрасиво. Поэтому нужно вызывать родителей
    // в порядке старшинства.
    for (var index = nodeList.length; index > 0; index--) {
        $("#tree_stage").treeview("expandNode", nodeList[index - 1]);
    }
}

window.widthSetter = widthSetter;
window.highlighter = highlighter;
