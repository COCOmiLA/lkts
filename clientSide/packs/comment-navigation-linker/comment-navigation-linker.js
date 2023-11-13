import $ from "jquery";
import { createApp } from "vue/dist/vue.esm-bundler";
import CommentLinkerGroupComponent from "../../components/comment-linker-group/CommentLinkerGroupComponent";

$(function () {
    createApp({ components: { CommentLinkerGroupComponent } }).mount('#comment-linker-group');
});
