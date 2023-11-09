<?php

namespace common\modules\abiturient\models\interfaces;

interface ApplicationInterface
{
    const STATUS_CREATED = 0;

    
    const STATUS_SENT = 1;

    const STATUS_APPROVED = 2;
    const STATUS_NOT_APPROVED = 3;
    const STATUS_REJECTED_BY1C = 4;
    const STATUS_WANTS_TO_BE_REMOTE = 6;

    
    const STATUS_WANTS_TO_RETURN_ALL = 7;
    
    const STATUS_SENT_AFTER_APPROVED = 8;
    
    const STATUS_SENT_AFTER_NOT_APPROVED = 9;
    
    const STATUS_ENROLLMENT_REJECTION_REQUESTED = 10;
    
    const BLOCK_STATUS_DISABLED = 0;
    const BLOCK_STATUS_ENABLED = 1;

    public function getSpecialities();
    
    public function getUser();
    
    public function translateStatus();
    
    




    public function moderationAllowedByStatus();
}
