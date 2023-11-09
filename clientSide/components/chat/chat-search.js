import '../../scss/chat-search.scss';
import {
    isEmptyValue,
    periodicUpdateChatPeopleList,
} from './common'

$("#accept-filters").click(function () {
    let searchObject = initSearch();
    if (isEmptyValue(searchObject)) {
        return;
    }

    setUrlQueryFromInput(searchObject.url, searchObject.searchInputs);

    finishingSearch(searchObject.url);
});

$("#clear-filters").click(function () {
    let searchObject = initSearch();
    if (isEmptyValue(searchObject)) {
        return;
    }

    for (let I = 0; I < searchObject.searchInputs.length; I++) {
        const input = $(searchObject.searchInputs[I]);

        input.val(null);
    }

    finishingSearch(searchObject.url);
});

/**
 * @returns {object}
 */
function initSearch() {
    let searchInputs = $("#search_user_info").find("input, select");
    if (searchInputs.length < 1) {
        return null;
    }

    let url = new URL(updateChatPeopleListUrl, window.location.origin);
    clearUrlQuery(url, searchInputs);

    return {
        url: url,
        searchInputs: searchInputs,
    };
}

/**
 * @param {URL} url
 *
 * @returns {void}
 */
function finishingSearch(url) {
    updateChatPeopleListUrl = url.href;
    showLoadingSpinner();
    periodicUpdateChatPeopleList();
}

/**
 * @param {URL} url
 * @param {object} searchInputs
 *
 * @returns {void}
 */
function clearUrlQuery(url, searchInputs) {
    for (let I = 0; I < searchInputs.length; I++) {
        const input = $(searchInputs[I]);

        url.searchParams.delete(input.attr("name"));
    }
}

/**
 * @param {URL} url
 * @param {object} searchInputs
 *
 * @returns {void}
 */
function setUrlQueryFromInput(url, searchInputs) {
    for (let I = 0; I < searchInputs.length; I++) {
        const input = $(searchInputs[I]);

        if (!isEmptyValue(input.val())) {
            url.searchParams.set(input.attr("name"), input.val());
        }
    }
}

/**
 * @returns {void}
 */
function showLoadingSpinner() {
    let list = $(".people-list").find("ul.list");

    list.html("");
    list.addClass("loading-search");
}
