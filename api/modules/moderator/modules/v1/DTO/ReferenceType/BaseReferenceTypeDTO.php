<?php


namespace api\modules\moderator\modules\v1\DTO\ReferenceType;


use api\modules\moderator\modules\v1\DTO\exceptions\DTOFieldNotFoundException;
use api\modules\moderator\modules\v1\DTO\ReferenceType\interfaces\IReferenceTypeDTO;
use common\components\ArrayToXmlConverter\ArrayToXmlConverter;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use SimpleXMLElement;
use yii\web\NotFoundHttpException;

class BaseReferenceTypeDTO implements IReferenceTypeDTO
{

    


    public $referenceName;

    


    public $referenceId;

    


    public $referenceUID;

    


    public $referenceClassName;

    


    protected $serializedData;

    


    private $rawData;

    



    public function __construct(array $data = null)
    {
        if (!is_null($data)) {
            $this->rawData = $data;
            $this->serialize();
        }
    }

    


    public function setArrayRawData(array $rawData): void
    {
        $this->rawData = $rawData;
        $this->serialize();
    }

    


    public function setSerializedData(SimpleXMLElement $serializedData): void
    {
        $this->serializedData = $serializedData;
        $this->setArrayRawData((array)$serializedData);
    }

    


    public function getReferenceTypeId(): ?int
    {
        $ref = $this->getStoredReferenceType();
        if (is_null($ref)) {
            return null;
        }
        return $ref->id;
    }

    






    public function getStoredReferenceType(): ?StoredReferenceType
    {
        if (ReferenceTypeManager::isReferenceTypeEmpty($this->serializedData)) {
            return null;
        }
        $ref = ReferenceTypeManager::GetOrCreateReference($this->getStoredReferenceTypeClass(), $this->serializedData);
        if ($ref === null) {
            throw new NotFoundHttpException('Не найден ReferenceType. ' . self::getStoredReferenceTypeClass());
        }
        return $ref;
    }

    



    public function serialize()
    {
        $this->checkFieldProvided('ReferenceId');
        $this->referenceId = $this->rawData['ReferenceId'];
        $this->checkFieldProvided('ReferenceClassName');
        $this->referenceClassName = $this->rawData['ReferenceClassName'];
        $this->checkFieldProvided('ReferenceUID');
        $this->referenceUID = $this->rawData['ReferenceUID'];
        $this->checkFieldProvided('ReferenceName');
        $this->referenceName = $this->rawData['ReferenceName'];
    }

    public function getStoredReferenceTypeClass(): string
    {
        return '';
    }

    


    public function setStringRawData(string $rawData)
    {
        $this->setSerializedData(new SimpleXMLElement(ArrayToXmlConverter::removeNameSpaces($rawData)));
    }

    public function checkFieldProvided($field): bool
    {
        if (!isset($this->rawData[$field])) {
            throw new DTOFieldNotFoundException($field);
        }
        return true;
    }
}