<?php

namespace common\modules\abiturient\models;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;















class CommentsComing extends ChangeHistoryDecoratedModel implements ApplicationConnectedInterface
{
    use HasDirtyAttributesTrait;
    use HtmlPropsEncoder;

    


    public static function tableName()
    {
        return '{{%comments_coming}}';
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
                    'created_at',
                    'updated_at',
                    'author_id',
                    'bachelor_application_id'
                ],
                'integer'
            ],
            [
                [
                    'comment',
                    'answer'
                ],
                'string',
                'max' => 1000
            ],
            [
                ['bachelor_application_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BachelorApplication::class,
                'targetAttribute' => ['bachelor_application_id' => 'id']
            ],
            [
                ['author_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['author_id' => 'id']
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'answer'                  => Yii::t('abiturient/bachelor/comment/comments-coming', 'Подпись для поля "answer"; формы комментария: `Ответ на комментарий`'),
            'comment'                 => Yii::t('abiturient/bachelor/comment/comments-coming', 'Подпись для поля "comment"; формы комментария: `Комментарий`'),
            'author_id'               => Yii::t('abiturient/bachelor/comment/comments-coming', 'Подпись для поля "author_id"; формы комментария: `id автора комментария`'),
            'bachelor_application_id' => Yii::t('abiturient/bachelor/comment/comments-coming', 'Подпись для поля "bachelor_application_id"; формы комментария: `id заявления`'),
        ];
    }

    


    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'bachelor_application_id']);
    }

    


    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    public function getChangeLoggedAttributes()
    {
        return ['comment'];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_ABITURIENT_COMMENT;
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }
}
