<?php

namespace common\modules\abiturient\models\bachelor;

use backend\components\ApplicationTypeHistoryTrait;
use backend\models\applicationTypeHistory\ApplicationTypeHistory;
use backend\models\ManageAC;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\queries\ArchiveQuery;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\interfaces\IArchiveQueryable;
use common\models\User;
use common\modules\abiturient\models\ApplicationResubmitPermission;
use yii\base\UnknownPropertyException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


































class ApplicationType extends ActiveRecord implements IArchiveQueryable
{
    use ApplicationTypeHistoryTrait;

    public const ALIAS_FOR_EMPTY_APPLICATION = 'empty_application';
    public const NOT_BOOLEAN_FIELDS = ['campaign_id', 'archive', 'blocked', 'name'];
    public const LIST_EXCLUSIONS = ['id', 'name', 'archive', 'campaign_id', 'blocked'];

    




    const ADMISSION_STAGE_ONE = 1;
    




    const ADMISSION_STAGE_TWO = 2;

    


    public $campaignArchive;

    public function afterFind()
    {
        parent::afterFind();
        $this->campaignArchive = $this->isCampaignArchive();
    }

    public static function tableName()
    {
        return '{{%application_type}}';
    }

    


    public function rules()
    {
        return [
            [
                'campaign_id',
                'integer'
            ],
            [
                'name',
                'string',
                'max' => 1000
            ],
            [
                [
                    'campaign_id',
                    'name'
                ],
                'required'
            ],
            [
                'campaign_id',
                'unique',
                'filter' => function ($query) {
                    return $query->active();
                }
            ],
            [
                [
                    'archive',
                    'allow_add_new_education_after_approve',
                    'allow_add_new_file_to_education_after_approve',
                    'allow_delete_file_from_education_after_approve',
                    'allow_language_selection',
                    'allow_pick_dates_for_exam',
                    'allow_remove_sent_application_after_moderation',
                    'allow_secondary_apply_after_approval',
                    'allow_special_requirement_selection',
                    'archive_actual_app_on_update',
                    'blocked',
                    'can_change_date_exam_from_1c',
                    'can_see_actual_address',
                    'citizenship_is_required',
                    'disable_creating_draft_if_exist_sent_application',
                    'disable_type',
                    'display_code',
                    'display_group_name',
                    'display_speciality_name',
                    'enable_autofill_specialty_on_a_universal_basis',
                    'enable_check_ege',
                    'filter_spec_by_code',
                    'filter_spec_by_dep',
                    'filter_spec_by_detail_group',
                    'filter_spec_by_eduf',
                    'filter_spec_by_fin',
                    'filter_spec_by_spec',
                    'filter_spec_by_special_law',
                    'hide_benefits_block',
                    'hide_ege',
                    'hide_ind_ach',
                    'hide_olympic_block',
                    'hide_profile_field_for_education',
                    'hide_scans_page',
                    'hide_scans_page',
                    'hide_targets_block',
                    'minify_scans_page',
                    'minify_scans_page',
                    'moderator_allowed_to_edit',
                    'persist_moderators_changes_in_sent_application',
                    'required_actual_address',
                    'show_list',
                ],
                'boolean'
            ],
            [
                [
                    'archive',
                    'allow_add_new_education_after_approve',
                    'allow_add_new_file_to_education_after_approve',
                    'allow_delete_file_from_education_after_approve',
                    'allow_pick_dates_for_exam',
                    'archive_actual_app_on_update',
                    'blocked',
                    'can_change_date_exam_from_1c',
                    'citizenship_is_required',
                    'disable_creating_draft_if_exist_sent_application',
                    'disable_type',
                    'display_group_name',
                    'enable_check_ege',
                    'hide_benefits_block',
                    'hide_ege',
                    'hide_ind_ach',
                    'hide_olympic_block',
                    'hide_profile_field_for_education',
                    'hide_scans_page',
                    'hide_targets_block',
                    'minify_scans_page',
                    'persist_moderators_changes_in_sent_application',
                    'show_list',
                ],
                'default',
                'value' => false
            ],
            [
                [
                    'allow_language_selection',
                    'allow_remove_sent_application_after_moderation',
                    'allow_secondary_apply_after_approval',
                    'allow_special_requirement_selection',
                    'can_see_actual_address',
                    'display_code',
                    'display_group_name',
                    'display_speciality_name',
                    'enable_autofill_specialty_on_a_universal_basis',
                    'filter_spec_by_code',
                    'filter_spec_by_dep',
                    'filter_spec_by_detail_group',
                    'filter_spec_by_eduf',
                    'filter_spec_by_fin',
                    'filter_spec_by_spec',
                    'filter_spec_by_special_law',
                    'moderator_allowed_to_edit',
                    'required_actual_address',
                ],
                'default',
                'value' => true
            ],
            [
                [
                    'display_group_name',
                    'display_speciality_name',
                ],
                'required',
                'when' => function ($model) {
                    return !$this->getIsNewRecord() &&
                        empty($model->display_group_name) &&
                        empty($model->display_speciality_name);
                },
                'isEmpty' => function ($value) {
                    return !$value;
                }
            ],
        ];
    }

    




