$(function () {
    function setMobileTable(selector) {
        if (window.innerWidth > 768) return false;
        var tableEls = document.querySelectorAll(selector);

        if (tableEls.length === 0) return false;

        tableEls.forEach(function (tableEl) {
            var thEls = tableEl.querySelectorAll("thead tr:last-of-type th");
            var tdLabels = Array.from(thEls).map(function (el) {
                return el.innerText;
            });
            tableEl.querySelectorAll("tbody tr").forEach(function (tr) {
                var tds = Array.from(tr.querySelectorAll("td"));
                if (tds.length !== tdLabels.length) {
                    return; // не удастся точно подобрать подписи - вероятно в строке один td с надписью ничего не найдено
                }
                tds.forEach(function (td, ndx) {
                    var _tdLabels$ndx;

                    td.setAttribute(
                        "data-label",
                        (_tdLabels$ndx = tdLabels[ndx]) !== null && _tdLabels$ndx !== void 0
                            ? _tdLabels$ndx
                            : ""
                    );
                });
            });
        });
    }

    setMobileTable(".mobile-friendly table");
    $(window).on("resize", function () {
        setMobileTable(".mobile-friendly table");
    });
});
