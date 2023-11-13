<?php


namespace common\modules\abiturient\models\repositories;


use common\models\attachment\attachmentCollection\ApplicationAttachmentCollection;
use common\models\attachment\attachmentCollection\QuestionaryAttachmentCollection;
use common\models\AttachmentType;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\db\ActiveQuery;

class FileRepository
{

    




    public static function GetAttachmentCollectionsFromTypes(BachelorApplication $application, $relatedEntity): array
    {
        $resultList = [];
        $types = AttachmentType::GetUnionAttachmentTypes($application->type->rawCampaign->referenceType->reference_uid, $relatedEntity)
            ->with(['attachments' => function (ActiveQuery $q) use ($application) {
                return $q->andWhere([
                    'attachment.application_id' => $application->id,
                    'deleted' => false
                ]);
            }])
            ->all();
        foreach ($types as $type_to_add) {
            $resultList[] = new ApplicationAttachmentCollection($type_to_add, $application, $type_to_add->attachments ?? []);
        }

        self::SortCollection($resultList);

        return $resultList;
    }

    




    public static function GetQuestionaryCollectionsFromTypes(AbiturientQuestionary $questionary, $relatedEntity): array
    {
        $resultList = [];
        $types = AttachmentType::GetCommonAttachmentTypesQuery($relatedEntity);
        if (!$questionary->isNewRecord) {
            $types
                ->with(['attachments' => function (ActiveQuery $q) use ($questionary) {
                    return $q
                        ->andWhere([
                            'or',
                            ["attachment.questionary_id" => $questionary->id],
                            [
                                'and',
                                ['attachment.owner_id' => $questionary->user_id],
                                ['attachment.questionary_id' => null],
                            ]
                        ])
                        ->andWhere([
                            'attachment.deleted' => false
                        ]);
                }]);
        }

        foreach ($types->all() as $type_to_add) {
            $resultList[] = new QuestionaryAttachmentCollection($type_to_add, $questionary, $type_to_add->attachments ?? []);
        }

        self::SortCollection($resultList);

        return $resultList;
    }

    public static function SortCollection(&$resultList)
    {
        $sort = \Yii::$app->configurationManager->getCode('scan_sort_code');

        usort($resultList, function ($a, $b) use ($sort) {
            $first = null;
            $second = null;
            if ($sort !== null && $a->attachmentType && $b->attachmentType) {
                switch ($sort) {
                    case '1':
                        $first = $a->attachmentType->name;
                        $second = $b->attachmentType->name;
                        break;
                    case '2':
                        $first = $a->attachmentType->id;
                        $second = $b->attachmentType->id;
                        break;
                    case '3':
                        if ((int)$a->attachmentType->from1c === (int)$b->attachmentType->from1c) {
                            $first = $a->attachmentType->custom_order;
                            $second = $b->attachmentType->custom_order;
                        } else {
                            $first = (int)$a->attachmentType->from1c;
                            $second = (int)$b->attachmentType->from1c;
                        }
                        break;
                    case '0':
                        break;
                }
            }
            return ($first < $second) ? -1 : 1;
        });
    }

    public static function getMimeType($extension)
    {
        switch ($extension) {
            case 'pdf':
                return 'application/pdf';
                break;
            default:
                return 'image/jpeg';
                break;
        }
    }
}