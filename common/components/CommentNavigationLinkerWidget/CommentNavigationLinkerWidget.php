<?php


namespace common\components\CommentNavigationLinkerWidget;

use common\models\EmptyCheck;
use common\models\settings\TagDictionary;
use common\modules\abiturient\models\bachelor\ApplicationType;
use kartik\helpers\Html;
use Yii;
use yii\base\Widget;

class CommentNavigationLinkerWidget extends Widget
{
    public const TAG_TEMPLATE = '[{{ALIAS}}|{{TAG}}]';

    private const TAG_PATTERN = "/\\[([^\\]]+)\\|(#[a-z\x{0410}-\x{042F}_]+)\\]/iu";
    
    
    
    
    
    
    
    
    
    
    
    
    

    
    public $textAriaId;

    
    public $btnClass;

    
    public $role = TagDictionary::ABITURIENT_ROLE;

    
    public $applicationType;

    public function run()
    {
        if (!$this->btnClass) {
            $this->btnClass = 'btn btn-outline-secondary';
        }

        $tn = TagDictionary::tableName();
        $rawTags = TagDictionary::find()
            ->select(["{$tn}.id", "{$tn}.tag", "{$tn}.description", "{$tn}.default_alias", "{$tn}.icon"])
            ->andWhere(["{$tn}.role" => $this->role])
            ->andWhere(['NOT IN', "{$tn}.tag", $this->getExceptTagList()])
            ->groupBy(["{$tn}.id", "{$tn}.tag", "{$tn}.description", "{$tn}.default_alias", "{$tn}.icon"])
            ->all();

        $tags = [];
        foreach ($rawTags as $tag) {
            
            $btnId = md5("{$tag->description} {$tag->default_alias}");
            $tags[] = json_encode([
                'btn_id'        => $btnId,
                'btn_class'     => $this->btnClass,
                'tag'           => $tag->tag,
                'icon_class'    => $tag->icon,
                'text_aria_id'  => $this->textAriaId,
                'title'         => $tag->description,
                'default_alias' => $tag->default_alias,
                'tag_template'  => CommentNavigationLinkerWidget::TAG_TEMPLATE,
            ]);
        }

        return $this->render(
            'comment_navigation_linker',
            ['tags' => $tags]
        );
    }

    


    private function getExceptTagList(): array
    {
        if (!$this->applicationType) {
            return [];
        }
        $exceptTagList = [];

        if ($this->applicationType->hide_ege) {
            $exceptTagList[] = '#заявление_наборы_вступительных_испытаний';
            $exceptTagList[] = '#заявление_результаты_вступительных_испытаний';
        }

        if ($this->applicationType->hide_benefits_block) {
            $exceptTagList[] = '#заявление_льготы';
            $exceptTagList[] = '#заявление_без_вступительных_испытаний';
        }

        if ($this->applicationType->hide_ind_ach) {
            $exceptTagList[] = '#заявление_индивидуальные_достижения';
        }

        if ($this->applicationType->hide_scans_page) {
            $exceptTagList[] = '#заявление_сканы_документов';
        }

        if (!$this->applicationType->can_see_actual_address) {
            $exceptTagList[] = '#анкета_адрес_проживания';
        }

        return $exceptTagList;
    }

    





    public static function renderFormattedModeratorComment(?string $comment, int $id): ?string
    {
        $comment = Html::encode($comment);
        $comment = nl2br($comment);
        if (EmptyCheck::isEmpty($comment)) {
            return null;
        }

        $pattern = CommentNavigationLinkerWidget::TAG_PATTERN;
        if (!preg_match_all(
            $pattern,
            $comment,
            $matches,
            PREG_PATTERN_ORDER
        )) {
            return $comment;
        }
        $role = Yii::$app->user->identity->isModer() ?
            TagDictionary::MODERATOR_ROLE :
            TagDictionary::ABITURIENT_ROLE;

        [
            $placeToReplace,
            $tagAliases,
            $tags,
        ] = $matches;
        for ($i = 0; $i < count($tags); $i++) {
            $url = null;
            $aliase = trim($tagAliases[$i]);
            $tag = TagDictionary::findOne([
                'tag' => $tags[$i],
                'role' => $role,
            ]);
            if ($tag) {
                $url = $tag->getUrl($id);
                if (empty($aliase)) {
                    $aliase = $tag->default_alias;
                }
            }

            $comment = strtr(
                $comment,
                [$placeToReplace[$i] => Html::a($aliase, $url)]
            );
        }

        return $comment;
    }
}
