<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$script = <<<JS
    $(document).ready(function() {
        $("#speciality-select").change(function() {
            var ocId = $("#speciality-select option:selected").val();
            $(".block-container").hide();
            $("#"+ocId).fadeIn();
        });

        function check_units(id_chbx = '', is_checked = false) {
            var units_class_name = document.getElementsByClassName("chbx");
            var count_units_class_name = units_class_name.length;

            if (count_units_class_name > 0) {
                var unique_class_id = [];
                var k = 0;
                unique_class_id[k] = units_class_name[k].className.split(" ")[2];

                for (var i = 1; i < count_units_class_name; i++) {
                    var unit_id = units_class_name[i].className.split(" ")[2];

                    if (unique_class_id[k] !== unit_id) {
                        unique_class_id.push(unit_id);
                        k++;
                    }
                }

                if (document.getElementById("speciality-select") === null) {
                    var count_speciality = 1;
                } else {
                    var count_speciality = document.getElementById("speciality-select").length;
                }

                var unit_error = false;
                var count_units = unique_class_id.length;

                document.getElementById("block_alert").innerText = "";

                for (var k = 0; k < count_speciality; k++) {
                    for (var i = 0; i < count_units; i++) {
                        var unit_class_name = ["chbx", k, unique_class_id[i]];
                        var array_unit = document.getElementsByClassName(unit_class_name.join(" "));
                        var count_unit = array_unit.length;

                        var name = '';
                        var rawName = array_unit[0].name;
                        var nameMatches = rawName.match(/\[(.*?)\]/);
                        if (nameMatches) {
                            name = nameMatches[1];
                        }
                        var array_unit_info = decodeURIComponent(escape(window.atob(name))).split("|");
                        var unit_name = array_unit_info[2];
                        var MaxCount = parseInt(array_unit_info[3]);

                        if (array_unit_info[9] === "0") {
                            continue;
                        }

                        var count_checked_in_unit = [];
                        for (var j = 0; j < count_unit; j++) {
                            subjectId = $(array_unit[j]).attr('id').split("_")[4];
                            if (array_unit[j].checked && count_checked_in_unit.indexOf(subjectId) == -1) {
                                count_checked_in_unit.push(subjectId);
                            }
                        }
                        count_checked_in_unit = count_checked_in_unit.length;

                        if (id_chbx.length > 0) {
                            var this_val = id_chbx.split("_")[4];
                            var this_val_page = id_chbx.split("_")[1];
                            for (var q = 0; q < count_unit; q++) {
                                var that_val = $(array_unit[q]).attr('id').split("_")[4];
                                var that_val_page = $(array_unit[q]).attr('id').split("_")[1];
                                if (this_val == that_val && this_val_page == that_val_page && id_chbx != $(array_unit[q]).attr('id')) {
                                    array_unit[q].checked = is_checked;
                                    array_unit[q].disabled = !is_checked;
                                }
                            }
                        }

                        if (count_checked_in_unit !== MaxCount) {
                            unit_error = true;
                        }

                        if (count_checked_in_unit === MaxCount) {
                            var all_is_disabled = true;
                            for (var j = 0; j < count_unit; j++) {
                                if (!array_unit[j].checked) {
                                    array_unit[j].disabled = true;
                                } else {
                                    all_is_disabled = false;
                                }
                            }
                            if (all_is_disabled) {
                                for (var j = 0; j < count_unit; j++) {
                                    array_unit[j].disabled = false;
                                }
                            }
                        } else {
                            for (var j = 0; j < count_unit; j++) {
                                if (!array_unit[j].checked) {
                                    array_unit[j].disabled = false;
                                }
                            }

                            switch (MaxCount) {
                                case 1:
                                    discipline = ["не выбрана", "дисциплина."];
                                    break;
                                case 2:
                                case 3:
                                case 4:
                                    discipline = ["не выбраны", "дисциплины."];
                                    break;
                                default:
                                    discipline = ["не выбрано", "дисциплин."];
                            }

                            if (count_speciality !== 1) {
                                var spec_name = "\u00AB" + document.getElementById("speciality-select").children[k].innerText + "\u00BB";
                                var message = ["Кнопка заблокирована, так как на вкладке", spec_name, "в блоке", unit_name, discipline[0], MaxCount, discipline[1]].join(" ");
                            } else {
                                var message = ["Кнопка заблокирована, так как", "в блоке", unit_name, discipline[0], MaxCount, discipline[1]].join(" ");
                            }

                            var temp_msg = document.getElementById("block_alert").innerText;
                            if (temp_msg.indexOf(message) === -1) {
                                document.getElementById("block_alert").innerText += message + "\\n";
                            }
                        }

                        var unit_label = document.getElementById(k + "_" + unique_class_id[i]);
                        unit_label.innerText = count_checked_in_unit + "/" + MaxCount;
                        if (count_checked_in_unit === MaxCount) {
                            unit_label.className = "badge badge-success";
                        } else {
                            unit_label.className = "badge badge-danger";
                        }
                    }
                }

                if (unit_error) {
                    document.getElementById("block").disabled = true;
                    document.getElementById("block_alert").style.display = "block";
                } else {
                    document.getElementById("block").disabled = false;
                    document.getElementById("block_alert").style.display = "none";
                }
                return unit_error;
            }
        }

        check_units();

        $(".chbx").change(function() {
            check_units($(this).attr('id'), this.checked);
        });

        $("#block_form").submit(function() {
            if (check_units()) {
                return false;
            }
        });
    });
