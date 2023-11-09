<?php


namespace common\models\dictionary\StoredReferenceType;

use common\models\dictionary\StoredReferenceType\RestoreHandler\BaseRestoreHandler;
use common\models\interfaces\IFillableReferenceDictionary;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorResultCentralizedTesting;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\PassportData;
use Yii;
use yii\bootstrap4\Html;
use yii\helpers\Inflector;




class StoredDocumentCheckStatusReferenceType
extends StoredReferenceType
implements IFillableReferenceDictionary, IRestorableReferenceDictionary
{
    public static function tableName()
    {
        return '{{%document_check_status_reference_type}}';
    }

    protected static $required_fields = [
        'reference_name',
        'reference_class_name',
        'archive',
    ];

    


    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'humanReadableName',
                        'human_readable_name',
                    ],
                    'string',
                    'max' => 255
                ],
                [
                    [
                        'icon_class',
                        'icon_color',
                    ],
                    'string',
                    'max' => 50
                ]
            ]
        );
    }

    


    public function attributeLabels()
    {
        return [
            'humanReadableName' => $this->reference_name,
            'icon_class' => Yii::t('backend', 'Тип пиктограммы'),
            'icon_color' => Yii::t('backend', 'Цвет пиктограммы'),
        ];
    }

    public static function getReferenceClassToFill(): string
    {
        return 'Перечисление.СтатусыПроверкиДокументовПоступающих';
    }

    


    public function fillDictionary()
    {
    }

    public function restoreDictionary()
    {
        (new BaseRestoreHandler(
            $this,
            PassportData::class,
            'document_check_status_ref_id'
        ))->restore();

        (new BaseRestoreHandler(
            $this,
            EducationData::class,
            'document_check_status_ref_id'
        ))->restore();

        (new BaseRestoreHandler(
            $this,
            BachelorPreferences::class,
            'document_check_status_ref_id'
        ))->restore();

        (new BaseRestoreHandler(
            $this,
            IndividualAchievement::class,
            'document_check_status_ref_id',
            [IndividualAchievement::getArchiveColumn() => IndividualAchievement::getArchiveValue()]
        ))->restore();

        (new BaseRestoreHandler(
            $this,
            BachelorTargetReception::class,
            'document_check_status_ref_id'
        ))->restore();

        (new BaseRestoreHandler(
            $this,
            BachelorResultCentralizedTesting::class,
            'document_check_status_ref_id'
        ))->restore();
    }

    


    public function getHumanReadableName(): string
    {
        if ($this->human_readable_name) {
            return $this->human_readable_name;
        }

        $resultName = Inflector::camel2words(Inflector::camelize($this->reference_name));
        return mb_strtoupper(mb_substr($resultName, 0, 1)) . mb_strtolower(mb_substr($resultName, 1));
    }

    




    public function setHumanReadableName(string $userName): void
    {
        $this->human_readable_name = $userName;
    }

    


    public static function getIconColorList(): array
    {
        return [
            'text-primary' => Yii::t('backend', 'Синий'),
            'text-secondary' => Yii::t('backend', 'Серый'),
            'text-success' => Yii::t('backend', 'Зелёный'),
            'text-danger' => Yii::t('backend', 'Красный'),
            'text-warning' => Yii::t('backend', 'Жёлтый'),
            'text-info' => Yii::t('backend', 'Голубой'),
            'text-dark' => Yii::t('backend', 'Чёрный'),
        ];
    }

    


    public static function getIconColorListOptions(): array
    {
        $result = [];
        foreach (array_keys(StoredDocumentCheckStatusReferenceType::getIconColorList()) as $class) {
            $result[$class] = ['class' => $class];
        }

        return $result;
    }

    




    public function getIcon(string $text = ''): string
    {
        $icon = $this->renderIcon();
        if ($text && $icon) {
            $text = Html::tag(
                'dev',
                $text,
                ['class' => 'pr-1']
            );
        }
        return Html::tag(
            'dev',
            $text . $icon,
            [
                'class' => 'd-flex flex-row',
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'title' => $this->humanReadableName,
            ]
        );
    }

    


    private function renderIcon(): string
    {
        if (!$this->icon_class) {
            return '';
        }

        $class = "align-self-center {$this->icon_class}";
        if ($this->icon_color) {
            $class .= " {$this->icon_color}";
        }

        return Html::tag('i', null, [
            'class' => $class,
            'aria-hidden' => 'true',
        ]);
    }
}
