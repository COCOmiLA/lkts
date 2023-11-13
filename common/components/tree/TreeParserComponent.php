<?php

namespace common\components\tree;

use common\components\BooleanCaster;
use common\components\tree\assets\TreeParserAsset;
use common\services\NamesManagementService;
use sguinfocom\widget\TreeView;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;






class TreeParserComponent extends Component
{
    protected NamesManagementService $namesManagementService;

    public function __construct(NamesManagementService $namesManagementService, $config = [])
    {
        parent::__construct($config);
        $this->namesManagementService = $namesManagementService;
    }

    







    public function parseTree($treeSource, $puid, $luid)
    {
        $a = array_shift($treeSource);
        $result = [];
        if (isset($a['properties'])) {
            $name = ArrayHelper::getValue($a, 'name');
            $result['text'] = $name ?? '';
            $result['nodes'][] = $this->parseSubTree($a['properties'], $puid, $luid);
        }
        return $result;
    }

    







    private function parseSubTree($treeSource, $puid, $luid)
    {
        $_treeSource = $treeSource;

        if (!is_array($_treeSource)) {
            $_treeSource = [$_treeSource];
        }
        $result = [];
        $PropertyAttributeValue = $this->namesManagementService->getPropertyAttributeValueColumnName();
        $PropertyAttributeName = $this->namesManagementService->getPropertyAttributeNameColumnName();

        foreach ($_treeSource as $item) {
            $anchor = '';
            if (isset($item->LapStrings)) {
                if (is_array($item->LapStrings)) {
                    foreach ($item->LapStrings as $item_LapStrings) {
                        $tmp = $this->parseSubTree($item_LapStrings, $puid, $luid);
                        if (!is_null($tmp)) {
                            $result['nodes'][] = $tmp;
                        }
                    }
                } else {
                    $tmp = $this->parseSubTree($item->LapStrings, $puid, $luid);
                    if (!is_null($tmp)) {
                        $result['nodes'][] = $tmp;
                    }
                }
            } else {
                if (isset($item->LapAttributes)) {
                    $attributes = $item->LapAttributes;
                    if (!is_array($attributes)) {
                        $attributes = [$attributes];
                    }
                    foreach ($attributes as $attribute) {
                        if (isset($attribute->{$PropertyAttributeName}) && $attribute->{$PropertyAttributeName} == 'Видимость') {
                            $value = $attribute->{$PropertyAttributeValue};
                            if (!BooleanCaster::cast($value)) {
                                return null;
                            }
                        }
                    }
                }

                $anchor = md5(json_encode($item));
                $result['href'] = Url::current([
                    'puid' => $item->Plan->ReferenceUID,
                    'luid' => $item->LapUID,
                    '#' => $anchor
                ]);
            }
            $result['text'] = "<div class='text_li' data-anchor='#{$anchor}'>{$item->LapName}</div>";
        }
        return $result;
    }

    






    public function treeShow($view, $treeArray)
    {
        if (key_exists('node', $treeArray) || key_exists('text', $treeArray)) {
            $treeArray = [$treeArray];
        }

        TreeParserAsset::register($view);

        $template = '
            <div class="tree-view-wrapper">
                <div class="row tree-header">
                    <div class="col-12">{search}</div>
                </div>

                <div class="row">
                    <div class="col-12">{tree}</div>
                </div>
            </div>
        ';

        return TreeView::widget([
            'header' => '',
            'id' => 'tree_stage',
            'data' => $treeArray,
            'template' => $template,
            'size' => TreeView::SIZE_SMALL,
            'searchOptions' => [
                'inputOptions' => [
                    'placeholder' => 'Введите этап...'
                ],
                'clearButtonOptions' => [
                    'title' => 'Clear',
                ],
            ],
            'clientOptions' => [
                'onNodeSelected' => new JsExpression('
                    function(event, data) {
                        if (data.href){
                            window.location.href = data.href;
                        }
                    }'),
                'onRendered' => new JsExpression('
                    function() {
                        window.highlighter();
                    }'),
                'onNodeExpanded' => new JsExpression('
                    function() {
                        window.widthSetter();
                    }'),
                'borderColor' => 'var(--white)',
                'enableLinks' => true,
                'levels' => 5,
            ],
        ]);
    }
}
