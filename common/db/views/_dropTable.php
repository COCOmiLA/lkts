<?php






echo $this->render('_dropForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]) ?>
        $this->dropTable('<?= $table ?>');
