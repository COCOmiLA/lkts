<?php


namespace common\modules\abiturient\models\bachelor\changeHistory;


use common\models\Attachment;
use common\models\attachment\attachmentCollection\BaseAttachmentCollection;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\ActualAddressData;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\bachelor\BachelorDatePassingEntranceTest;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\bachelor\changeHistory\rows\AbiturientJuxtapositionRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\AgreementChangeHistoryRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\AgreementDeclineChangeHistoryRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\ApplicationAcceptRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\ApplicationDeclineRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\models\ApplicationAcceptDeclineModel;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationModerateRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationReturnRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\DefaultChangeHistoryRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\EntranceTestDateChangeHistoryRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ExamResultChangeHistoryRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ExamSetChangeHistoryRow;
use common\modules\abiturient\models\bachelor\changeHistory\rows\FileChangeHistoryRow;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\models\CommentsComing;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\PersonalData;
use Yii;









class ChangeHistoryClasses
{
    public const CLASS_UNDEFINED = 0;
    public const CLASS_ADDRESS_DATA = 1;
    public const CLASS_PERSONAL_DATA = 2;
    public const CLASS_PASSPORT_DATA = 3;
    public const CLASS_ATTACHMENT = 4;
    public const CLASS_ATTACHMENT_COLLECTION = 5;
    public const CLASS_USER_REGULATION = 6;
    public const CLASS_EDUCATION_DATA = 7;
    public const CLASS_TARGET_RECEPTION = 8;
    public const CLASS_BACHELOR_PREFERENCES = 9;
    public const CLASS_BACHELOR_SPECIALITY = 10;
    public const CLASS_EXAM_RESULT = 12;
    public const CLASS_INDIVIDUAL_ACHIEVEMENT = 13;
    public const CLASS_ABITURIENT_COMMENT = 14;
    public const CLASS_APPLICATION_ACCEPT_REJECT = 16;
    public const CLASS_AGREEMENT = 17;
    public const CLASS_AGREEMENT_DECLINE = 18;
    public const CLASS_ACTUAL_ADDRESS_DATA = 19;
    public const CLASS_ABITURIENT_QUESTIONARY = 20;
    public const CLASS_PARENT_DATA = 21;
    public const CLASS_CENTRALIZED_TESTING = 22;
    public const CLASS_ENTRANCE_TEST_DATE_PASSING = 23;

    private const _CLASSES = [
        ChangeHistoryClasses::CLASS_UNDEFINED => null,
        ChangeHistoryClasses::CLASS_ADDRESS_DATA => AddressData::class,
        ChangeHistoryClasses::CLASS_PERSONAL_DATA => PersonalData::class,
        ChangeHistoryClasses::CLASS_PASSPORT_DATA => PassportData::class,
        ChangeHistoryClasses::CLASS_ATTACHMENT => Attachment::class,
        ChangeHistoryClasses::CLASS_ATTACHMENT_COLLECTION => BaseAttachmentCollection::class,
        ChangeHistoryClasses::CLASS_USER_REGULATION => UserRegulation::class,
        ChangeHistoryClasses::CLASS_EDUCATION_DATA => EducationData::class,
        ChangeHistoryClasses::CLASS_BACHELOR_PREFERENCES => BachelorPreferences::class,
        ChangeHistoryClasses::CLASS_BACHELOR_SPECIALITY => BachelorSpeciality::class,
        ChangeHistoryClasses::CLASS_TARGET_RECEPTION => BachelorTargetReception::class,
        ChangeHistoryClasses::CLASS_EXAM_RESULT => EgeResult::class,
        ChangeHistoryClasses::CLASS_INDIVIDUAL_ACHIEVEMENT => IndividualAchievement::class,
        ChangeHistoryClasses::CLASS_ABITURIENT_COMMENT => CommentsComing::class,
        ChangeHistoryClasses::CLASS_APPLICATION_ACCEPT_REJECT => ApplicationAcceptDeclineModel::class,
        ChangeHistoryClasses::CLASS_ACTUAL_ADDRESS_DATA => ActualAddressData::class,
        ChangeHistoryClasses::CLASS_ABITURIENT_QUESTIONARY => AbiturientQuestionary::class,
        ChangeHistoryClasses::CLASS_PARENT_DATA => ParentData::class,
        ChangeHistoryClasses::CLASS_CENTRALIZED_TESTING => BachelorResultCentralizedTesting::class,
        ChangeHistoryClasses::CLASS_ENTRANCE_TEST_DATE_PASSING => BachelorDatePassingEntranceTest::class,
    ];

    private const _CLASSES_CUSTOM_ROWS = [
        ChangeHistory::CHANGE_HISTORY_FILE => FileChangeHistoryRow::class,
        ChangeHistory::CHANGE_HISTORY_EXAM_SET => ExamSetChangeHistoryRow::class,
        ChangeHistory::CHANGE_HISTORY_EXAM_POINTS => ExamResultChangeHistoryRow::class,
        ChangeHistory::CHANGE_HISTORY_ENTRANCE_TEST_DATE_PASSING => EntranceTestDateChangeHistoryRow::class,
        ChangeHistory::CHANGE_HISTORY_APPLICATION_MODERATE => ApplicationModerateRow::class,
        ChangeHistory::CHANGE_HISTORY_APPLICATION_SENT => ApplicationAcceptRow::class,
        ChangeHistory::CHANGE_HISTORY_APPLICATION_RETURN_DOCS => ApplicationReturnRow::class,
        ChangeHistory::CHANGE_HISTORY_ABITURIENT_JUXTAPOSITION => AbiturientJuxtapositionRow::class,
        ChangeHistory::CHANGE_HISTORY_APPLICATION_REJECT => ApplicationDeclineRow::class,
        ChangeHistory::CHANGE_HISTORY_NEW_AGREEMENT => AgreementChangeHistoryRow::class,
        ChangeHistory::CHANGE_HISTORY_AGREEMENT_DECLINE => AgreementDeclineChangeHistoryRow::class
    ];

