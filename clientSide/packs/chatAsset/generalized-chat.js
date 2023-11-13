import "../../scss/generalized-chat.scss";
import {
    bindEventsForSelectBtn,
    initPeriodicUpdateChatPeopleList,
} from "../../components/chat/common";

$(function () {
    bindEventsForSelectBtn();

    $(".collapse-toggle-btn").click(function (e) {
        e.preventDefault();

        let collapseContentClass = $(this).data("collapse_id");

        $(`.${collapseContentClass}`).toggleClass("collapse-content-active");
    });

    initPeriodicUpdateChatPeopleList();
});
