<?php

namespace backend\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Menu as WidgetsMenu;





class Menu extends WidgetsMenu
{
    


    public $itemOptions = ['class' => 'nav-item'];

    


    public $linkTemplate = '
        <a class="nav-link d-flex flex-row" href="{url}">
            {icon}

            <div class="mr-2">
                {label}
            </div>

            {right-icon}

            {badge}
        </a>
    ';

    


    public $labelTemplate = '{icon}{label}{badge}';

    


    public $badgeTag = 'span';
    


    public $badgeClass = 'badge ml-auto';
    


    public $badgeBgClass;

    


    public $parentRightIcon = '<i class="right fa fa-angle-left"></i>';

    


    protected function renderItem($item)
    {
        $item['badgeOptions'] = $item['badgeOptions'] ?? [];

        if (!ArrayHelper::getValue($item, 'badgeOptions.class')) {
            $bg = $item['badgeBgClass'] ?? $this->badgeBgClass;
            $item['badgeOptions']['class'] = $this->badgeClass . ' ' . $bg;
        }

        if (isset($item['items']) && !isset($item['right-icon'])) {
            $item['right-icon'] = $this->parentRightIcon;
        }

        if (isset($item['url'])) {
            $template = ArrayHelper::getValue($item, 'template', $this->linkTemplate);

            return strtr($template, [
                '{badge}' => isset($item['badge'])
                    ? Html::tag('small', $item['badge'], $item['badgeOptions'])
                    : '',
                '{icon}' => $item['icon'] ?? '',
                '{right-icon}' => $item['right-icon'] ?? '',
                '{url}' => Url::to($item['url']),
                '{label}' => $item['label'],
            ]);
        } else {
            $template = ArrayHelper::getValue($item, 'template', $this->labelTemplate);

            return strtr($template, [
                '{badge}' => isset($item['badge'])
                    ? Html::tag('small', $item['badge'], $item['badgeOptions'])
                    : '',
                '{icon}' => $item['icon'] ?? '',
                '{right-icon}' => $item['right-icon'] ?? '',
                '{label}' => $item['label'],
            ]);
        }
    }
}
