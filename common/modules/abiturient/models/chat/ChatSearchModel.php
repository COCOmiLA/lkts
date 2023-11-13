<?php

namespace common\modules\abiturient\models\chat;

use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\bachelor\ApplicationType;
use Yii;
use yii\base\Model;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;









class ChatSearchModel extends Model
{
    use HtmlPropsEncoder;

    public $email = '';
    public $full_name = '';
    public $applications = '';

    


    public function rules()
    {
        return [
            [
                [
                    'email',
                    'full_name',
                    'applications',
                ],
                'string'
            ],
            [
                [
                    'email',
                    'full_name',
                    'applications',
                ],
                'trim'
            ],
        ];
    }

    


    public function attributeLabels(): array
    {
        return [
            'email' => Yii::t('abiturient/chat/search', 'Подпись для поля "email"; формы поиска пользователя в чате: `Почта`'),
            'full_name' => Yii::t('abiturient/chat/search', 'Подпись для поля "full_name"; формы поиска пользователя в чате: `ФИО`'),
            'applications' => Yii::t('abiturient/chat/search', 'Подпись для поля "applications"; формы поиска пользователя в чате: `Приёмные кампании`'),
        ];
    }

    public function renderFieldForm(string $attrName): string
    {
        if (!$this->checkIfAttrExists($attrName)) {
            throw new ServerErrorHttpException('Передан не существующий аттрибут поисковой формы');
        }

        $formFieldName = "{$this->formName()}[{$attrName}]";
        $idForFormField = $this->generateIdForFormField($attrName);
        $field = Html::tag(
            'label',
            "{$this->getAttributeLabel($attrName)}:",
            ['for' => $idForFormField]
        );
        switch ($attrName) {
            case 'applications':
                $field .= Html::tag(
                    'select',
                    $this->renderOptionsForSelect($attrName),
                    [
                        'name' => $formFieldName,
                        'id' => $idForFormField,
                    ]
                );
                break;

            default:
                $field .= Html::tag('input', null, [
                    'name' => $formFieldName,
                    'type' => 'text',
                    'id' => $idForFormField,
                ]);
                break;
        }

        return $field;
    }

    private function generateIdForFormField(string $attrName): string
    {
        if (!$this->checkIfAttrExists($attrName)) {
            throw new ServerErrorHttpException('Передан не существующий аттрибут поисковой формы');
        }

        return mb_strtolower($this->formName()) . "-{$attrName}";
    }

    private function checkIfAttrExists(string $attrName): bool
    {
        $attributes = array_keys($this->attributes);
        return in_array($attrName, $attributes);
    }

    private function getDataForOptions(string $attrName): array
    {
        if (!$this->checkIfAttrExists($attrName)) {
            throw new ServerErrorHttpException('Передан не существующий аттрибут поисковой формы');
        }

        $searchData = [];
        switch ($attrName) {
            case 'applications':
                $searchData = [
                    'class' => ApplicationType::class,
                    'searchQuery' => ['archive' => false],
                    'valueMap' => 'id',
                    'descriptionMap' => 'name',
                ];
                break;

            default:
                $searchData = [];
        }

        if (!$searchData) {
            return [];
        }

        $data = $searchData['class']::findAll($searchData['searchQuery']);
        if (!$data) {
            return [];
        }

        return ArrayHelper::map($data, $searchData['valueMap'], $searchData['descriptionMap']);
    }

    private function renderOptionsForSelect(string $attrName): string
    {
        if (!$this->checkIfAttrExists($attrName)) {
            throw new ServerErrorHttpException('Передан не существующий аттрибут поисковой формы');
        }

        $data = $this->getDataForOptions($attrName);
        if (!$data) {
            return '';
        }

        $options = Html::tag(
            'option',
            Yii::t('abiturient/chat/search', 'Подпись для пустого значения выпадающего списка; формы поиска пользователя в чате: `Выберите ...`'),
            ['disabled' => true, 'selected' => true, 'value' => true,]
        );
        $options .= Html::tag(
            'option',
            Yii::t('abiturient/chat/search', 'Подпись для значения выпадающего списка "без приёмной кампании"; формы поиска пользователя в чате: `Без приёмной кампании`'),
            ['value' => ApplicationType::ALIAS_FOR_EMPTY_APPLICATION]
        );
        foreach ($data as $value => $description) {
            $options .= Html::tag('option', $description, ['value' => $value]);
        }

        return $options;
    }
}
