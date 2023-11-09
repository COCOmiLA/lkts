<?php

namespace common\modules\abiturient\controllers;

use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ContractorPackageBuilder;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\ToAssocCaster;
use common\models\User;
use common\services\abiturientController\bachelor\ContractorService;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Module;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;

class ContractorController extends Controller
{
    
    protected ContractorService $contractorService;

    





    public function __construct(
        $id, 
        $module, 
        ContractorService $contractorService, 
        $config = []
    ) {
        $this->contractorService = $contractorService;

        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => [
                            'search', 'location'
                        ],
                        'allow' => true,
                        'roles' => ['?', '@']
                    ],
                    [
                        'actions' => ['approve'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER]
                    ]
                ]
            ]
        ];
    }

    public function actionSearch()
    {
        return $this->asJson($this->contractorService->searchContractor());
    }

    public function actionApprove()
    {
        try {
            if (!\Yii::$app->request->isPost) {
                throw new MethodNotAllowedHttpException();
            }
            
            $request = Yii::$app->request;
            
            if ($request->post('approve_contractor_location_not_found')) {
                $location_code = null;
                $location_name = $request->post('approve_contractor_location_name');
            } else {
                $location_code = $request->post('approve_contractor_location_code');
                $location_name = null;
            }

            $type_ref_uid = $request->post('contractor_type_ref_uid');
            if (empty($type_ref_uid)) {
                throw new InvalidArgumentException("Необходимо выбрать тип контрагента при подтверждении");
            }

            $request_data = [
                'Name' => $request->post('contractor_name'),
                'SubdivisionCode' => $request->post('subdivsion_code'),
                'Type' => ReferenceTypeManager::GetReference(StoredContractorTypeReferenceType::findByUID($type_ref_uid)),
            ];
            
            if ($location_code || $location_name) {
                $request_data['Address'] = ContractorPackageBuilder::buildAddress($location_code, $location_name);
            }

            $response = \Yii::$app->soapClientAbit->load('GetOrCreateContractor', $request_data);
            
            $contractor_raw = ToAssocCaster::getAssoc($response->return);
            $contractor = ContractorManager::GetOrCreateContractor($contractor_raw, $request->post('approve_contractor_id'));
            
            return $this->asJson(['id' => $contractor->id, 'text' => $contractor->name, 'status' => true, 'messages' => []]);
        } catch (\Throwable $e) {
            Yii::error('Ошибка при подтверждении контрагента: ' . $e->getMessage(), 'CONTRACTOR_APPROVE');
            return $this->asJson(['status' => false, 'messages' => [$e->getMessage()]]);
        }
    }

    public function actionLocation()
    {
        return $this->asJson($this->contractorService->searchLocation());
    }
}
