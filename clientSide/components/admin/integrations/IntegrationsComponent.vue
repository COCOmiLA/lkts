<template>
    <div class="alert alert-success" v-if="success_message">
        {{ success_message }}
    </div>
    <div class="alert alert-danger" v-if="error_message">
        {{ error_message }}
    </div>
    <BlockIntegrationComponent
        v-if="
            integrationSettings && integrationSettings.sms_sender !== undefined
        "
        header="Настройка СМС-рассылки"
        setting-name="sms_sender"
        :integration-settings="integrationSettings"
        :environment-settings="smsEnvironmentSettings"
        :environment-setting-labels="smsEnvironmentSettingLabels"
        @update:integrationSettings="saveSettings"
    ></BlockIntegrationComponent>
    <BlockIntegrationComponent
        v-if="
            integrationSettings &&
            integrationSettings.telegram_bot_sender !== undefined
        "
        header="Настройка Telegram-рассылки"
        setting-name="telegram_bot_sender"
        :integration-settings="integrationSettings"
        :environment-settings="telegramEnvironmentSettings"
        :environment-setting-labels="telegramEnvironmentSettingLabels"
        @update:integrationSettings="saveSettings"
    >
    </BlockIntegrationComponent>
</template>

<script>
import getCurrentLine from "get-current-line";
import sendClientErrorToServer from "../../../packs/js/client-error-receiver.js";
import { computed, ref, toRef } from "vue";
import BlockIntegrationComponent from "./BlockIntegrationComponent";
import axios from "axios";

export default {
    name: "IntegrationsComponent",
    props: {
        integrationSettings: {
            type: Object,
            required: true,
        },
        smsDeliverers: {
            type: Object,
            required: true,
        },
        telegramSettings: {
            type: Object,
            required: true,
        },
        saveSettingsUrl: {
            type: String,
            required: true,
        },
    },
    setup(props) {
        const integrationSettings = ref(props.integrationSettings);
        const split_settings_to_values_and_labels = (settings) => {
            const env_settings = {};
            const env_setting_labels = {};
            for (const deliver_type of Object.keys(settings)) {
                env_settings[deliver_type] = {};
                env_setting_labels[deliver_type] = {};
                for (const [setting_name, [label, value]] of Object.entries(
                    settings[deliver_type]
                )) {
                    env_settings[deliver_type][setting_name] =
                        value === false ? "" : value;
                    env_setting_labels[deliver_type][setting_name] = label;
                }
            }
            return [env_settings, env_setting_labels];
        };
        const [sms_env_settings, sms_env_setting_labels] =
            split_settings_to_values_and_labels(props.smsDeliverers);
        const smsEnvironmentSettings = ref(sms_env_settings);
        const smsEnvironmentSettingLabels = ref(sms_env_setting_labels);

        const [telegram_env_settings, telegram_env_setting_labels] =
            split_settings_to_values_and_labels(props.telegramSettings);
        const telegramEnvironmentSettings = ref(telegram_env_settings);
        const telegramEnvironmentSettingLabels = ref(
            telegram_env_setting_labels
        );

        const flatten_environment_settings = computed(() => {
            const result = {};
            for (const [_, settings] of Object.entries(
                smsEnvironmentSettings.value
            )) {
                for (const [setting_name, value] of Object.entries(settings)) {
                    result[setting_name] = value;
                }
            }
            for (const [_, settings] of Object.entries(
                telegramEnvironmentSettings.value
            )) {
                for (const [setting_name, value] of Object.entries(settings)) {
                    result[setting_name] = value;
                }
            }
            return result;
        });

        const saveSettings = async () => {
            success_message.value = "";
            error_message.value = "";
            axios
                .post(props.saveSettingsUrl, {
                    integration_settings: integrationSettings.value,
                    environment_settings: flatten_environment_settings.value,
                })
                .then(({ data }) => {
                    if (data.status) {
                        success_message.value = "Настройки успешно сохранены";
                    } else {
                        error_message.value = data.error;
                    }
                })
                .catch((error) => {
                    error_message.value = "Не удалось сохранить настройки";
                    let eventLocation = getCurrentLine();
                    sendClientErrorToServer(
                        "error",
                        error_message.value,
                        eventLocation
                    );
                });
        };

        const error_message = ref("");
        const success_message = ref("");
        return {
            integrationSettings,
            saveSettings,
            smsEnvironmentSettings,
            smsEnvironmentSettingLabels,
            telegramEnvironmentSettings,
            telegramEnvironmentSettingLabels,
            error_message,
            success_message,
        };
    },
    components: {
        BlockIntegrationComponent,
    },
};
</script>

<style scoped>
</style>