    public function getApplicationTypeSettings()
    {
        return $this->hasMany(ApplicationTypeSettings::class, ['application_type_id' => 'id']);
    }

    



    public function __set($name, $value)
    {
        $setter = static::convertAttrNameToSetter($name);
        if (
            !in_array($name, ApplicationType::LIST_EXCLUSIONS) &&
            !method_exists($this, $setter)
        ) {
            if ($this->getIsNewRecord()) {
                return;
            }

            $setting = $this->getOrCreateSettingsByName($name, $value);
            if ($setting) {
                return;
            }
        }

        parent::__set($name, $value);
    }

    




    public function __get($name)
    {
        $getter = static::convertAttrNameToGetter($name);
        if (
            !in_array($name, ApplicationType::LIST_EXCLUSIONS) &&
            !method_exists($this, $getter)
        ) {
            if ($setting = $this->searchSettingsByName($name)) {
                return $setting->value;
            }

            if ($setting = $this->getOrCreateSettingsByName($name)) {
                return $setting->value;
            }
        }

        try {
            return parent::__get($name);
        } catch (UnknownPropertyException $e) {
            if (!$this->getIsNewRecord()) {
                throw $e;
            }

            return null;
        }
    }

    




    public function searchSettingsByName(string $name)
    {
        return $this->getApplicationTypeSettings()
            ->andWhere(['name' => $name])
            ->one();
    }

    





    private function getOrCreateSettingsByName(string $name, ?string $value = null)
    {
        $setting = $this->getOrBuildSettingsByName($name);
        if (!$setting) {
            return;
        }

        if (!EmptyCheck::isEmpty($value)) {
            $setting->value = $value;
        }
        if (!$setting->save()) {
            throw new RecordNotValid($setting);
        }

        return $setting;
    }

    private function settingAttrDefaultValues()
    {
        $list = [];
        foreach ($this->rules() as $rule) {
            if (!in_array('default', $rule)) {
                continue;
            }

            $attrs = $rule[0];
            if (!is_array($attrs)) {
                $attrs = [$attrs];
            }
            $defaultValue = $rule['value'];
            foreach ($attrs as $attr) {
                $list[$attr] = $defaultValue;
            }
        }

        return $list;
    }

    private function getDefaultValue(string $name)
    {
        $list = $this->settingAttrDefaultValues();
        $value = ArrayHelper::getValue($list, $name, null);
        if (!is_null($value)) {
            return $value;
        }
    }

    




    private function getOrBuildSettingsByName(string $name)
    {
        $setting = $this->searchSettingsByName($name);
        if (!$setting) {
            $setting = new ApplicationTypeSettings();
            $setting->application_type_id = $this->id;
            $setting->name = $name;
            $value = $this->getDefaultValue($name);
            if (!is_null($value)) {
                $setting->value = $value;
            }
        }

        return $setting;
    }

    




    private function getSettingsDescriptionByName(string $name): string
    {
        $setting = $this->searchSettingsByName($name);
        if ($setting) {
            return $setting->description;
        }

        return '';
    }

    


