<div class="mx-gutters abitlist-header">

    <h3 class="text-center"><?php echo $data->speciality; ?></h3>
    <h4 class="text-center"><?php echo $data->date; ?></h4>
    <h4 class="text-center"><?php echo $data->admission_phase; ?> этап. Зачисление на <?php echo $data->taken_percent; ?>% бюджетных мест</h4>

    <div class="col-4 offset-8">
        <table class="table">
            <thead>
                <tr>
                    <th colspan="2">
                        Количество бюджетных мест
                    </th>
                </tr>
            </thead>
            <tr>
                <td>Квота Крым</td>
                <td><?php echo $data->crimea_count; ?></td>
            </tr>
            <tr>
                <td>Квота особого права</td>
                <td><?php echo $data->special_count; ?></td>
            </tr>
            <tr>
                <td>Квота целевого приема</td>
                <td><?php echo $data->target_count; ?></td>
            </tr>
            <tr>
                <td>Общий конкурс</td>
                <td><?php echo $data->competition_count; ?></td>
            </tr>
            <tr>
                <td><strong>Всего</strong></td>
                <td><strong><?php echo $data->total_count; ?></strong></td>
            </tr>
        </table>
    </div>

</div>
<div class="mx-gutters">
    <table class="table table-bordered table-striped table-spec-list small-font">
        <thead>
            <tr>
                <th>№ п/п</th>
                <th>Рег. №</th>
                <th>ФИО</th>
                <th>Приоритет</th>
                <th>Сумма баллов по предметам</th>
                <th>Сумма баллов за индивидуальные достижения</th>
                <th>Общая сумма баллов</th>
                <th>Условие приема</th>
                <th>Решение ПК</th>
            </tr>
        </thead>

        <?php foreach ($data->rows as $row) : ?>
            <tr id="<?php echo $row->user_guid; ?>" <?php if (isset($code) && $code == $row->user_guid) : ?>class="cur-fio" <?php endif; ?>>
                <td><?php echo $row->row_number; ?></td>
                <td><?php echo $row->abit_regnumber; ?></td>
                <td><?php echo $row->fio; ?></td>
                <td><?php echo $row->speciality_priority; ?></td>
                <td><?php echo $row->exam_points; ?></td>
                <td><?php echo $row->id_points; ?></td>
                <td><?php echo $row->total_points; ?></td>
                <td><?php echo $row->special; ?></td>
                <td><?php echo $row->abiturient_state; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>