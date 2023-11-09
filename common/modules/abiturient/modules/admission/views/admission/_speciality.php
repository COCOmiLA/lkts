<div class="mx-gutters abitlist-header">
    <p><span class="row-title">Дата:</span> <?php echo $data->dateBegin; ?> &ndash; <?php echo $data->dateEnd; ?> <span class="float-right">БВИ – Без вступительных испытаний</span></p>
    <p><span class="row-title">Квалификация:</span> <?php echo $data->qualification; ?> <span class="float-right">ОП – Особое право</span></p>
    <p><span class="float-right">ЦП – Целевой прием</span></p><br>
    <p> <span class="float-right">К – Крым</span></p><br>
    <p> <span class="float-right">ПП – Преимущественное право</span></p>
</div>
<div class="mx-gutters">
    <table class="table table-bordered table-striped table-spec-list">
        <thead>
        <tr>
            <th rowspan="2">№ п/п</th>
            <th rowspan="2">Рег. №</th>
            <th rowspan="2">ФИО</th>
            <th colspan="4">Код направления подготовки/специальности</th>
            <th rowspan="2">Форма вступительного испытания</th>
            <th rowspan="2">Условие поступления</th>
            <th rowspan="2">Оригинал</th>
            <th rowspan="2">Общежитие</th>
        </tr>
        <tr>
            <th>1 приоритет</th>
            <th>2 приоритет</th>
            <th>3 приоритет</th>
            <th>4 приоритет</th>
        </tr>
        </thead>
        <?php foreach($data->rows as $row): ?>
        <tr>
            <td><?php echo $row->row_number; ?></td>
            <td><?php echo $row->abit_regnumber; ?></td>
            <td><?php echo $row->fio; ?></td>
            <td><?php echo $row->speciality_1; ?></td>
            <td><?php echo $row->speciality_2; ?></td>
            <td><?php echo $row->speciality_3; ?></td>
            <td><?php echo $row->speciality_4; ?></td>
            <td><?php echo $row->exam_form; ?></td>
            <td><?php echo $row->admission_condition; ?></td>
            <td><?php echo $row->have_original; ?></td>
            <td><?php echo $row->need_dormitory; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