JS;

$this->registerJs($script, View::POS_END);
?>

<div class="block-widget">
    <div class="block-header">
        <?php if (sizeof($list) == 1) :
            $speciality_name = array_keys($list);
        ?>
            <h4 class="block-header-title"><strong>Специальность:</strong> <?php echo $speciality_name[0]; ?></h4>
            <br />
        <?php else : ?>
            <label for="speciality-select">Специальность:</label>
            <select id="speciality-select" class="form-control">
                <?php $specialities_name = array_keys($list);
                foreach ($specialities_name as $speciality_key => $speciality_name) : ?>
                    <option value="oc-<?php echo $speciality_key; ?>"><?php echo $speciality_name; ?></option>
                <?php endforeach; ?>
            </select>
            <br />
        <?php endif; ?>
    </div>

    <?php if ($units_error) : ?>
        <div id="main_alert" class="alert alert-danger">
            При отправке данных произошла ошибка. Пожалуйста, проверьте правильность введенных данных и повторите отправку.
        </div>
    <?php endif; ?>

    <?php if (\Yii::$app->session->hasFlash('individualEducationPredmetsErrors')) : ?>
        <?php foreach (\Yii::$app->session->getFlash('individualEducationPredmetsErrors') as $error) : ?>
            <div class="alert alert-danger">
                <?php echo $error ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="block-body">
        <?php $form = ActiveForm::begin(["id" => "block_form"]);
        $is_first_block = true;
        $i = 0;
        foreach ($list as $specialities_blocks) : ?>
            <div id="oc-<?php echo $i; ?>" class="block-container <?php if ($is_first_block) {
                                                                        echo 'active';
                                                                        $is_first_block = false;
                                                                    } ?>" <?php if ($i > 0) {
                                                                                                                                                echo "style='display: none' ";
                                                                                                                                            } ?>>
                <table class="table table-striped table-hover grade-table" style="font-size: 80%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Наименование блока дисциплин</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($specialities_blocks as $index => $blocks) : ?>
                            <tr style="background-color: var(--light)">
                                <td></td>
                                <td><?php echo $blocks->CourseUnitName; ?></td>
                                <td>
                                    <?php if ($blocks->Variable) : ?>
                                        <span class="badge label-danger" id='<?php echo "{$i}_{$blocks->CourseUnitId}"; ?>'></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php $j = 1;
                            foreach ($blocks->SubjectsString as $block) : ?>
                                <tr style="background-color: var(--white)">
                                    <td style="vertical-align: middle"><?php echo $j;
                                                                        $j++; ?></td>
                                    <td style="vertical-align: middle"><?php echo $block->SubjectName; ?></td>
                                    <td style="vertical-align: middle">
                                        <?php $name = base64_encode(
                                            join(
                                                '|',
                                                [
                                                    $i,
                                                    $block->CourseUnitId,
                                                    $block->CourseUnitName,
                                                    $blocks->MaxCount,
                                                    $block->CurriculumId,
                                                    $block->SubjectId,
                                                    $block->SubjectName,
                                                    (int)$block->SaveChecked,
                                                    (int)$block->DefaultChecked,
                                                    (int)$block->Variable
                                                ]
                                            )
                                        );
                                        $name = "block[{$name}]";
                                        $id = join('_', ['checkbox', $i, $block->CourseUnitId, $block->CurriculumId, $block->SubjectId]);
                                        $cls = join(' ', ['chbx', $i, $block->CourseUnitId]);

                                        if ($block->Variable) {
                                            echo $form->field($block, 'Checked')
                                                ->checkbox([
                                                    'id' => $id,
                                                    'class' => $cls,
                                                    'name' => $name
                                                ]);
                                        } else {
                                            echo $form->field($block, 'Checked')
                                                ->checkbox([
                                                    'id' => $id,
                                                    'class' => $cls,
                                                    'name' => $name, 'disabled' => true
                                                ]);

                                            if ($block->Checked) {
                                                echo Html::hiddenInput($name, 1);
                                            }
                                        } ?>
                                    </td>
                                </tr>
                        <?php endforeach;
                        endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php $i++;
        endforeach; ?>

        <div id="block_alert" class="alert alert-warning">
            Кнопка заблокирована, так как в блоке не все дисциплины выбраны.
        </div>

        <div>
            <?php echo Html::submitButton('Применить', ['id' => 'block', 'class' => 'btn btn-primary', 'style' => 'float:right']); ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>