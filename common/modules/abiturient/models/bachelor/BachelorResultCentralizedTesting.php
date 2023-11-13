<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\CodeSettingsManager\exceptions\CodeNotFilledException;
use common\models\dictionary\Country;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\EmptyCheck;
use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\traits\ArchiveTrait;
use common\models\traits\DocumentCheckStatusTrait;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\bootstrap4\Html;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;































class BachelorResultCentralizedTesting extends ChangeHistoryDecoratedModel implements
    ApplicationConnectedInterface,
    IHaveIdentityProp,
    ArchiveModelInterface,
    ICanGivePropsToCompare,
    ChangeLoggedModelInterface
{
    use ArchiveTrait;
    use HtmlPropsEncoder;
    use DocumentCheckStatusTrait;
    use HasDirtyAttributesTrait;

    
    const CT_BELARUS = 'CTBelarus';

    const SCENARIO_CREATED_BY_USER = 'created-by-user';

    
    public $isNew = false;

    


    public static function tableName()
    {
        return '{{%bachelor_result_centralized_testing}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'mark',
                    'created_at',
                    'updated_at',
                    'archived_at',
                    'egeresult_id',
                    'passed_subject_ref_id',
                ],
                'integer'
            ],
            [
                'mark',
                'number',
                'min' => 0,
                'max' => 100,
            ],
            [
                [
                    'archive',
                    'read_only',
                ],
                'boolean'
            ],
            [
                [
                    'archive',
                    'read_only',
                ],
                'default',
                'value' => false
            ],
            [
                [
                    'series',
                    'number',
                ],
                'string',
                'max' => 255
            ],
            [
                ['year'],
                'string',
                'max' => 4
            ],
            [
                ['egeresult_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => EgeResult::class,
                'targetAttribute' => ['egeresult_id' => 'id']
            ],
            [
                ['passed_subject_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDisciplineReferenceType::class,
                'targetAttribute' => ['passed_subject_ref_id' => 'id']
            ],
            [
                [
                    'mark',
                    'year',
                    'number',
                    'series',
                    'egeresult_id',
                    'passed_subject_ref_id',
                ],
                'required',
                'on' => BachelorResultCentralizedTesting::SCENARIO_CREATED_BY_USER,
                'whenClient' => "function (attribute, value) {
                    var split = attribute.input.split('-');
                    var form_name = split.hasOwnProperty(0) ? split[0] : '';
                    var exam_index = split.hasOwnProperty(1) ? split[1] : '';
                    if (!form_name || !exam_index) {
                      return true;
                    }
                    
                    var at_least_one_is_filled = false;
                    $('#collapse-centralized-testing_' + exam_index).find('[data-at_least_one_is_required=1]').each(function () {
                      if ($(this).val()) {
                        at_least_one_is_filled = true;
                        return true;
                      }
                    });

                    return at_least_one_is_filled;
                }"
            ],
            [
                ['document_check_status_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDocumentCheckStatusReferenceType::class,
                'targetAttribute' => ['document_check_status_ref_id' => 'id'],
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[BachelorResultCentralizedTesting::SCENARIO_CREATED_BY_USER] = [
            'mark',
            'year',
            'number',
            'series',
            'egeresult_id',
            'passed_subject_ref_id',
        ];
        return $scenarios;
    }

    


    public function attributeLabels()
    {
        return [
            'mark' => Yii::t('abiturient/bachelor/centralized_testing/bachelor_result_centralized_testing', 'Подпись для поля "mark"; формы результатов ЦТ: `Балл`'),
            'year' => Yii::t('abiturient/bachelor/centralized_testing/bachelor_result_centralized_testing', 'Подпись для поля "year"; формы результатов ЦТ: `Год`'),
            'number' => Yii::t('abiturient/bachelor/centralized_testing/bachelor_result_centralized_testing', 'Подпись для поля "number"; формы результатов ЦТ: `Номер`'),
            'series' => Yii::t('abiturient/bachelor/centralized_testing/bachelor_result_centralized_testing', 'Подпись для поля "series"; формы результатов ЦТ: `Серия`'),
            'passedSubjectRef' => Yii::t('abiturient/bachelor/centralized_testing/bachelor_result_centralized_testing', 'Подпись для поля "passedSubjectRef"; формы результатов ЦТ: `Сданная дисциплина`'),
            'documentCheckStatus' => Yii::t('abiturient/bachelor/centralized_testing/bachelor_result_centralized_testing', 'Подпись для поля "documentCheckStatus" формы "Док. об обр.": `Статус проверки документа`'),
            'passed_subject_ref_id' => Yii::t('abiturient/bachelor/centralized_testing/bachelor_result_centralized_testing', 'Подпись для поля "passed_subject_ref_id"; формы результатов ЦТ: `Сданная дисциплина`'),
        ];
    }

    




    public function getApplication()
    {
        return $this->hasMany(BachelorApplication::class, ['id' => 'application_id'])
            ->via('rawEgeResult');
    }

    




    public function getRawEgeResult()
    {
        return $this->hasOne(EgeResult::class, ['id' => 'egeresult_id']);
    }

    




    public function getEgeResult()
    {
        return $this->getRawEgeResult()->active();
    }

    




    public function getPassedSubjectRef()
    {
        return $this->hasOne(StoredDisciplineReferenceType::class, ['id' => 'passed_subject_ref_id']);
    }

    public function getPropsToCompare(): array
    {
        return [
            'mark',
            'year',
            'number',
            'reason',
            'series',
            'passed_subject_ref_id',
        ];
    }

    




    public function getIdentityList(bool $forHuman = false): array
    {
        $refValue = 'reference_uid';
        if ($forHuman) {
            $refValue = 'reference_name';
        }
        $mark = $this->mark;
        $year = $this->year;
        $number = $this->number;
        $series = $this->series;
        $passedSubjectRefUid = ArrayHelper::getValue($this, "passedSubjectRef.{$refValue}", null);

        return array_filter(
            [$passedSubjectRefUid, $series, $number, $year, $mark],
            function ($item) {
                return isset($item);
            }
        );
    }

    


    public function getIdentityString(): string
    {
        return implode('_', $this->getIdentityList());
    }

    


    public function getLabelForCollapse(): string
    {
        $attributes = '';
        $attributesList = $this->getIdentityList(true);
        if (!EmptyCheck::isEmpty($attributesList)) {
            $value = ArrayHelper::getValue($attributesList, '0');
            if ($value) {
                $attributes .= "({$value}, ";
            } else {
                $attributes .= '(';
            }

            $label = mb_strtolower($this->getAttributeLabel('series'));
            $value = ArrayHelper::getValue($attributesList, '1');
            $attributes .= "{$label}: {$value}, ";

            $label = mb_strtolower($this->getAttributeLabel('number'));
            $value = ArrayHelper::getValue($attributesList, '2');
            $attributes .= "{$label}: {$value}, ";

            $label = mb_strtolower($this->getAttributeLabel('year'));
            $value = ArrayHelper::getValue($attributesList, '3');
            $attributes .= "{$label}: {$value}";

            $value = ArrayHelper::getValue($attributesList, '4');
            if ($value) {
                $label = mb_strtolower($this->getAttributeLabel('mark'));
                $attributes .= ", {$label}: {$value})";
            } else {
                $attributes .= ')';
            }

            $attributes = Html::tag('span', $attributes, ['class' => 'additional_information_for_collapse_label']);
        }

        return trim(Yii::t(
            'abiturient/bachelor/centralized_testing/centralized_testing-result',
            'Заголовок блока формы результатов ЦТ; на стр. ВИ: `Централизованное тестирование/экзамен Республики Беларусь {attributes}`',
            ['attributes' => $attributes]
        ));
    }

    


    public function getPassedSubjectList(): array
    {
        $tnStoredDisciplineReferenceType = StoredDisciplineReferenceType::tableName();
        $passedSubjects = StoredDisciplineReferenceType::find()
            ->where(["{$tnStoredDisciplineReferenceType}.archive" => false])
            ->orderBy("{$tnStoredDisciplineReferenceType}.reference_name")
            ->all();
        if (!$passedSubjects) {
            return [];
        }

        return ArrayHelper::map($passedSubjects, 'id', 'reference_name');
    }

    


    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);
        $this->isNew = false;
    }

    


    public function getYearFor1c(): string
    {
        return "{$this->year}-01-01";
    }

    




    public static function hasCorrectCitizenship(BachelorApplication $application): bool
    {
        $tnCountry = Country::tableName();
        try {
            $uidBelarus = Yii::$app->configurationManager->getCode('belarusian_citizenship_guid');
        } catch (CodeNotFilledException $e) {
            return false;
        } catch (Throwable $e) {
            Yii::error("Ошибка получения кода гражданства Республики Беларусь, по причине: `{$e->getMessage()}`", 'hasCorrectCitizenship');

            throw $e;
        }

        return $application->getAbiturientQuestionary()
            ->joinWith('personalData.citizenship')
            ->andWhere(["{$tnCountry}.ref_key" => $uidBelarus])
            ->exists();
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'mark',
            'year',
            'number',
            'series',
            'passed_subject_ref_id' => function ($model) {
                return ArrayHelper::getValue($model, 'passedSubjectRef.reference_name');
            },
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_CENTRALIZED_TESTING;
    }

    public function getEntityIdentifier(): ?string
    {
        return ArrayHelper::getValue($this, 'rawEgeResult.disciplineReferenceName');
    }

    




    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$this->fillDocumentCheckStatusIfNotVerified()) {
            return false;
        }

        return true;
    }

    





    public function load($data, $formName = null)
    {
        if (!parent::load($data, $formName)) {
            return false;
        }

        return true;
    }
}
