<?php








echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use common\components\Migration\SafeMigration;

/**
 * Handles the dropping of table `<?= $table ?>`.
<?= $this->render('_foreignTables', [
    'foreignKeys' => $foreignKeys,
]) ?>
 */
class <?= $className ?> extends SafeMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
<?= $this->render('_dropTable', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
])
?>
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
<?= $this->render('_createTable', [
    'table' => $table,
    'fields' => $fields,
    'foreignKeys' => $foreignKeys,
])
?>
    }
}
