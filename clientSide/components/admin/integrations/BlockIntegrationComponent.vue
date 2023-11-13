<template>
    <div class="row mt-3">
        <div class="col">
            <h3 v-if="header">{{ header }}</h3>
            <select
                class="form-control"
                v-model="integrationSettings[settingName]"
            >
                <option v-for="[value, label] of possibleModes" :value="value">
                    {{ label }}
                </option>
            </select>
            <template
                v-if="
                    integrationSettings[settingName] &&
                    environmentSettings[integrationSettings[settingName]]
                "
            >
                <template
                    v-for="key of get_object_keys(
                        environmentSettingLabels[
                            integrationSettings[settingName]
                        ]
                    )"
                >
                    <div class="form-group">
                        <label>{{
                            environmentSettingLabels[
                                integrationSettings[settingName]
                            ][key]
                        }}</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="
                                environmentSettings[
                                    integrationSettings[settingName]
                                ][key]
                            "
                        />
                    </div>
                </template>
            </template>
            <button class="btn btn-primary mt-2" @click="emitSaveEvent">
                Сохранить
            </button>
        </div>
    </div>
</template>

<script>
import { get_object_keys } from "../../../helpers/object_interaction";
import { computed } from "vue";

export default {
    name: "BlockIntegrationComponent",
    props: {
        header: {
            type: String,
            required: false,
        },
        environmentSettings: {
            type: Object,
            required: true,
        },
        environmentSettingLabels: {
            type: Object,
            required: true,
        },
        settingName: {
            type: String,
            required: true,
        },
        integrationSettings: {
            type: Object,
            required: true,
        },
    },
    setup(props, { emit }) {
        const possibleModes = computed(() => {
            const keys = Object.keys(props.environmentSettings);
            return [["", "Не использовать"]].concat(
                keys.map((key) => [key, key])
            );
        });

        const emitSaveEvent = () => {
            emit("update:integrationSettings");
        };
        return {
            possibleModes,
            get_object_keys,
            emitSaveEvent,
        };
    },
    emits: ["update:integrationSettings"],
};
</script>

<style scoped>
</style>