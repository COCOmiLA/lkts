<?php $questionaryFileError = Yii::$app->configurationManager->getText('questionary_file_error'); ?>

<?php if (!empty($questionaryFileError) || !empty($attachmentErrors)) : ?>
    <div class="alert alert-danger" role="alert">
        <?php if (!empty($questionaryFileError)) : ?>
            <?= $questionaryFileError; ?><br>
        <?php endif; ?>

        <?php if (!empty($attachmentErrors)) : ?>
            <ul style="margin-left: 20px">
                <?php foreach ($attachmentErrors as $key => $error) : ?>
                    <li><?= $error; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif;