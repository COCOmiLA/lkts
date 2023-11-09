import $ from 'jquery';
import axios from 'axios'
import {createApp} from 'vue/dist/vue.esm-bundler';
import IntegrationsComponent from "../../components/admin/integrations/IntegrationsComponent";

$(function () {
    axios.defaults.headers.common['X-CSRF-Token'] = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content');

    const element = document.querySelector('#service_integration');
    createApp({components: {IntegrationsComponent}}).mount(`#${element.id}`);
})