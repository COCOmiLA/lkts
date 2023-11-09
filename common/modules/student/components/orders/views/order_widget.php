<?php

use yii\web\View;

$script = '
    $(document).ready(function(){
        $("#speciality-select").change(function(){
            var ocId = $("#speciality-select option:selected").val();
            $(".order-container").hide();
            $("#"+ocId).fadeIn();
        });
    }); 
;';

$this->registerJs($script, View::POS_END);
?>

<div class="order-widget">
    <div class="order-header">
        <?php if(sizeof($list) == 1):
            $speciality_name = array_keys($list);
            ?>
            <h4 class="order-header-title"><strong>Специальность:</strong> <?php echo $speciality_name[0]; ?></h4>
            <br />
        <?php else: ?>
            <label for="speciality-select">Специальность:</label>
            <select id="speciality-select" class="form-control">
                <?php
                $specialities_name = array_keys($list);
                foreach($specialities_name as $speciality_key => $speciality_name): ?>
                    <option value="oc-<?php echo $speciality_key; ?>"><?php echo $speciality_name; ?></option>
                <?php endforeach; ?>
            </select>
            <br/>
        <?php endif; ?>
    </div>
    <div class="order-body">
        <?php $is_first_order = true; $i = 0;
        foreach($list as $orders): ?>
            <div id="oc-<?php echo $i; ?>" class="order-container <?php if($is_first_order) { echo 'active'; $is_first_order = false; } ?>" <?php if($i > 0) { echo "style='display: none' "; } ?> >
                <table class="table table-striped table-hover grade-table" style="font-size: 80%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Наименование приказа</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $j = 1;
                    foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $j; $j++; ?></td>
                            <td><?php echo $order; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php
            $i++;
        endforeach;
        ?>
    </div>
</div>
