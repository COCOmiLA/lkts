<template>
    <div class="d-flex flex-row flex-wrap m-n1">
        <div class="p-1" v-for="item in jsonTags" :key="item.id">
            <comment-navigation-linker-component
                :tag="item.tag"
                :title="item.title"
                :btn_class="item.btn_class"
                :icon_class="item.icon_class"
                :tag_template="item.tag_template"
                :text_aria_id="item.text_aria_id"
                :default_alias="item.default_alias"
            ></comment-navigation-linker-component>
        </div>
    </div>
</template>

<script>
import CommentNavigationLinkerComponent from "../comment-navigation-linker/CommentNavigationLinkerComponent.vue";

export default {
    name: "CommentLinkerGroupComponent",

    components: {
        CommentNavigationLinkerComponent,
    },
    props: {
        tags: {
            type: String,
            required: true,
        },
    },
    setup(props) {
        const tmpList = JSON.parse(props.tags);
        let jsonTags = [];

        for (let I = 0; I < tmpList.length; I++) {
            const tag = tmpList[I];
            jsonTags.push(JSON.parse(tag));
        }

        return { jsonTags: jsonTags };
    },
};
</script>