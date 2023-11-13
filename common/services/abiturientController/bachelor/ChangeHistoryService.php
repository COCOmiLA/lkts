<?php

namespace common\services\abiturientController\bachelor;

use common\models\settings\ChangeHistorySettings;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\repository\ChangeHistoryRepository;
use common\services\abiturientController\bachelor\BachelorService;

class ChangeHistoryService extends BachelorService
{
    










    public function getChangeHistoryByApplicationWithFilters(
        User                $currentUser,
        BachelorApplication $application,
        int                 $sortDirection = SORT_ASC,
        ?int                $dateStart = null,
        ?int                $dateEnd = null,
        ?int                $limit = null,
        ?int                $offset = null
    ): array {
        $changeHistoriesQuery = ChangeHistoryRepository::getApplicationAndQuestionaryChangeHistoryByApplicationQuery(
            $application,
            $sortDirection,
            $dateStart,
            $dateEnd
        );

        $limit = $limit ?? ChangeHistorySettings::getValueByName('first_load_limit');
        $changeHistoriesQuery->limit($limit);

        if ($offset) {
            $changeHistoriesQuery->offset($offset);
        }

        if ($currentUser->isAbiturient()) {
            
            $changeHistoriesQuery->andWhere([
                'not',
                ['change_history.change_type' => ChangeHistory::CHANGE_HISTORY_ABITURIENT_JUXTAPOSITION]
            ]);
        }

        return $changeHistoriesQuery->all();
    }

    





    public function getChangeHistoryByApplicationWithFiltersFromPost(
        User                $currentUser,
        BachelorApplication $application
    ): array {
        $limit = $this->request->post('infiniteScrollLimit');
        $offset = $this->request->post('infiniteScrollOffset');
        $dateEnd = empty($this->request->post('dateEnd')) ? null : $this->request->post('dateEnd');
        $dateStart = empty($this->request->post('dateStart')) ? null : $this->request->post('dateStart');
        $sortDirection = (int) $this->request->post('sortDirection');

        return $this->getChangeHistoryByApplicationWithFilters(
            $currentUser,
            $application,
            $sortDirection,
            $dateStart,
            $dateEnd,
            $limit,
            $offset
        );
    }
}