    public static function getClassByID($id)
    {
        return ChangeHistoryClasses::_CLASSES[$id];
    }

    public static function getClassDescriptionByID($id)
    {
        $_CLASSES_DESCRIPTION = [
            ChangeHistoryClasses::CLASS_UNDEFINED => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_UNDEFINED" в которой произошли изменения виджета истории изменений: `Неопределено`'
            ),
            ChangeHistoryClasses::CLASS_ADDRESS_DATA => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_ADDRESS_DATA" в которой произошли изменения виджета истории изменений: `Адрес регистрации`'
            ),
            ChangeHistoryClasses::CLASS_PERSONAL_DATA => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_PERSONAL_DATA" в которой произошли изменения виджета истории изменений: `Персональные данные пользователя`'
            ),
            ChangeHistoryClasses::CLASS_PASSPORT_DATA => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_PASSPORT_DATA" в которой произошли изменения виджета истории изменений: `Паспортные данные пользователя`'
            ),
            ChangeHistoryClasses::CLASS_ATTACHMENT => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_ATTACHMENT" в которой произошли изменения виджета истории изменений: `Прикрепляемые файлы`'
            ),
            ChangeHistoryClasses::CLASS_ATTACHMENT_COLLECTION => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_ATTACHMENT_COLLECTION" в которой произошли изменения виджета истории изменений: `Прикрепляемые файлы`'
            ),
            ChangeHistoryClasses::CLASS_USER_REGULATION => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_USER_REGULATION" в которой произошли изменения виджета истории изменений: `Подтверждение нормативного документа`'
            ),
            ChangeHistoryClasses::CLASS_EDUCATION_DATA => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_EDUCATION_DATA" в которой произошли изменения виджета истории изменений: `Данные об образовании`'
            ),
            ChangeHistoryClasses::CLASS_BACHELOR_PREFERENCES => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_BACHELOR_PREFERENCES" в которой произошли изменения виджета истории изменений: `Данные о льготах и преимущественных правах`'
            ),
            ChangeHistoryClasses::CLASS_BACHELOR_SPECIALITY => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_BACHELOR_SPECIALITY" в которой произошли изменения виджета истории изменений: `Данные направлений подготовок`'
            ),
            ChangeHistoryClasses::CLASS_TARGET_RECEPTION => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_TARGET_RECEPTION" в которой произошли изменения виджета истории изменений: `Данные о целевых договорах`'
            ),
            ChangeHistoryClasses::CLASS_EXAM_RESULT => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_EXAM_RESULT" в которой произошли изменения виджета истории изменений: `Результаты вступительных испытаний`'
            ),
            ChangeHistoryClasses::CLASS_INDIVIDUAL_ACHIEVEMENT => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_INDIVIDUAL_ACHIEVEMENT" в которой произошли изменения виджета истории изменений: `Индивидуальные достижения`'
            ),
            ChangeHistoryClasses::CLASS_ABITURIENT_COMMENT => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_ABITURIENT_COMMENT" в которой произошли изменения виджета истории изменений: `Комментарий`'
            ),
            ChangeHistoryClasses::CLASS_AGREEMENT => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_AGREEMENT" в которой произошли изменения виджета истории изменений: `Согласие на зачисление`'
            ),
            ChangeHistoryClasses::CLASS_AGREEMENT_DECLINE => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_AGREEMENT_DECLINE" в которой произошли изменения виджета истории изменений: `Отзыв согласия на зачисление`'
            ),
            ChangeHistoryClasses::CLASS_ACTUAL_ADDRESS_DATA => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_ACTUAL_ADDRESS_DATA" в которой произошли изменения виджета истории изменений: `Адрес проживания`'
            ),
            ChangeHistoryClasses::CLASS_PARENT_DATA => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_PARENT_DATA" в которой произошли изменения виджета истории изменений: `Данные родителей или законных представителей`'
            ),
            ChangeHistoryClasses::CLASS_ABITURIENT_QUESTIONARY => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_ABITURIENT_QUESTIONARY" в которой произошли изменения виджета истории изменений: `Анкета`'
            ),
            ChangeHistoryClasses::CLASS_CENTRALIZED_TESTING => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_CENTRALIZED_TESTING" в которой произошли изменения виджета истории изменений: `Результат Централизованного тестирования/экзамена`'
            ),
            ChangeHistoryClasses::CLASS_ENTRANCE_TEST_DATE_PASSING => Yii::t(
                'abiturient/change-history-widget',
                'Подпись страницы "CLASS_ENTRANCE_TEST_DATE_PASSING" в которой произошли изменения виджета истории изменений: `Запись на вступительное испытание`'
            ),
        ];
        return $_CLASSES_DESCRIPTION[$id];
    }

    public static function getRowByChangeTypeId($id)
    {
        return ChangeHistoryClasses::_CLASSES_CUSTOM_ROWS[$id] ?? DefaultChangeHistoryRow::class;
    }
}
