<?php

namespace common\widgets\ContractorField;

use common\components\configurationManager;
use common\models\dictionary\Contractor;
use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use InvalidArgumentException;
use Yii;
use yii\base\Model;
use yii\base\Widget;
use yii\bootstrap\ActiveForm;
use yii\db\ActiveRecord;
use yii\helpers\Url;

class ContractorField extends Widget
{
    
    public $form;
    
    
    public $model;
    
    
    public $attribute;
    
    
    public $keynum;

    
    public $is_readonly = false;
    
    
    public $disabled = false;
    
    
    public $need_subdivision_code = false;
    
    
    public $labels = [];
    
    
    public $notFoundAttribute = "contractorNotFound";

    
    public $options = [];

    


    public $contractor_type_ref_uid;
    
    


    public $default_contractor_type_guid_code;

    
    public $need_approve = false;

    public $mask_subdivision_code = '*{0,100}';

    
    public $application;

    



    public $moderator_allowed_to_edit;
    
    



    protected $default_contractor_type_ref_uid;

    
    protected configurationManager $configurationManager;

    public function __construct(configurationManager $configurationManager, $config = [])
    {
        $this->configurationManager = $configurationManager;
        parent::__construct($config);
    }

    public function init()
    {
        if (!$this->model instanceof ActiveRecord) {
            throw new InvalidArgumentException("Недопустимый класс модели.");
        }

        $this->labels['contractor_name'] = $this->labels['contractor_name'] 
            ?? \Yii::t('common/models/dictionary/contractor', 'Подпись для поля "name" формы "Контрагент": `Наименование`');
        $this->labels['subdivision_code'] = $this->labels['subdivision_code'] 
            ?? \Yii::t('common/models/dictionary/contractor', 'Подпись для поля "subdivision_code" формы "Контрагент": `Код подразделения`');

        if (empty($this->options['contractorFormName'])) {
            $this->options['contractorFormName'] = (new Contractor())->formName();
        }

        if ($this->default_contractor_type_guid_code) {
            $this->default_contractor_type_ref_uid = $this->configurationManager->getCode($this->default_contractor_type_guid_code);
        }

        $this->initModeratorAllowedToEdit();
    }

    public function run()
    {
        $this->view->registerJsVar('approveContractorUrl', Url::to('/contractor/approve'));
        $this->view->registerJsVar('approveContractorTextForAjaxTooltip', Yii::$app->configurationManager->getText('global_text_for_ajax_tooltip'));

        if (\Yii::$app->user->can(User::ROLE_MANAGER)) {
            $contractor = Contractor::findOne($this->model->{$this->attribute});
            if ($contractor && $contractor->status === Contractor::STATUS_PENDING) {
                $this->need_approve = true;
            }
        }

        if ($this->contractor_type_ref_uid) {
            $contractor_type_ref = StoredContractorTypeReferenceType::findByUID($this->contractor_type_ref_uid);
        } elseif ($this->default_contractor_type_ref_uid) {
            $contractor_type_ref = StoredContractorTypeReferenceType::findByUID($this->default_contractor_type_ref_uid);
        }

        $new_contractor = new Contractor();
        $new_contractor->need_subdivision_code = $this->need_subdivision_code;
        $new_contractor->contractor_type_ref_id = $contractor_type_ref->id ?? null;

        return $this->render('index', [
            'form' => $this->form,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'notFoundAttribute' => $this->notFoundAttribute,
            'keynum' => $this->keynum,
            'is_readonly' => $this->is_readonly,
            'disabled' => $this->disabled,
            'need_subdivision_code' => $this->need_subdivision_code,
            'labels' => $this->labels,
            'contractor_type_ref_uid' => $this->contractor_type_ref_uid,
            'need_approve' => $this->need_approve,
            'new_contractor' => $new_contractor,
            'mask_subdivision_code' => $this->mask_subdivision_code,
            'config' => $this->options,
            'application' => $this->application,
            'moderator_allowed_to_edit' => $this->moderator_allowed_to_edit,
        ]);
    }
    
    public static function getIdentifier(Model $model, string $attribute, $keynum = null): string
    {
        $result = "";
        
        $reflection_class = new \ReflectionClass(get_class($model));
        $result = mb_strtolower($reflection_class->getShortName()) . '-' . $attribute;
        if (isset($keynum)) {
            $result .= '-' . $keynum;
        }
        
        return $result;
    }
    
    public static function getBlockIdFound(Model $model, string $attribute, $keynum = null)
    {
        $identifier = static::getIdentifier($model, $attribute, $keynum);
        return "contractor-field-found-{$identifier}";
    }
    
    public static function getBlockIdNotFound(Model $model, string $attribute, $keynum = null)
    {
        $identifier = static::getIdentifier($model, $attribute, $keynum);
        return "contractor-field-not-found-{$identifier}";
    }

    public static function getContractorTypeInputId(Model $model, string $attribute, $keynum = null)
    {
        $identifier = static::getIdentifier($model, $attribute, $keynum);
        return "contractor-type-{$identifier}";
    }

    public function initModeratorAllowedToEdit(): void
    {
        if (isset($this->moderator_allowed_to_edit)) {
            return;
        }

        $this->moderator_allowed_to_edit = true;

        if (isset($this->application->type)) {
            $this->moderator_allowed_to_edit = $this->application->type->moderator_allowed_to_edit;
        } else {
            foreach ((new User())->availableApplicationTypes as $type) {
                if (!$type->moderator_allowed_to_edit) {
                    $this->moderator_allowed_to_edit = false;
                    break;
                }
            }
        }
    }
}
