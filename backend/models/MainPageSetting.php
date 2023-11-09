<?php

namespace backend\models;

use common\models\User;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
















class MainPageSetting extends ActiveRecord
{
    
    public string $sortableElements = '';

    private const PARSER_FRONTEND_NAME = '/(MainPageInstruction\w+)(\[(\w+)\])/i';

    


    public static function tableName()
    {
        return '{{%main_page_setting}}';
    }

    


    public function rules()
    {
        return [
            [
                [
                    'number',
                    'created_at',
                    'updated_at',
                ],
                'default',
                'value' => null
            ],
            [
                [
                    'number',
                    'created_at',
                    'updated_at',
                ],
                'integer'
            ],
            [
                'sortableElements',
                'string'
            ],
        ];
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getMainPageInstructionVideo()
    {
        return $this->hasOne(MainPageInstructionVideo::class, ['main_page_setting_id' => 'id']);
    }

    


    public function getOrBuildVideo(): MainPageInstructionVideo
    {
        $video = $this->mainPageInstructionVideo;
        if (!$video) {
            $video = new MainPageInstructionVideo();
            if ($this->id) {
                $video->main_page_setting_id = $this->id;
            }
        }

        return $video;
    }

    




    public function getMainPageInstructionImage()
    {
        return $this->hasOne(MainPageInstructionImage::class, ['main_page_setting_id' => 'id']);
    }

    


    public function getOrBuildImage(): MainPageInstructionImage
    {
        $image = $this->mainPageInstructionImage;
        if (!$image) {
            $image = new MainPageInstructionImage();
            if ($this->id) {
                $image->main_page_setting_id = $this->id;
            }
        }

        return $image;
    }

    




    public function getMainPageInstructionHeader()
    {
        return $this->hasOne(MainPageInstructionHeader::class, ['main_page_setting_id' => 'id']);
    }

    


    public function getOrBuildHeader(): MainPageInstructionHeader
    {
        $header = $this->mainPageInstructionHeader;
        if (!$header) {
            $header = new MainPageInstructionHeader();
            if ($this->id) {
                $header->main_page_setting_id = $this->id;
            }
        }

        return $header;
    }

    




    public function getMainPageInstructionText()
    {
        return $this->hasOne(MainPageInstructionText::class, ['main_page_setting_id' => 'id']);
    }

    


    public function getOrBuildText(): MainPageInstructionText
    {
        $text = $this->mainPageInstructionText;
        if (!$text) {
            $text = new MainPageInstructionText();
            if ($this->id) {
                $text->main_page_setting_id = $this->id;
            }
        }

        return $text;
    }

    


    private static function getRelatedInstructionClasses(): array
    {
        return [
            (new MainPageInstructionText)->formName() => MainPageInstructionText::class,
            (new MainPageInstructionImage)->formName() => MainPageInstructionImage::class,
            (new MainPageInstructionVideo)->formName() => MainPageInstructionVideo::class,
            (new MainPageInstructionHeader)->formName() => MainPageInstructionHeader::class,
        ];
    }

    




    public static function loadFromPost(User $currentUser): array
    {
        $postData = Yii::$app->request->post();
        $sortableElements = json_decode(
            base64_decode(
                ArrayHelper::getValue(
                    $postData,
                    (new MainPageSetting)->formName() . '.sortableElements',
                    ''
                )
            ),
            true
        );

        $load = [];
        foreach ($sortableElements as $sortableElement) {
            if (!preg_match(MainPageSetting::PARSER_FRONTEND_NAME, $sortableElement, $matches)) {
                continue;
            }

            $mainPageSettingId = $matches[3];
            $instructionForm   = $matches[1];
            $class             =  ArrayHelper::getValue(MainPageSetting::getRelatedInstructionClasses(), $instructionForm);
            if (!$class) {
                throw new UserException("Передан неизвестная модель {$instructionForm} макета");
            }

            $load[] = MainPageSetting::loadOneSortableElementFromPost(
                $currentUser,
                $postData,
                $class,
                $instructionForm,
                $mainPageSettingId
            );
        }

        return $load;
    }

    








    private static function loadOneSortableElementFromPost(
        User   $currentUser,
        array  $postData,
        string $class,
        string $instructionForm,
        string $mainPageSettingId
    ): ActiveRecord {
        $instructionData = $class::getInstructionData($postData, $instructionForm, $mainPageSettingId);
        $instructionPoint = MainPageSetting::getOrBuildInstructionPointFromMainPageSettingId($mainPageSettingId, $class);

        foreach ($instructionData as $attribute => $value) {
            $instructionPoint->{$attribute} = $value;
        }
        if ($instructionPoint instanceof MainPageInstructionFile) {
            $instructionPoint->user_id = $currentUser->id;
            $instructionPoint->uploadFromPost($mainPageSettingId);
        }

        return $instructionPoint;
    }

    




    public static function getRelatedInstruction(MainPageSetting $setting): ?ActiveRecord
    {
        $instructionsList = [
            'mainPageInstructionText',
            'mainPageInstructionImage',
            'mainPageInstructionVideo',
            'mainPageInstructionHeader',
        ];
        foreach ($instructionsList as $instruction) {
            if ($tmpInstruction = $setting->{$instruction}) {
                return $tmpInstruction;
            }
        }

        return null;
    }

    public static function getInstructions(): array
    {
        $instructions = [];

        $tnMainPageSetting = MainPageSetting::tableName();
        $settings = MainPageSetting::find()
            ->joinWith('mainPageInstructionText')
            ->joinWith('mainPageInstructionImage')
            ->joinWith('mainPageInstructionVideo')
            ->joinWith('mainPageInstructionHeader')
            ->orderBy("{$tnMainPageSetting}.number")
            ->all();
        foreach ($settings as $setting) {
            

            if ($tmpInstruction = MainPageSetting::getRelatedInstruction($setting)) {
                $instructions[] = $tmpInstruction;
            }
        }

        return $instructions;
    }

    





    private static function getOrBuildInstructionPointFromMainPageSettingId(
        string $mainPageSettingId,
        string $class
    ): ActiveRecord {
        $instructionPoint = new $class();

        if (strpos($mainPageSettingId, 'new') === false) {
            $tnClass = $class::tableName();

            $instructionPoint = $class::find()
                ->andWhere(["{$tnClass}.main_page_setting_id" => $mainPageSettingId])
                ->one();
            if (!$instructionPoint) {
                $instructionPoint = new $class();
            }
        }

        return $instructionPoint;
    }
}
