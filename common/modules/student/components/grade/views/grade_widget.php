<?php

use common\modules\student\components\grade\models\GradeTableRenderer;
use yii\web\View;






$active_grade = Yii::$app->request->get('active_grade');
$script = <<<JS
    var speciality_select=$("#speciality-select");
    var initialGcId = speciality_select.find('option:selected');
    if (initialGcId.length > 0){
        $(".grade-container").hide();
        $("#"+initialGcId.val()).show();  
    }

    speciality_select.change(function(){
        var gcId = $("#speciality-select option:selected").val();
        $(".grade-container").hide();
        $("#"+gcId).fadeIn();
    });
JS;

$this->registerJs($script);
?>
<div class="grade-widget">
    <div class="grade-header">
        <?php if (sizeof($grades) == 1) :
            $speciality_name = array_keys($grades); ?>
            <h4 class="grade-header-title"><strong>Специальность:</strong> <?php echo $speciality_name[0]; ?></h4>
            <br />
        <?php else : ?>
            <label for="speciality-select">Специальность:</label>
            <select id="speciality-select" class="form-control">
                <?php
                $first_option_should_be_selected = empty($active_grade);
                $specialities_name = array_keys($grades);
                foreach ($specialities_name as $speciality_key => $speciality_name) :
                    $adjust_selected_option = '';
                    if ((!empty($active_grade) && $speciality_name == $active_grade) || $first_option_should_be_selected) {
                        $adjust_selected_option = 'selected';
                        $first_option_should_be_selected = false;
                    } ?>
                    <option value="gc-<?php echo $speciality_key; ?>" <?= $adjust_selected_option ?>><?php echo $speciality_name; ?></option>
                    <?php $adjust_selected_option = ''; ?>
                <?php endforeach; ?>
            </select>
            <br />
        <?php endif; ?>
    </div>

    <div class="grade-body">
        <?php $is_first_grade = true;
        $k = 0;
        foreach ($grades as $speciality_name => $grade) : ?>
            <?php $activeClass = '';
            if ((!empty($active_grade) && $speciality_name == $active_grade) || $is_first_grade) {
                $activeClass = 'active';
                $is_first_grade = false;
            } ?>
            <div id="gc-<?php echo $k; ?>" class="grade-container <?= $activeClass ?>">
                <ul id="grade-tabs-<?php echo $k; ?>" class="nav nav-tabs">
                    <?php
                    $first_term = true;
                    $old_term = '';
                    $i = 0;
                    foreach ($grade as $grade_value) :
                        $term = $grade_value->Term;
                        if ($term != $old_term) :
                            $old_term = $term;
                            $activeClass = '';
                            if ($first_term) {
                                $activeClass = 'active';
                                $first_term = false;
                            } ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeClass ?>" data-toggle="tab" href="#tab-<?= $k . '-' . $i; ?>"><?= $term; ?></a>
                            </li>
                            <?php $i++; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <div id="tab-content-<?php echo $k; ?>" class="tab-content" style="border: 0">
                    <?php
                    $first_term = true;
                    $old_term = '';
                    $i = 0;
                    foreach ($grade as $grade_value) :
                        $term = $grade_value->Term;
                        if ($term != $old_term) :
                            $old_term = $term;
                            $activeClass = '';
                            if ($first_term) {
                                $activeClass = 'active show';
                                $first_term = false;
                            } ?>
                            <div id="tab-<?= $k . '-' . $i ?>" class="tab-pane fade in <?= $activeClass ?>">
                                <?php $i++;
                                $tableArray = GradeTableRenderer::getFormattedRowsForTerm($grade, $term); ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Предмет</th>
                                                <th>Вид контроля</th>
                                                <th>Оценка</th>
                                                <th>ЗЕТ</th>
                                                <th>Часы</th>
                                                <th>Дата</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php GradeTableRenderer::renderTableContent($tableArray); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php $k++; ?>
        <?php endforeach; ?>
    </div>
</div>