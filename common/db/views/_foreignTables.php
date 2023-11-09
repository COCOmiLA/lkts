<?php



if (!empty($foreignKeys)):?>
 * Has foreign keys to the tables:
 *
<?php foreach ($foreignKeys as $fkData): ?>
 * - `<?= $fkData['relatedTable'] ?>`
<?php endforeach;
endif;