    public function attributeLabels()
    {
        return [
            'allow_add_new_education_after_approve' => 'Разрешить добавление нового образования после зачисления',
            'allow_add_new_file_to_education_after_approve' => 'Разрешить добавление новых файлов в образование после зачисления',
            'allow_delete_file_from_education_after_approve' => 'Разрешить удаление файлов из образования после зачисления',
            'allow_language_selection' => 'Разрешить заполнение Языка для результатов Вступительных испытаний',
            'allow_pick_dates_for_exam' => 'Разрешить выбирать дату сдачи экзаменов',
            'allow_remove_sent_application_after_moderation' => 'Разрешать поступающему удаление экземпляров заявлений, после их отклонения модератором',
            'allow_secondary_apply_after_approval' => 'Разрешать поступающему подавать заявления после одобрения модератором, без необходимости получить на это разрешение', 
            'allow_special_requirement_selection' => 'Разрешить заполнение Специальных условий для результатов Вступительных испытаний',
            'archive_actual_app_on_update' => 'Создавать новую запись чистовика заявления при каждом обновлении данных из Информационной системы вуза',
            'blocked' => 'Блокировка работы',
            'campaign_id' => 'Приемная кампания 1С',
            'can_change_date_exam_from_1c' => 'Разрешить поступающему изменять даты записи на вступительные испытания, пришедшие из 1С',
            'can_see_actual_address' => 'Показывать адрес проживания',
            'citizenship_is_required' => 'Требовать заполнение гражданства',
            'disable_creating_draft_if_exist_sent_application' => 'Запретить поступающему создавать черновик, если есть отправленное на проверку заявление',
            'disable_type' => 'Отключить приемную кампанию',
            'display_code' => 'Показывать код направления подготовки',
            'display_group_name' => 'Показывать конкурсную группу в списке конкурсов',
            'display_speciality_name' => 'Показывать направление подготовки в списке конкурсов',
            'enable_autofill_specialty_on_a_universal_basis' => 'Включить автоматическое добавление конкурсной группы "на общих основаниях"',
            'enable_check_ege' => 'Проверять баллы ЕГЭ перед сохранением',
            'filter_spec_by_code' => 'Отображать фильтр по шифру при выборе направлений подготовки',
            'filter_spec_by_dep' => 'Отображать фильтр по подразделению при выборе направлений подготовки',
            'filter_spec_by_detail_group' => 'Отображать фильтр по особенностям приёма при выборе направлений подготовки',
            'filter_spec_by_eduf' => 'Отображать фильтр по форме обучения при выборе направлений подготовки',
            'filter_spec_by_fin' => 'Отображать фильтр по форме оплаты при выборе направлений подготовки',
            'filter_spec_by_spec' => 'Отображать фильтр по наименованию при выборе направлений подготовки',
            'filter_spec_by_special_law' => 'Отображать фильтр по особому праву при выборе направлений подготовки',
            'hide_benefits_block' => 'Скрывать блок льгот',
            'hide_ege' => 'Скрывать вкладку "Вступительные испытания"',
            'hide_ind_ach' => 'Скрывать вкладку "Индивидуальные достижения"',
            'hide_olympic_block' => 'Скрывать блок олимпиад',
            'hide_profile_field_for_education' => 'Скрывать поле профиля для формы док. образования',
            'hide_scans_page' => 'Скрывать вкладку "Сканы документов"',
            'hide_targets_block' => 'Скрывать блок целевых договоров',
            'minify_scans_page' => 'Отображать превью файлов на вкладке "Сканы документов"',
            'moderator_allowed_to_edit' => 'Разрешать модератору вносить изменения в данные поданные поступающим',
            'name' => 'Наименование ПК',
            'persist_moderators_changes_in_sent_application' => 'Сохранять изменения внесённые модератором в заявление поданное поступающим',
            'required_actual_address' => 'Требовать заполнения адреса проживания',
            'show_list' => 'Показать списки',
        ];
    }

    public function getRawCampaign()
    {
        return $this->hasOne(AdmissionCampaign::class, ['id' => 'campaign_id']);
    }

    public function getCampaign()
    {
        return $this->getRawCampaign()->andOnCondition([AdmissionCampaign::tableName() . '.archive' => false]);
    }

