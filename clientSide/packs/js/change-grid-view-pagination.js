function changePaginationFunc(_this) {
    var radioInput = _this.find("input");
    var radioInputNames = radioInput.attr("name");
    var radioInputValue = parseInt(radioInput.val());

    var url = new URL(window.location.toString());
    url.searchParams.set(radioInputNames, radioInputValue);
    window.location.href = url.href;
}

function toTopFunc() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

window.toTop = toTopFunc;
window.changePagination = changePaginationFunc;