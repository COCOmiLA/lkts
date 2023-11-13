<?php

namespace common\modules\abiturient\models\drafts;

use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;

class ApplicationAndQuestionaryLinker
{
    public static function setUpQuestionaryLink(BachelorApplication $app): BachelorApplication
    {
        $draft_status = $app->draft_status;
        
        $q = $app->getLinkedAbiturientQuestionary()->one();
        if ($app->isDraftInSendMode()) {
            if (!$q) {
                
                $abiturientQuestionary = $app->user->getAbiturientQuestionary()->one();
                if ($abiturientQuestionary) {
                    $new_questionary = DraftsManager::makeCopy($abiturientQuestionary);
                    
                    $new_questionary->draft_status = $draft_status;
                    $new_questionary->status = AbiturientQuestionary::STATUS_SENT;
                    $new_questionary
                        ->loadDefaultValues()
                        ->save(false);

                    $app->link('linkedAbiturientQuestionary', $new_questionary);
                }
            } else {
                $q->draft_status = $draft_status;
                $q->status = AbiturientQuestionary::STATUS_SENT;
                $q
                    ->loadDefaultValues()
                    ->save(false);
            }
        } elseif (!$app->isArchivedApprovedDraft()) {
            foreach ($app->getLinkedAbiturientQuestionary()->all() as $item) {
                $item->delete();
            }
            $app->unlinkAll('linkedAbiturientQuestionary', true);
        }
        unset($app->linkedAbiturientQuestionary);
        unset($app->abiturientQuestionary);

        return $app;
    }

    public static function copyQuestionaryToActual(AbiturientQuestionary $questionary)
    {
        
        $actualAbiturientQuestionary = $questionary->user->getActualAbiturientQuestionary()->one();
        if (!$actualAbiturientQuestionary || $questionary->id != $actualAbiturientQuestionary->id) {
            if ($actualAbiturientQuestionary) {
                $actualAbiturientQuestionary->archive();
            }
            
            $questionary = DraftsManager::makeCopy($questionary);
            $questionary->status = AbiturientQuestionary::STATUS_APPROVED;
            $questionary->draft_status = IDraftable::DRAFT_STATUS_APPROVED;
            $questionary
                ->loadDefaultValues()
                ->save(false);
        }
    }

    public static function copyQuestionaryToDraft(AbiturientQuestionary $q)
    {
        
        $abiturientQuestionary = $q->user->getAbiturientQuestionary()->one();
        if (!$abiturientQuestionary || $q->id != $abiturientQuestionary->id) {
            if ($abiturientQuestionary) {
                $abiturientQuestionary->archive();
            }
            
            $q = DraftsManager::makeCopy($q);
            $q->status = AbiturientQuestionary::STATUS_SENT;
            $q->draft_status = IDraftable::DRAFT_STATUS_CREATED;
            $q
                ->loadDefaultValues()
                ->save(false);
        }
    }

    






    public static function linkCurrentActualQuestionary(BachelorApplication $app)
    {
        $q = $app->getLinkedAbiturientQuestionary()->one();
        if (!$q) {
            $actualAbiturientQuestionary = $app->user->getActualAbiturientQuestionary()->one();
            if ($actualAbiturientQuestionary) {
                $new_questionary = DraftsManager::makeCopy($actualAbiturientQuestionary);
                $new_questionary->draft_status = IDraftable::DRAFT_STATUS_APPROVED;
                $new_questionary->status = AbiturientQuestionary::STATUS_APPROVED;
                $new_questionary->save(false);

                $app->link('linkedAbiturientQuestionary', $new_questionary);
            }
        } else {
            $q->status = AbiturientQuestionary::STATUS_APPROVED;
            $q->draft_status = IDraftable::DRAFT_STATUS_APPROVED;

            $q->save(false);
        }
    }
}