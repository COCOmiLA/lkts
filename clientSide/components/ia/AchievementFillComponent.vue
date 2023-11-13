<template>
    <div
        v-if="proposition_to_fill_ia && proposition_to_fill_ia.length"
        class="alert alert-success"
        role="alert"
    >
        <p>
            Хотите заполнить данные об индивидуальном достижении из документа об
            образовании?
        </p>
        <p>
            <a href="" @click.prevent="fillAchievement">{{
                proposition_to_fill_ia
            }}</a>
        </p>
    </div>
    <input
        type="hidden"
        v-if="using_education_to_fill_confirmed && matched_education"
        name="fill_from_education"
        :value="matched_education.id"
    />
</template>

<script>
import getCurrentLine from "get-current-line";
import sendClientErrorToServer from "../../packs/js/client-error-receiver.js";
import axios from "axios";
import { computed, ref } from "vue";

export default {
    name: "AchievementFillComponent",
    props: {
        education_search_url: {
            type: String,
            required: true,
        },
        achievement_form_selector: {
            type: String,
            required: true,
        },
        app_id: {
            type: Number,
            required: true,
        },
    },
    setup(props) {
        const matched_education = ref(null);
        const using_education_to_fill_confirmed = ref(false);
        const proposition_to_fill_ia = computed(() => {
            if (!matched_education.value) {
                return null;
            }
            return `Серия: ${matched_education.value.series}, номер: ${
                matched_education.value.number
            }, выдан: ${matched_education.value.issued_by}, дата выдачи: ${
                matched_education.value.issued_at
            }, скан-копии: ${
                matched_education.value.files
                    ? matched_education.value.files.join(", ")
                    : "нет"
            }`;
        });
        window.bus.on("achievement:document_type:changed", (form_selector) => {
            if (form_selector !== props.achievement_form_selector) {
                return;
            }
            matched_education.value = null;
            using_education_to_fill_confirmed.value = false;

            const form = document.querySelector(
                props.achievement_form_selector
            );
            // find document input
            const document_input = form.querySelector(
                "[name='IndividualAchievement[document_type_id]']"
            );
            if (!document_input || !document_input.value) {
                return;
            }
            axios
                .get(props.education_search_url, {
                    params: {
                        app_id: props.app_id,
                        ia_document_type_id: document_input.value,
                    },
                })
                .then(({ data }) => {
                    if (data.matched) {
                        matched_education.value = data.matched;
                    }
                })
                .catch((error) => {
                    let eventLocation = getCurrentLine();
                    sendClientErrorToServer(
                        "error",
                        error.toJSON(),
                        eventLocation
                    );
                });
        });

        const fillAchievement = () => {
            if (!matched_education.value) {
                return;
            }
            using_education_to_fill_confirmed.value = true;
            const form = document.querySelector(
                props.achievement_form_selector
            );
            if (!form) {
                return;
            }
            // submit form in next tick to ensure input rendered
            setTimeout(() => {
                form.submit();
            }, 0);
        };
        return {
            matched_education,
            fillAchievement,
            proposition_to_fill_ia,
            using_education_to_fill_confirmed,
        };
    },
};
</script>

<style scoped>
</style>