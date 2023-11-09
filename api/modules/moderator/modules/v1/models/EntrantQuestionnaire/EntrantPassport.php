<?php

namespace api\modules\moderator\modules\v1\models\EntrantQuestionnaire;


use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\modules\abiturient\models\PassportData;

class EntrantPassport extends PassportData
{
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return [
            'DocumentTypeRef' => ReferenceTypeManager::GetReference($this, 'documentType'),
            'DocSeries' => $this->series,
            'DocNumber' => $this->number,
            'DocOrganization' => [
                'ContractorRef' => ReferenceTypeManager::GetReference($this, 'contractor.contractorRef'),
                'SubdivisionCode' => ($this->contractor->subdivision_code ?? ''),
                'ContractorTypeRef' => ReferenceTypeManager::GetReference($this, 'contractor.contractorTypeRef')
            ],
            'IssueDate' => (string)$this->formatted_issued_date,
            'DocumentCheckStatusRef' => $this->buildDocumentCheckStatusRefType(),
            'ReadOnly' => $this->read_only ? 1 : 0,
            'SubdivisionCode' => $this->department_code
        ];
    }
}
