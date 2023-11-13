<?php



?>
<div class="">

    <main>
        <?php if ($summary['errors'] > 0): ?>
            <div id="requirements" class="alert alert-danger">
                <strong>К сожалению, конфигурация вашего сервера не удовлетворяет требованиям. Обратите внимание на подробное описание ниже.</strong>
            </div>
        <?php elseif ($summary['warnings'] > 0): ?>
            <div id="requirements" class="alert alert-info">
                <strong>Конфигурация вашего сервера удовлетворяет минимальным требованиям. Обратите внимания на предупреждения ниже.</strong>
            </div>
        <?php else: ?>
            <div id="requirements" class="alert alert-success">
                <strong>Конфигурация сервера удовлетворяет всем требованиям.</strong>
            </div>
        <?php endif; ?>

        <h3>Подробности:</h3>

        <table class="table table-bordered">
            <tr><th>Наименование</th><th>Результат</th><th>Описание</th></tr>
            <?php foreach ($requirements as $requirement): ?>
            <tr class="<?php echo $requirement['condition'] ? 'success' : ($requirement['mandatory'] ? 'danger' : 'warning') ?>">
                <td>
                <?php echo $requirement['name']; ?>
                </td>
                <td>
                <span class="result">
                    <?php
                    if ($requirement['condition']) {
                        echo '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                    } elseif ($requirement['mandatory']) {
                        echo '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';
                    } else {
                        echo '<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>';
                    }
                    ?>
                </span>
                </td>
                <td>
                <?php echo $requirement['memo']; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</div>

