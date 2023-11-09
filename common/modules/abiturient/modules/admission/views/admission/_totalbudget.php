<div class="mx-gutters abitlist-header">
    <p><span class="row-title">Дата:</span> <?php echo $data->dateBegin; ?> &ndash; <?php echo $data->dateEnd; ?></p>
    <p><span class="row-title">Квалификация:</span> <?php echo $data->qualification; ?></p>
    <p><span class="row-title">Форма обучения:</span> <?php echo $data->learnForm ?></p>
    <p><span class="row-title">Форма финансирования:</span> <strong><?php echo $data->financeForm ?></strong></p>
</div>
<div class="mx-gutters">
    <table class="table table-bordered table-striped table-spec-list">
        <thead>
        <tr>
            <th>Институт</th>
            <th>Код</th>
            <th>Направление подготовки / Специальность</th>
            <th>КЦП</th>
            <th>Количество поступающих</th>
            <th>Количество оригиналов</th>
            <th>Количество заявлений</th>
        </tr>
        </thead>
        <?php foreach($data->rows as $row): ?>
        <tr>
            <td><?php echo $row->department; ?></td>
            <td><?php echo $row->speciality_code; ?></td>
            <td><?php echo $row->speciality_name; ?></td>
            <td><?php echo $row->admission_plan; ?></td>
            <td><?php echo $row->abiturient_count; ?></td>
            <td><?php echo $row->doc_original_count; ?></td>
            <td><?php echo $row->application_count; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
