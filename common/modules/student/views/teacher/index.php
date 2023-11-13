<?php

use yii\helpers\Url;
use yii\web\View;






$this->title = Yii::$app->name;
?>
<div class="site-index">
    <div class="body-content">
        <?php if(sizeof($routes) > 0): ?>
            <?php $i = 0; foreach($routes as $route => $link_name): ?>
                <?php if($i % 2 == 0): ?>
                    <div class="row">
                <?php endif; ?>
                    <div class="col-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <a href="<?php echo Url::toRoute($route); ?>"><?php echo $link_name; ?></a>
                            </div>
                        </div>
                    </div>  
                <?php if($i % 2 !== 0 || $i == (sizeof($routes)-1)): ?>
                    </div>
                <?php endif; ?>
            <?php $i++; endforeach; ?>
        <?php endif; ?>
    </div>
</div>
