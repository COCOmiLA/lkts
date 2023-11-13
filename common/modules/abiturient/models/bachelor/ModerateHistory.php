<?php

namespace common\modules\abiturient\models\bachelor;

use backend\models\RBACAuthAssignment;
use common\components\CommentNavigationLinkerWidget\CommentNavigationLinkerWidget;
use common\components\LikeQueryManager;
use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;












class ModerateHistory extends ActiveRecord implements IHaveIgnoredOnCopyingAttributes
{
    use HtmlPropsEncoder;

    public static function tableName()
    {
        return '{{%application_moderate_history}}';
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
                    'application_id',
                    'status',
                    'user_id',
                    'moderated_at'
                ],
                'integer'
            ],
            [
                [
                    'application_id',
                    'status'
                ],
                'required'
            ],
            [
                ['comment'],
                'string',
                'max' => 2000
            ],
            [
                ['status'],
                'in',
                'range' => [
                    BachelorApplication::STATUS_NOT_APPROVED,
                    BachelorApplication::STATUS_APPROVED
                ]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'application_id' => 'Заявление',
            'user_id' => 'Модератор',
            'status' => 'Статус',
            'comment' => 'Комментарий'
        ];
    }

    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    public function getRbacAuthAssignment()
    {
        return $this->hasOne(RBACAuthAssignment::class, ['user_id' => 'user_id']);
    }

    


    public function getUser()
    {
        return $this->getRawUser()->andOnCondition(['user.is_archive' => false]);
    }

    


    public function getRawUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getStatusName()
    {
        switch ($this->status) {
            case (BachelorApplication::STATUS_NOT_APPROVED):
                return 'Отклонено';
            case (BachelorApplication::STATUS_APPROVED):
                return 'Одобрено';
            default:
                return '';
        }
    }

    public function getUserName()
    {
        if ($this->user_id != 1 && $this->user != null) {
            return $this->user->getPublicIdentity();
        } elseif ($this->user_id == 1) {
            return 'Система';
        }
        return '-';
    }

    public static function getModerateQuery(
        $username = null,
        $campaign = null,
        $modelTimeStart = null,
        $modelTimeStop = null
    ) {
        $tnUser = User::tableName();
        $tnModerateHistory = ModerateHistory::tableName();
        $tnApplicationType = ApplicationType::tableName();
        $tnBachelorApplication = BachelorApplication::tableName();

        if (empty($modelTimeStart)) {
            $timeStart = 0;
        } else {
            $timeStart = strtotime($modelTimeStart);
        }
        if (empty($modelTimeStop)) {
            $timeStop = time();
        } else {
            $timeStop = strtotime($modelTimeStop);
        }
        $query = (new Query())
            ->from($tnModerateHistory)
            ->leftJoin(
                $tnUser,
                "{$tnUser}.id = {$tnModerateHistory}.user_id"
            )
            ->leftJoin(
                $tnBachelorApplication,
                "{$tnModerateHistory}.application_id = {$tnBachelorApplication}.id"
            )
            ->leftJoin(
                $tnApplicationType,
                "{$tnBachelorApplication}.type_id = {$tnApplicationType}.id"
            )
            ->andWhere([LikeQueryManager::getActionName(), "{$tnUser}.username", $username])
            ->andWhere(["{$tnBachelorApplication}.archive" => false])
            ->andWhere([
                'BETWEEN',
                "{$tnModerateHistory}.moderated_at",
                $timeStart,
                $timeStop
            ])
            ->orderBy("{$tnUser}.id");
        if (!empty($campaign)) {
            $query = $query->andWhere(['=', "{$tnApplicationType}.campaign_id", $campaign]);
        }

        return $query;
    }

    public function getIgnoredOnCopyingAttributes(): array
    {
        return [
            'id',
            'updated_at'
        ];
    }

    




    public function getFormattedComment(int $id): ?string
    {
        return CommentNavigationLinkerWidget::renderFormattedModeratorComment($this->comment, $id);
    }
}
