<?php

namespace common\components;

use Closure;
use common\components\dictionaryManager\GetReferencesManager\GetReferencesManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\interfaces\IFillableReferenceDictionary;
use League\CLImate\TerminalObject\Dynamic\Progress;
use yii\helpers\Html;

class AppUpdate
{
    



    public const DICTIONARY_UPDATE = [
        'loadAdmissionCampaigns' => 'Справочник "Приемные кампании"',
        'loadCampaignInfo' => 'Справочник "Этапы приемных кампаний"',
        'loadStoredCompetitiveGroupReferenceType' => 'Справочник "КонкурсныеГруппы"',
        'loadSpecialities' => 'Справочник "Направления подготовки"',
        'loadDictionaryCompetitiveGroupEntranceTests' => 'Справочник "Конкурсные наборы дисциплин ЕГЭ/Экзаменов"',
        'loadGetOlympiad' => 'Справочник "Справочник олимпиад"',
        'loadGetOlympiadFilter' => 'Справочник "Список доступных для выбора олимпиад"',
        'loadDocumentTypes' => 'Справочник "Типы Документов"',
        'loadBudgetLevel' => 'Справочник "Список уровней бюджета"',
        'loadAdmissionCategory' => 'Справочник "Категории Приема"',
        'loadPrivileges' => 'Справочник "Льготы"',
        'loadSpecialMarks' => 'Справочник "Особые Отметки"',
        'loadIndividualAchievement' => 'Справочник "Индивидуальные Достижения"',
        'loadGetAvailableDocumentTypes' => 'Справочник "Список доступных типов документов для скан-копий"',
        'loadGetAdmissionProcedures' => 'Справочник "Учёт льгот и отличительных признаков"',
        'loadGender' => 'Справочник "Пол Физических Лиц"',
        'loadGetAllDocumentTypesAbiturient' => 'Справочник "Доступные типы документов для поступающего"',
        'loadAdmissionBase' => 'Справочник "Основания Поступления"',
        'loadAdmissionFeatures' => 'Справочник "Особенности Приема"',
        'loadForeignLanguages' => 'Справочник "Иностранные Языки"',
        'loadDocumentShipment' => 'Справочник "Типы Доставки Документов"',
        'loadEducationTypes' => 'Справочник "Виды Образований"',
        'loadCountry' => 'Справочник "Страны"',
        'loadOwnageForms' => 'Справочник "Формы собственности"',
        'loadReasonsForExam' => 'Справочник "Основания для сдачи вступительных экзаменов"',
        'loadFamilyTypes' => 'Справочник "Состав семьи"',
        'loadSpecialRequirements' => 'Справочник "Специальные условия"',
        'loadGetEducationLevelDocumentTypeMap' => 'Фильтр элементов справочников для образования',
        'loadGetAdditionalReceiptDateControl' => 'Регистр "Дополнительные ограничения дат приема заявлений по конкурсным группам"',
        'loadDocumentTypePropertySettings' => 'Данные о структуре полей документов',
        'loadDictionaryPredmetOfExamsSchedule' => 'Справочник "Предметы для сдачи экзаменов"',
        'loadDictionaryDateTimeOfExamsSchedule' => 'Справочник "Даты сдачи экзаменов"',
        'loadStoredProfileReferenceType' => 'Справочник "Специализации"',
        'loadStoredEducationLevelReferenceType' => 'Справочник "УровеньПодготовки"',
        'loadStoredEducationFormReferenceType' => 'Справочник "ФормаОбучения"',
        'loadStoredDisciplineReferenceType' => 'Справочник "Дисциплины"',
        'loadStoredDisciplineFormReferenceType' => 'Справочник "ВидыКонтроля"',
        'loadStoredEducationSourceReferenceType' => 'Справочник "ОснованияПоступления"',
        'loadStoredDocumentSetReferenceType' => 'Справочник "НаборыДокументовПредоставляемыхПоступающими"',
        'loadStoredVariantOfRetestReferenceType' => 'Справочник "ВариантыПерезачетаОлимпиады"',
        'loadContractorList' => 'Справочник "Контрагенты"',
        'loadStoredDocumentCheckStatusReferenceType' => 'Справочник "Статусы результата проверки документа"',
    ];

    private function createTable($message)
    {
        $thead = array_shift($message);
        $tbody = $message;

        $tableWidth = intval(100 / count($thead));

        $msg = '<table class="table table-striped"><thead><tr>';
        foreach ($thead as $value) {
            $msg .= Html::tag('th', $value, ['width' => $tableWidth . '%']);
        }
        $msg .= '</tr></thead><tbody>';
        foreach ($tbody as $key => $subarr) {
            $msg .= '<tr>';
            foreach ($subarr as $subvalue) {
                $msg .= Html::tag('td', $subvalue, ['width' => $tableWidth . '%']);
            }
            $msg .= '</tr>';
        }
        $msg .= '</tbody></table>';

        return $msg;
    }

    public function updateReferenceTable(
        $model,
        Closure $onBeginFilling = null,
        Closure $onNextReference = null,
        Closure $onEndFilling = null,
        ?Progress $progress = null
    ) {
        $result = GetReferencesManager::getReferences($model::getReferenceClassToFill());
        $references = $result->getReferences();
        $countReferences = 0;
        $touched_ids = [];
        $progressCount = 0;
        if ($progress) {
            $references = iterator_to_array($references);
            $progressCount = count($references);
            $progress->total($progressCount);
        }
        foreach ($references as $index => $reference) {
            

            if ($progress && $index % 3 == 0) {
                $progress->current($index);
            }

            
            if ($index === 0) {
                $countReferences = $result->getAllRowsCount();
                if (!is_null($onBeginFilling)) {
                    $onBeginFilling($countReferences);
                }
            }

            if (!is_null($onNextReference)) {
                if (!$onNextReference($index, $countReferences, $reference)) {
                    continue;
                }
            }
            $storedReference = ReferenceTypeManager::GetOrCreateReference($model, $reference);
            $storedReference->fillDictionary();
            $touched_ids[] = $storedReference->id;
        }
        $model::updateAll(
            [$model::getArchiveColumnName() => $model::getArchiveColumnPositiveValue()],
            ['not', ['id' => $touched_ids]]
        );
        foreach ($touched_ids as $touched_id) {
            $record = $model::findOne($touched_id);
            $record->restoreDictionary();
        }
        if (!is_null($onEndFilling)) {
            $onEndFilling();
        }

        if ($progress) {
            $progress->current($progressCount);
        }
    }


    public function needsUpdateReferenceDictionary()
    {
        return true;
    }

    public static function GetArrayOfDictionaries()
    {
        $resultArray = [];
        foreach (self::DICTIONARY_UPDATE as $key => $name) {
            $resultArray[] = [
                'name' => $name,
                'key' => $key
            ];
        }

        return $resultArray;
    }
}
