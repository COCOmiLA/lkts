<?php

use common\modules\abiturient\models\AddressData;
use yii\web\View;







$showRequiredSymbols = $addressData->showRequiredSymbols();

?>

<?php if (!$addressData->homeless) : ?>
    <div class="row">
        <div class="form-group col-6 <?= $showRequiredSymbols ? 'required' : '' ?>">
            <div class="row">
                <label class="col-4 col-form-label <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $addressData->getAttributeLabel('country_id') ?>
                </label>

                <div class="col-8">
                    <p class="form-control-plaintext">
                        <?php if ($addressData->country != null) {
                            echo $addressData->country->name;
                        } ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="form-group col-6 <?= $showRequiredSymbols ? 'required' : '' ?>">
            <div class="row">
                <label class="col-5 col-form-label <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $addressData->getAttributeLabel('street_id') ?>
                </label>

                <div class="col-7">
                    <?php if ($addressData->street != null) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->street->fullname; ?>
                        </p>
                    <?php elseif ($addressData->not_found) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->street_name; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-6 <?= $showRequiredSymbols ? 'required' : '' ?>">
            <div class="row">
                <label class="col-4 col-form-label <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $addressData->getAttributeLabel('region_id') ?>
                </label>

                <div class="col-8">
                    <?php if ($addressData->region != null) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->region->fullname; ?>
                        </p>
                    <?php elseif ($addressData->not_found) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->region_name; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-group col-6 <?= $showRequiredSymbols ? 'required' : '' ?>">
            <div class="row">
                <label class="col-5 col-form-label <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $addressData->getAttributeLabel('house_number') ?>
                </label>

                <div class="col-7">
                    <p class="form-control-plaintext">
                        <?= $addressData->house_number; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-6">
            <div class="row">
                <label class="col-4 col-form-label">
                    <?= $addressData->getAttributeLabel('area_id') ?>
                </label>

                <div class="col-8">
                    <?php if ($addressData->area != null) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->area->fullname; ?>
                        </p>
                    <?php elseif ($addressData->not_found) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->area_name; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-group col-6">
            <div class="row">
                <label class="col-5 col-form-label">
                    <?= $addressData->getAttributeLabel('housing_number') ?>
                </label>

                <div class="col-7">
                    <p class="form-control-plaintext">
                        <?= $addressData->housing_number; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-6">
            <div class="row">
                <label class="col-4 col-form-label">
                    <?= $addressData->getAttributeLabel('city_id') ?>
                </label>

                <div class="col-8">
                    <?php if ($addressData->city != null) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->city->fullname;  ?>
                        </p>
                    <?php elseif ($addressData->not_found) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->city_name; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-group col-6">
            <div class="row">
                <label class="col-5 col-form-label">
                    <?= $addressData->getAttributeLabel('flat_number') ?>
                </label>

                <div class="col-7">
                    <p class="form-control-plaintext">
                        <?= $addressData->flat_number; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-6">
            <div class="row">
                <label class="col-4 col-form-label">
                    <?= $addressData->getAttributeLabel('village_id') ?>
                </label>

                <div class="col-8">
                    <?php if ($addressData->village != null) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->village->fullname;  ?>
                        </p>
                    <?php elseif ($addressData->not_found) : ?>
                        <p class="form-control-plaintext">
                            <?= $addressData->town_name; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-group col-6 <?= $showRequiredSymbols ? 'required' : '' ?>">
            <div class="row">
                <label class="col-5 col-form-label <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $addressData->getAttributeLabel('postal_index') ?>
                </label>

                <div class="col-7">
                    <p class="form-control-plaintext">
                        <?= $addressData->postal_index; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="row">
        <div class="form-group col-12 <?= $showRequiredSymbols ? 'required' : '' ?>">
            <p class="form-control-plaintext">
                <?= $addressData->getAttributeLabel('homeless') ?>
            </p>
        </div>
    </div>
<?php endif;