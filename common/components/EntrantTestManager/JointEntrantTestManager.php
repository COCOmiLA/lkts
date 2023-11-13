<?php

namespace common\components\EntrantTestManager;

class JointEntrantTestManager extends BaseEntrantTestManager
{
    private const JOINT_TEMPLATE = '{DISCIPLINE_UID} {DISCIPLINE_FORM_UID} {CHILD_DISCIPLINE_UID}';

    






    public static function buildJointEntrantTestString(string $disciplineUid, string $disciplineFormUid, ?string $childDisciplineUid): string
    {
        return md5(strtr(
            JointEntrantTestManager::JOINT_TEMPLATE,
            [
                '{DISCIPLINE_UID}' => $disciplineUid,
                '{DISCIPLINE_FORM_UID}' => $disciplineFormUid,
                '{CHILD_DISCIPLINE_UID}' => $childDisciplineUid,
            ]
        ));
    }
}
