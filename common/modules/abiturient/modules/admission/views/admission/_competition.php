<div class="row abitlist-header">
    <?php if (isset($data)): ?>
    <div class="col-4">
        <p><span class="row-title">Дата:</span> <?php echo $data->date; ?></p>
        <p><span class="row-title">Квалификация:</span> <?php echo $data->qualification; ?></p>
        <p><span class="row-title">Форма обучения:</span> <?php echo $data->learnForm ?></p>
        <p><span class="row-title">Форма финансирования:</span> <?php echo $data->financeForm ?></p>
        <p><span class="row-title">Институт:</span> <?php echo $data->institute ?></p>
        <p><span class="row-title">Направление подготовки / специальность:</span> <?php echo $data->speciality ?></p>
    </div>
    <div class="col-4">
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
    <div class="col-4">
        <table class="table">
            <thead>
            <tr>
                <th colspan="2">
                    Сокращения
                </th>
            </tr>
            </thead>
            <tr>
                <td>К</td>
                <td>Крым</td>
            </tr>
            <tr>
                <td>БВИ</td>
                <td>Без вступительных испытаний</td>
            </tr>
            <tr>
                <td>ОП</td>
                <td>Особое право</td>
            </tr>
            <tr>
                <td>ЦП</td>
                <td>Целевой прием</td>
            </tr>
            <tr>
                <td>ПП</td>
                <td>Преимущественное право</td>
            </tr>
        </table>
    </div>
</div>
<div class="mx-gutters">
    <table class="table table-bordered table-striped table-spec-list small-font">
        <thead>
        <tr>
            <th rowspan="2">№ п/п</th>

            <th rowspan="2">ФИО</th>
            <th rowspan="2">Общая сумма баллов</th>
            <th colspan="4">Баллы ЕГЭ и ВИ</th>
            <th rowspan="2">Баллы ИД</th>
            <th rowspan="2">Приоритет</th>
            <th rowspan="2">Оригинал</th>
            <th rowspan="2">Условие приема</th>
            <th rowspan="2">Потребность в общежитии</th>
            <th rowspan="2">Решение ПК</th>
        </tr>
        <tr>
            <th>Всего</th>
            <th><?php echo $data->exam1; ?></th>
            <th><?php echo $data->exam2; ?></th>
            <th><?php echo $data->exam3; ?></th>
        </tr>
        </thead>
        <?php foreach ($data->rows as $row): ?>
            <tr id="<?php echo $row->user_guid; ?>"
                <?php if (isset($code) && $code == $row->user_guid): ?>class="cur-fio"<?php endif; ?>>
                <td><?php echo $row->row_number; ?></td>

                <td><?php echo $row->fio; ?></td>
                <td><?php echo $row->total_points; ?></td>
                <td><?php echo $row->total_exam_points; ?></td>
                <td><?php echo $row->exam1_points; ?></td>
                <td><?php echo $row->exam2_points; ?></td>
                <td><?php echo $row->exam3_points; ?></td>
                <td><?php echo $row->id_points; ?></td>
                <td><?php echo $row->speciality_priority; ?></td>
                <td><?php echo $row->have_original; ?></td>
                <td><?php echo $row->admission_condition; ?></td>
                <td><?php echo $row->need_dormitory; ?></td>
                <td><?php echo $row->abit_state; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>Записей не найдено</p>
    <?php endif; ?>
</div>