    public function getApplications()
    {
        return $this->hasMany(BachelorApplication::class, ['type_id' => 'id']);
    }

    public function getCampaignName()
    {
        if (isset($this->campaign)) {
            return $this->campaign->name;
        } elseif ($this->isCampaignArchive()) {
            return ArrayHelper::getValue($this->rawCampaign, 'name');
        } else {
            return '';
        }
    }

    public function getCampaignCode()
    {
        if (isset($this->campaign)) {
            return $this->campaign->referenceType->reference_id;
        } elseif ($this->isCampaignArchive()) {
            return ArrayHelper::getValue($this->rawCampaign, 'referenceType.reference_id');
        } else {
            return '';
        }
    }

    public function haveStageOne()
    {
        if (isset($this->campaign, $this->campaign->id)) {
            return $this->campaign->getInfo()
                ->andWhere(['>=', IndependentQueryManager::strToDateTime('date_final'), date('Y-m-d H:i:s')])
                ->exists();
        }
        return false;
    }

    public function stageTwoStarted()
    {
        if (isset($this->campaign, $this->campaign->id)) {
            return $this->campaign->getInfo()
                ->andWhere(['<', IndependentQueryManager::strToDateTime('date_final'), date('Y-m-d H:i:s')])
                ->exists();
        }
        return false;
    }

    public function haveStageTwo()
    {
        if (isset($this->campaign, $this->campaign->id)) {
            return $this->campaign->getInfo()
                ->andWhere(['>=', IndependentQueryManager::strToDateTime('date_order_start'), date('Y-m-d H:i:s')])
                ->exists();
        }
        return false;
    }

    public static function find()
    {
        return new ArchiveQuery(static::class);
    }

    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    public function isCampaignArchive()
    {
        return !!ArrayHelper::getValue($this, 'rawCampaign.archive', true);
    }

    public function needToValidateActualAddress()
    {
        return $this->can_see_actual_address && $this->required_actual_address;
    }

    


    public static function getMaxSpecialityCount()
    {
        $tnCampaignInfo = CampaignInfo::tableName();
        $tnAdmissionCampaign = AdmissionCampaign::tableName();

        return ApplicationType::find()
            ->active()
            ->select("MAX({$tnAdmissionCampaign}.max_speciality_count)")
            ->joinWith('campaign')
            ->joinWith('campaign.info')
            ->andWhere([
                '>',
                IndependentQueryManager::strToDateTime("{$tnCampaignInfo}.date_final"),
                date('Y-m-d H:i:s')
            ])
            ->scalar();
    }

    public function getCampaignModerators()
    {
        return $this->hasMany(User::class, ['id' => 'rbac_auth_assignment_user_id'])
            ->viaTable(ManageAC::tableName(), ['application_type_id' => 'id']);
    }

    public function getResubmitPermissions()
    {
        return $this->hasMany(ApplicationResubmitPermission::class, ['type_id' => 'id']);
    }

    public function toggleResubmitPermissions(User $user, bool $state): void
    {
        $permission = $this->getResubmitPermissions()->andWhere(['user_id' => $user->id])->one();
        if ($permission === null) {
            $permission = new ApplicationResubmitPermission();
            $permission->type_id = $this->id;
            $permission->user_id = $user->id;
        }
        $permission->allow = $state;
        if (!$permission->save()) {
            throw new RecordNotValid($permission);
        }
    }

    public function checkResubmitPermission(User $user): bool
    {
        if ($this->allow_secondary_apply_after_approval) {
            return true;
        }

        $perm = $this->getResubmitPermissions()->andWhere(['user_id' => $user->id])->one();
        if (!$perm) {
            return false;
        }
        return (bool)$perm->allow;
    }

    




    private static function convertAttrNameToSetter(string $name): string
    {
        $name = static::convertPathToAttrName($name);
        return "set{$name}";
    }

    




    private static function convertAttrNameToGetter(string $name): string
    {
        $name = static::convertPathToAttrName($name);
        return "get{$name}";
    }

    




    private static function convertPathToAttrName(string $name): string
    {
        return ArrayHelper::getValue(
            explode('.', $name),
            0,
            $name
        );
    }
}
