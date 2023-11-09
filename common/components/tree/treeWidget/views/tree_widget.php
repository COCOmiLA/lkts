<?php

use yii\web\View;

$this->registerJS(<<<JS
    $('.type-link').click(function(e) {
       $('.tree-tbody').css('display', 'none');
       $('#tbody-' + $(this).attr('tree-tbody-id')).css('display', '');   
    });

    $(document).on('click', '.showModalButton', function(){
        
        var properties = {
            properties: $(this).attr('data')
        };
        
        $('#modal').modal('show')
            .find('#modalContent')
            .load($(this).attr('value'), properties);
    });
JS
, View::POS_READY);


yii\bootstrap4\Modal::begin([
    'title' => '<h4>Добавление данных</h4>',
    'id' => 'modal',
]);

echo "<div id='modalContent'></div>";

yii\bootstrap4\Modal::end();
?>

<div class="tree-container">
    <div class="tree-header">
    </div>
    <div class="tree-body">
        <div class="col-3">
            <?= $tree ?>
        </div>
        <div class="col-9">
            <table class="table table-striped">
                <thead class="tree-thead">
                    <tr>
                        <th>Свойство</th>
                        <th>Значение</th>
                    </tr>
                </thead>
                <?php



                ?>
            </table>
        </div>
    </div>
</div>
