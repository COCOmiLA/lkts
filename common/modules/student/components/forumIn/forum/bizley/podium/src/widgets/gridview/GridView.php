<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\gridview;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\PageSizer;
use yii\grid\GridView as YiiGridView;
use yii\widgets\Pjax;







class GridView extends YiiGridView
{
    



    public $options = ['class' => 'grid-view table-responsive'];
    


    public $dataColumnClass = 'common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\gridview\DataColumn';
    


    public $tableOptions = ['class' => 'table table-striped table-hover'];
    


    public $filterSelector = 'select#per-page';

    



    public function init()
    {
        parent::init();
        $this->formatter = Podium::getInstance()->formatter;
    }

    


    public function run()
    {
        Pjax::begin();
        echo PageSizer::widget();
        parent::run();
        Pjax::end();
    }
}
