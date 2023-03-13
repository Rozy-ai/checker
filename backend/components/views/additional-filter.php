<?php

/**
 * @var string $f_asin_multiple 
 * @var string|integer $f_new
 * @var string|integer $f_favor
 * @var array $right_filters
 * @var array $additional_filter_values
 */
$filtersOpened = !empty($f_asin_multiple) || (int)$f_new || (int)$f_favor
    || !empty(array_filter($additional_filter_values, fn ($v) => !empty($v)));
?>

<div class="form-row mt-3 mb-3">
    <div class="col col-12 d-flex justify-content-end flex-wrap">
        <a сlass="btn btn-link d-flex align-items-center" href="#" id="additional_filter_link">
            Расширенный фильтр
            <i class="bi bi-caret-down-fill"></i>
        </a>
        <div class="w-100 additional-filters" class="pt-2" <?php echo !$filtersOpened ? 'style="display: none;"' : ''; ?>>
            <div class="row mb-4">
                <div class="col col-12 col-md-4 col-lg-3">
                    <textarea type="text" class="form-control" placeholder="ASIN (multiple)" id="id_f_asin_multiple" name="f_asin_multiple" rows="2"><?php echo $f_asin_multiple ?: ""; ?></textarea>
                </div>
                <div class="col col-auto">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control h-auto" id="id_f_new" name="f_new" <?php echo (int)$f_new ? ' checked' : ''; ?> />
                        <label for="id_f_new" class="mb-0 ml-2">New</label>
                    </div>
                </div>
                <div class="col col-auto">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="form-control h-auto" id="id_f_favor" name="f_favor" <?php echo (int)$f_favor ? ' checked' : ''; ?> />
                        <label for="id_f_new" class="mb-0 ml-2">Favorites</label>
                    </div>
                </div>
            </div>
            <div class="row flex-lg-nowrap">
                <div class="d-flex flex-wrap mb-4 col col-12 col-lg-6 additional-filters__left-products">
                    <div class="mb-2 w-100"><b>Товары слева</b></div>
                    <div class="row">
                        <?php foreach ($left_filters as $key => $lf) { ?>
                            <?php
                            $id = preg_replace("/\W+/", "", preg_replace("/\s+/", "_", $key));
                            $isRange = !!$lf['range'];
                            ?>
                            <div class="col col-auto mb-2">
                                <label for="id_<?= $id ?>_0"><?= $lf['label'] ?></label>
                                <div class="d-flex">
                                    <div class="col col-auto mr-2 pl-0 pr-0">
                                        <input type="text" class="form-control" name="<?= $lf['name'] ?><?= $isRange ? '_0' : ''; ?>" id="id_<?= $id ?>_0" style="max-width: 80px;" placeholder="от" value="<?= $additional_filter_values[$lf['name'] . ($isRange ? '_0' : '')] ?: "" ?>" />
                                    </div>
                                    <?php if ($rf['range']) { ?>
                                        <div class="col col-auto pl-0 pr-0">
                                            <input type="text" class="form-control" name="<?= $lf['name'] ?><?= $isRange ? '_1' : ''; ?>" id="id_<?= $id ?>_1" style="max-width: 80px;" placeholder="до" value="<?= $additional_filter_values[$lf['name'] . ($isRange ? '_1' : '')] ?: "" ?>" />
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="d-flex flex-wrap mb-4 col col-12 col-lg-6 additional-filters__right-products">
                    <div class="mb-2 w-100"><b>Товары справа</b></div>
                    <div class="row">
                        <?php foreach ($right_filters as $key => $rf) { ?>
                            <?php
                            $id = preg_replace("/\W+/", "", preg_replace("/\s+/", "_", $key));
                            $isRange = !!$rf['range'];
                            ?>
                            <div class="col col-auto mb-2">
                                <label for="id_<?= $id ?>_0"><?= $rf['label'] ?></label>
                                <div class="d-flex">
                                    <div class="col col-auto mr-2 pl-0 pr-0">
                                        <input type="text" class="form-control" name="<?= $rf['name'] ?><?= $isRange ? '_0' : ''; ?>" id="id_<?= $id ?>_0" style="max-width: 80px;" placeholder="от" value="<?= $additional_filter_values[$rf['name'] . ($isRange ? '_0' : '')] ?: "" ?>" />
                                    </div>
                                    <?php if ($rf['range']) { ?>
                                        <div class="col col-auto pl-0 pr-0">
                                            <input type="text" class="form-control" name="<?= $rf['name'] ?><?= $isRange ? '_1' : ''; ?>" id="id_<?= $id ?>_1" style="max-width: 80px;" placeholder="до" value="<?= $additional_filter_values[$rf['name'] . ($isRange ? '_1' : '')] ?: "" ?>" />
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>