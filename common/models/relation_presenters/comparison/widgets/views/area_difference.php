<?php








$style = '';
if ($wrap_in_flexbox) {
    $style = 'display: -webkit-box;display: -ms-flexbox;display: flex;-webkit-box-orient: horizontal;-webkit-box-direction: normal;-ms-flex-direction: row;flex-direction: row;';
}
?>
<div class="<?= $difference ? $difference_class : '' ?>" style="<?= $style ?>">
    <?= $content ?>
    <?= $difference ?>
</div>
