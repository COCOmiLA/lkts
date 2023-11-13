import $ from 'jquery';
import axios from 'axios'
import {createApp} from 'vue/dist/vue.esm-bundler';
import AchievementFillComponent from "../../components/ia/AchievementFillComponent";

$(function () {
    axios.defaults.headers.common['X-CSRF-Token'] = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content');

    // find elements with ia_fill_component class
    const elements = document.querySelectorAll('.ia_fill_component');
    // create vue app for each element
    elements.forEach(element => {
        createApp({components: {AchievementFillComponent}}).mount(`#${element.id}`);
    });
})