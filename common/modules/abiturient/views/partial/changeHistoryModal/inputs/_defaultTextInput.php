<?php if (isset($identifier)) : ?>
    <div class="row">
        <div class="col-12">
            <span class="change--identifier">
                <?= $identifier ?>
            </span>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($value)) : ?>
    <div class="change-value-input">
        <div class="row">
            <div class="col-1 change-value--icon">
                <i class="fa fa-plus"></i>
            </div>
            <div class="col-md-4 col-10 change-value--name">
                <?= $input_name ?>:
            </div>
            <div class="col-md-7 col-12 change-value--value">
                <?= $value ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($old_value)) : ?>
    <div class="change-old-value-input">
        <div class="row">
            <div class="col-1 change-value--icon">
                <i class="fa fa-minus"></i>
            </div>
            <div class="col-md-4 col-10 change-value--name">
                <?= $input_name ?>:
            </div>
            <div class="col-md-7 col-12 change-value--value">
                <?= $old_value ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($value, $old_value)) : ?>
    <div class="change-value--divider">

    </div>
<?php endif; ?>
<style>
    .change-value--icon {
        font-size: 12px;
        text-align: center;
        opacity: 1;
    }

    .change-value--name {
        font-style: italic;
    }

    .change-value-input .change-value--value {
        font-weight: bold;
    }

    .change-value-input {
        padding: 10px;
        overflow-wrap: break-word;
    }

    .change-value-input .change-value--icon {
        color: var(--teal);
    }

    .change-old-value-input .change-value--icon {
        color: var(--gray);
    }

    .change-old-value-input {
        padding: 10px;
        color: var(--gray);
        overflow-wrap: break-word;
    }

    .change--identifier {
        font-style: italic;
    }

    .change-value--divider {
        margin-bottom: 10px;
    }
</style>