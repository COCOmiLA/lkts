<template>
    <button
        type="button"
        :title="title"
        :class="btn_class"
        data-placement="top"
        @click.prevent="setTag"
        data-toggle="vue-tooltip"
    >
        <i :class="icon_class"></i>
    </button>
</template>

<script>
import _ from "lodash";
import { onMounted } from "vue";

export default {
    name: "CommentNavigationLinkerComponent",

    props: {
        tag_template: {
            type: String,
            required: true,
        },
        text_aria_id: {
            type: String,
            required: true,
        },
        btn_class: {
            type: String,
            required: true,
        },
        icon_class: {
            type: String,
            required: true,
        },
        tag: {
            type: String,
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        default_alias: {
            type: String,
            required: true,
        },
    },

    setup(props) {
        onMounted(() => {
            $('[data-toggle="vue-tooltip"]').tooltip();
        });

        /**
         * @returns {void}
         */
        const setTag = () => {
            const textarea = document.getElementById(props.text_aria_id);
            if (_.isEmpty(textarea)) {
                return;
            }

            let alias = getSel(textarea);
            if (alias.length < 1) {
                alias = props.alias;
            }

            const data = {
                ALIAS: alias,
                TAG: props.tag,
            };

            const formattedTeg = templateCompile(data);
            pasteTag(textarea, formattedTeg);
        };

        /**
         * @param {JSON} data
         *
         * @returns {string}
         */
        const templateCompile = (data) => {
            const pattern = /{{\s*(\w+?)\s*}}/g; // {{property}}
            return props.tag_template.replace(
                pattern,
                (_, token) => data[token] || ""
            );
        };

        /**
         * @param {object} textarea document-объект текстового поля
         *
         * @returns {string}
         */
        const getSel = (textarea) => {
            const start = textarea.selectionStart;
            const finish = textarea.selectionEnd;

            return textarea.value.substring(start, finish).trim();
        };

        /**
         * @param {object} textarea document-объект текстового поля
         * @param {string} formattedTeg
         *
         * @returns {void}
         */
        const pasteTag = (textarea, formattedTeg) => {
            const start = textarea.selectionStart;
            const finish = textarea.selectionEnd;

            const beforeSelectText = textarea.value.substring(0, start);
            const afterSelectText = textarea.value.substring(finish);

            textarea.value = beforeSelectText + formattedTeg + afterSelectText;
        };

        return {
            setTag,
        };
    },
};
</script>