<?php

namespace common\modules\abiturient\models;

use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\AttachmentType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use Yii;
use yii\base\UserException;
use yii\bootstrap4\Html;
use yii\helpers\Url;

class CheckAllApplication
{
    private function cleanMessage(array $message): string
    {
        $message = implode(", ", $message);
        $messagePrefix = Yii::t(
            'abiturient/bachelor/check-all-application/all',
            'Текст префикса ошибки валидации данных анкеты или заявления: `Необходимо заполнить`'
        );
        $message = str_replace("{$messagePrefix} «", '', $message);
        return '<br>' . str_replace('».', '', $message);
    }

    





    public function validateAbiturientQuestionary(AbiturientQuestionary $questionary, array $additional_application_types = []): array
    {
        if (empty($questionary)) {
            return [
                false,
                ''
            ];
        }

        $message = [];

        if (!$questionary->validate()) {
            foreach ($questionary->errors as $error) {
                $message[] = $error[0];
            }
        }

        $personalData = $questionary->personalData;
        if (empty($personalData)) {
            $personalData = new PersonalData(['questionary_id' => $questionary->id]);
        }
        if ($personalData->validation_extender) {
            $personalData->validation_extender->additional_application_types = $additional_application_types;
        }

        if (!$personalData->validate()) {
            foreach ($personalData->errors as $error) {
                $message[] = $error[0];
            }
        }
        $passports = $questionary->passportData;
        if ($passports) {
            foreach ($passports as $passport) {
                if (!$passport->validate()) {
                    foreach ($passport->errors as $error) {
                        $message[] = 'Паспорт: ' . $error[0];
                    }
                }
            }
            if (!$questionary->isPassportsRequiredFilesAttached()) {
                $message[] = Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Текст ошибки при отсутствии скан-копии паспорта в анкете; при валидации данных анкеты или заявления: `Отсутствует скан-копия паспорта`'
                );
            }
            if (!$questionary->isPreviousPassportsFilled()) {
                $message[] = Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Текст ошибки при отсутствии информации о предыдущем паспорте в анкете: `Отсутствует информация о предыдущем документе, удостоверяющем личность.`'
                ) . ' ' . Html::a(
                    Yii::t(
                        'abiturient/bachelor/check-all-application/all',
                        'Текст ссылки для пропуска проверки на наличие предыдущих документов: `У меня нет информации о предыдущих документах, удостоверяющих личность.`'
                    ),
                    Url::to(['/abiturient/have-no-previous-passport', 'id' => $questionary->id]),
                );
            }
        } else {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при отсутствии паспорта в анкете; при валидации данных анкеты или заявления: `Не указано ни одного паспорта`'
            );
        }
        if (!$questionary->isRequiredCommonFilesAttached()) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при отсутствии обязательной скан-копии в анкете; при валидации данных анкеты: `Не заполнены обязательные скан-копии в разделе Анкета`'
            );
        }
        $parentsData = $questionary->parentData ?? [];

        foreach ($parentsData as $parentData) {
            
            $parentPersonalData = $parentData->personalData;
            $parent = $parentData->stringify();
            if (is_null($parentPersonalData)) {
                $message[] = Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Текст ошибки при добавлении родителя с не заполненным персональными данными; при валидации данных анкеты или заявления: `Для добавленного родителя или законного представителя ({parent}) отсутствуют персональные данные.`',
                    ['parent' => $parent]
                );
                continue;
            }
            if (is_null($parentPersonalData->citizenship) && QuestionarySettings::getSettingByName('require_ctitizenship_parent')) {
                $message[] = Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Текст ошибки при добавлении родителя с не заполненным данными о гражданстве; при валидации данных анкеты или заявления: `Для добавленного родителя или законного представителя ({parent}) отсутствуют данные о гражданстве.`',
                    ['parent' => $parent]
                );
            }
        }

        $addressData = $questionary->addressData;
        if (empty($addressData)) {
            $addressData = new AddressData(['questionary_id' => $questionary->id]);
        }

        if (!$addressData->validate()) {
            foreach ($addressData->errors as $error) {
                $message[] = $error[0];
            }
        }

        
        $actualAddressData = $questionary->getOrCreateActualAddressData(false);
        if ($actualAddressData->validation_extender) {
            $actualAddressData->validation_extender->additional_application_types = $additional_application_types;
            $actualAddressData->validation_extender->modelPreparationCallback();
        }

        if (!$actualAddressData->validate()) {
            foreach ($actualAddressData->errors as $error) {
                $message[] = $error[0];
            }
        }

        $missing_attachment_type_ids = $questionary->getNotFilledRequiredCommonAttachmentTypeIds();
        $missing_attachment_types = AttachmentType::find()->andWhere(['id' => $missing_attachment_type_ids])->all();
        foreach ($missing_attachment_types as $attachment_type) {
            $message[] = $attachment_type->name;
        }

        if (empty($message)) {
            return [
                true,
                ''
            ];
        } else {
            return [
                false,
                $this->cleanMessage($message)
            ];
        }
    }

    public function validateAbiturientIalist(BachelorApplication $application): array
    {
        $ind_achs = $application->individualAchievements;

        $message = [];

        foreach ($ind_achs as $ach) {
            if (!$ach->validate()) {
                foreach ($ach->errors as $error) {
                    $message[] = $error[0];
                }
            }
        }
        if (!$application->isIndividualAchievementsRequiredFilesAttached()) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при отсутствии скан-копии индивидуального достижения в заявлении; при валидации данных заявления: `Отсутствует скан-копия индивидуального достижения`'
            );
        }
        if (empty($message)) {
            return [
                true,
                ''
            ];
        } else {
            return [
                false,
                $this->cleanMessage($message)
            ];
        }
    }

    public function checkBlockAndUpdate(BachelorApplication $application): array
    {
        $message = [];

        [$update, $block] = NeedBlockAndUpdateProcessor::getProcessedNeedBlockAndUpdate($application);
        if ($update) {
            $is_versions_mismatch = UserReferenceTypeManager::IsUserRefDataVersionOutdated($application->user);
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст при невозможности подать заявление так как в Информационной системе вуза есть заявление с более поздней датой: `В приёмной кампании обнаружено заявление с более поздней датой, необходимо актуализировать текущее заявление.`'
            );
            if ($is_versions_mismatch) {
                $message[] = Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Сообщение об обнаруженном различии версий данных анкеты в Информационной системе вуза и портале: `Различаются версии данных Анкеты в Личном кабинете поступающего и Информационной системе вуза, необходимо актуализировать данные анкеты перед подачей заявления.`'
                );
            }
        }
        
        if ($block && !Yii::$app->configurationManager->sandboxEnabled) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст при невозможности подать заявление так как в Информационной системе вуза есть не проведённые сущности: `Заявление заблокировано. Для разблокировки обратитесь в приемную кампанию вуза.`'
            );
        }
        if (empty($message)) {
            return [
                true,
                ''
            ];
        } else {
            return [
                false,
                $this->cleanMessage($message)
            ];
        }
    }

    





    public function validateBachelorEge(bool $hide_ege, BachelorApplication $application): array
    {
        if ($hide_ege) {
            return [
                true,
                ''
            ];
        }

        $message = [];

        foreach ($application->getSavedEgeResults() as $ege) {
            if (!$ege->validate()) {
                foreach ($ege->errors as $error) {
                    $message[] = $error[0];
                }
            }
        }

        if ($application->haveUnstagedDisciplineSet()) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки для отсутствующего набора ВИ; при валидации данных анкеты или заявления: `Необходимо подтвердить набор вступительных испытаний`'
            );
        }

        if ($application->haveUnstagedDisciplineResult()) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при не сохранённых результатах ВИ; при валидации данных анкеты или заявления: `Необходимо сохранить результаты вступительных испытаний`'
            );
        }

        $not_filled_attachment_type_ids = $application->getNotFilledRequiredExamsScanTypeIds();

        $required_attachment_types = AttachmentType::find()->andWhere(['id' => $not_filled_attachment_type_ids])->all();
        foreach ($required_attachment_types as $attachment_type) {
            $message[] = $attachment_type->name;
        }

        $haveValidationErrors = false;
        if ($application->type->enable_check_ege) {
            $check_errors = [];
            foreach ($application->specialities as $speciality) {
                $check_error = $speciality->checkBalls();
                if ($check_error !== null) {
                    $check_errors[] = $check_error;
                }
            }
            if ($check_errors) {
                $haveValidationErrors = true;
                \Yii::$app->session->setFlash('checkEgeErrorsAbit', json_encode($check_errors));
            }
        }

        if ($haveValidationErrors) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки валидации результатов ВИ; при валидации данных анкеты или заявления: `Ошибка валидации результатов вступительных испытаний`'
            );
        }

        if (empty($message)) {
            return [
                true,
                ''
            ];
        } else {
            return [
                false,
                $this->cleanMessage($message)
            ];
        }
    }

    public function validateBachelorEducation(BachelorApplication $application): array
    {
        $educations = $application->educations;

        $message = [];

        if (empty($educations)) {
            $educations = [new EducationData()];
        }
        foreach ($educations as $education) {
            if (!$education->validate()) {
                foreach ($education->errors as $error) {
                    $message[] = $error[0];
                }
            }
        }
        if (!$application->isEducationDocumentsRequiredFilesAttached()) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при отсутствии скан-копии документа об образовании в заявлении; при валидации данных заявления: `Отсутствует скан-копия документа об образовании`'
            );
        }

        $not_filled_attachment_type_ids = $application->getNotFilledRequiredEducationScanTypeIds();
        $required_attachment_types = AttachmentType::find()->andWhere(['id' => $not_filled_attachment_type_ids])->all();
        foreach ($required_attachment_types as $attachment_type) {
            $message[] = $attachment_type->name;
        }

        if (empty($message)) {
            return [
                true,
                ''
            ];
        } else {
            return [
                false,
                $this->cleanMessage($message)
            ];
        }
    }

    





    public function validateBachelorPreferences(BachelorApplication $application): array
    {
        $message = [];

        if (!$application->isBachelorPreferencesRequiredFilesAttached()) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при отсутствии скан-копии документа об образовании в заявлении; при валидации данных заявления: `Отсутствует скан-копия документа льготы или преимущественного права`'
            );
        }
        if (!$application->isBachelorTargetReceptionsRequiredFilesAttached()) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/all',
                'Текст ошибки при отсутствии скан-копии документа об образовании в заявлении; при валидации данных заявления: `Отсутствует скан-копия документа о целевом наборе`'
            );
        }
        if (empty($message)) {
            return [
                true,
                ''
            ];
        } else {
            return [
                false,
                $this->cleanMessage($message)
            ];
        }
    }

    





    public function validateBachelorApplication(BachelorApplication $application): array
    {
        $message = [];
        $specialities = $application->specialities;

        if (empty($specialities)) {
            $message[] = Yii::t(
                'abiturient/bachelor/check-all-application/application',
                'Текст ошибки проверки заявления если не выбрано ни одного НП: `Для подачи заявления необходимо выбрать хотя бы одно направление подготовки`'
            );
        }

        $actual_application_container = null; 
        foreach ($specialities as $speciality) {
            $speciality->scenario = BachelorSpeciality::SCENARIO_FULL_VALIDATION;
            if (!$speciality->validate() || !$speciality->validateAgreementDate()) {
                foreach ($speciality->errors as $errors) {
                    foreach ($errors as $error) {
                        $message[] = $error;
                    }
                }
            }

            [$canSend, $messageSend] = $speciality->canSendByPeriod(
                $actual_application_container,
                $application->hasAnyNotVerifiedAgreementEntity()
            );
            if (!$canSend) {
                $message[] = $messageSend;
            }
        }

        $not_filled_attachment_type_ids = $application->getNotFilledRequiredSpecialitiesScanTypeIds();
        $required_attachment_types = AttachmentType::find()->andWhere(['id' => $not_filled_attachment_type_ids])->all();
        $missed_attachments = [];
        foreach ($required_attachment_types as $attachment_type) {
            $missed_attachments[] = $attachment_type->name;
        }
        if ($missed_attachments) {
            $message[] = Yii::t('abiturient/bachelor/check-all-application/application', 'Текст сообщающий о нехватке обязательных скан-копий: `Необходимо приложить скан-копии:`') . ' ' . implode(', ', $missed_attachments);
        }

        if (!$application->type->checkResubmitPermission($application->user) && $application->hasApprovedApplication()) {
            $message[] = Yii::t('abiturient/bachelor/check-all-application/all', 'Текст ошибки при повторной подаче заявления: `В данную приёмную капанию запрещена подача заявлений после одобрения модератором, для повторной подачи заявления необходимо обратиться в приёмную кампанию.`');
        }

        if (empty($message)) {
            return [
                true,
                ''
            ];
        } else {
            return [
                false,
                $this->cleanMessage($message)
            ];
        }
    }

    public function checkAllApplication(BachelorApplication $application, bool $write_flash = true)
    {
        $response = [];

        $questionary = $application->abiturientQuestionary;

        [$validate, $message] = $this->validateAbiturientQuestionary($questionary);
        if (!$validate) {
            $response[] = [
                'url' => Url::toRoute(['/abiturient/questionary']),
                'title' => Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Подпись ссылки на раздел "анкеты" в котором произошла ошибка; при валидации данных анкеты или заявления: `Анкета: `'
                ),
                'message' => $message,
            ];
        }

        [$validate, $message] = $this->validateBachelorEducation($application);
        if (!$validate) {
            $response[] = [
                'url' => Url::toRoute(['bachelor/education', 'id' => $application->id]),
                'title' => Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Подпись ссылки на раздел "образование" в котором произошла ошибка; при валидации данных анкеты или заявления: `Образование: `'
                ),
                'message' => $message,
            ];
        }

        [$validate, $message] = $this->validateBachelorPreferences($application);
        if (!$validate) {
            $response[] = [
                'url' => Url::toRoute(['bachelor/accounting-benefits', 'id' => $application->id]),
                'title' => Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Подпись ссылки на раздел "льготы" в котором произошла ошибка; при валидации данных заявления: `Особые условия поступления: `'
                ),
                'message' => $message,
            ];
        }

        [$validate, $message] = $this->validateBachelorApplication($application);
        if (!$validate) {
            $response[] = [
                'url' => Url::toRoute(['bachelor/application', 'id' => $application->id]),
                'title' => Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Подпись ссылки на раздел "направлений подготовки" в котором произошла ошибка; при валидации данных анкеты или заявления: `Направления подготовки: `'
                ),
                'message' => $message,
            ];
        }

        [$validate, $message] = $this->validateBachelorEge($application->type->hide_ege, $application);
        if (!$validate) {
            $response[] = [
                'url' => Url::toRoute(['bachelor/ege', 'id' => $application->id]),
                'title' => Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Подпись ссылки на раздел "вступительных испытаний" в котором произошла ошибка; при валидации данных анкеты или заявления: `Вступительные испытания: `'
                ),
                'message' => $message,
            ];
        }

        [$validate, $message] = $this->validateAbiturientIalist($application);
        if (!$validate) {
            $response[] = [
                'url' => Url::toRoute(['/abiturient/ialist']),
                'title' => Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Подпись ссылки на раздел "индивидуальных достижений" в котором произошла ошибка; при валидации данных анкеты или заявления: `Индивидуальные достижения: `'
                ),
                'message' => $message,
            ];
        }
        [$validate, $message] = $this->checkBlockAndUpdate($application);
        if (!$validate) {
            $response[] = [
                'url' => Url::toRoute(['bachelor/application', 'id' => $application->id]),
                'title' => Yii::t(
                    'abiturient/bachelor/check-all-application/all',
                    'Текст при невозможности подать заявление из-за конфликтов данных с Информационной системой вуза: `Конфликт данных с заявлением в приёмной кампании`'
                ),
                'message' => $message,
            ];
        }
        if ($write_flash) {
            Yii::$app->session->setFlash('resultOfCheckingAllApplication', $response);
        }

        return $response;
    }

    public function handleSentToModerateApplicationCheck(?BachelorApplication $application): array
    {
        if ($application && $application->draft_status == IDraftable::DRAFT_STATUS_CREATED && $application->hasSentToModerateRecordInHistory()) {
            return $this->checkAllApplication($application, false);
        }
        return [];
    }
}
