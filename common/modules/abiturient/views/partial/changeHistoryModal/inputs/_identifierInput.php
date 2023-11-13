<?php if (isset($identifier)) : ?>
    <div class="row change--identifier-wrapper">
        <div class="col-12">
            <span class="change--identifier">
                <strong>
                    <?= $identifier ?>
                </strong>
            </span>
        </div>
    </div>
<?php endif; ?>

<style>
    .change--identifier {
        overflow-wrap: break-word;
        font-style: italic;
    }

    .change--identifier-wrapper {
        margin: 10px 0;
    }
</style>