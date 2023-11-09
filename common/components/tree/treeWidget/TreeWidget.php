<?php

namespace common\components\tree\treeWidget;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

class TreeWidget extends Widget
{

    public $tree;
    public $treeArray;
    public $tableBodies = [];
    public $tablesCounter = 0;

    public function init()
    {
        parent::init();

        $this->tree = $this->makeTree($this->treeArray);
    }

    public function run()
    {
        return $this->render('tree_widget', [
            'tree' => $this->tree,
            'tableBodies' => $this->tableBodies,

        ]);
    }

    private function makeTree($array)
    {
        $html = '<ul class="tree-ul">';

        if (!isset($array['properties'])) {
            foreach ($array as $i => $element) {
                $html .= '<li class="tree-item">';
                $html .= $i;
                if (is_array($element)) {
                    $html .= $this->makeTree($element);
                }

                $html .= '</li>';
            }
        } else {
            $html .= '<li class="tree-item">';
            if (isset($array['properties'])) {

                $this->tablesCounter++;
                $html .= '<a tree-tbody-id="' . $this->tablesCounter . '" href="#" class="type-link">' . $array['name'] . '</a>';
                $this->tableBodies[] = $this->makeTableBody($array['properties']);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    
    private function makeTableBody($properties)
    {

        $tableBody = '<tbody class="tree-tbody" style="display: none" id="tbody-' . $this->tablesCounter . '">';

        $form_fields_names = array();

        foreach ($properties->properties as $property) {
            $tableBody .= '<tr>';
            $tableBody .= '<td>';
            $tableBody .= $property->name;
            $tableBody .= '</td>';
            $tableBody .= '<td>';
            $tableBody .= $property->value;
            $tableBody .= '</td>';
            $tableBody .= '</tr>';

            $form_fields_names[] = $property->name;
        }

        
        $tableBody .= '<tr>';
        $tableBody .= '<td colspan="2">';
        $tableBody .= Html::button('Добавить данные', ['value' => Url::to(['student/portfolio/get-form']), 'data' => json_encode($form_fields_names), 'title' => 'Добавление данных', 'class' => 'showModalButton btn btn-sm btn-primary']);
        $tableBody .= '</td>';
        $tableBody .= '</tr>';

        $tableBody .= '</tbody>';


        return $tableBody;
    }
}
