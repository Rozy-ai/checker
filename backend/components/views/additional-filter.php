<?php

use common\models\Source;

/**
 * @var Source $source
 * @var string $f_asin_multiple 
 * @var string|integer $f_new
 * @var string|integer $f_favor
 * @var array $right_filters
 * @var array $additional_filter_values
 */

use yii\helpers\Html;

$filtersOpened = !empty($f_asin_multiple) || !empty($f_asin_type) || (int)$f_new || (int)$f_favor
    || !empty(array_filter($additional_filter_values, fn ($v) => isset($v)));
?>

<div class="form-row mt-3 mb-3">
    <div class="col col-12 d-flex justify-content-end flex-wrap">
        <a сlass="btn btn-link d-flex align-items-center" href="#" id="additional_filter_link">
            Расширенный фильтр
            <i class="bi bi-caret-down-fill"></i>
        </a>
        <div class="w-100 additional-filters" class="pt-2" <?php echo !$filtersOpened ? 'style="display: none;"' : ''; ?>>
            <div class="row mb-4">
                <div class="col col-auto">
                    <div class="d-flex align-items-center">
                        <?= Html::checkbox('f_new', !!(int)$f_new, ['id' => 'id_f_new', 'class' => 'form-control h-auto', 'label' => null]); ?>
                        <?= Html::label('New', 'id_f_new', ['class' => 'mb-0 ml-2']); ?>
                    </div>
                </div>
                <div class="col col-auto">
                    <div class="d-flex align-items-center">
                        <?= Html::checkbox('f_favor', !!(int)$f_favor, ['id' => 'id_f_favor', 'class' => 'form-control h-auto', 'label' => null]); ?>
                        <?= Html::label('Favorites', 'id_f_favor', ['class' => 'mb-0 ml-2']); ?>
                    </div>
                </div>
            </div>
            <div class="row flex-lg-nowrap align-items-start">
                <div class="d-flex flex-wrap mb-4 col col-12 col-lg-6 additional-filters__left-products">
                    <div class="mb-2 w-100"><b>Товары Amazon</b></div>
                    <div class="row">
                        <?php foreach ($left_filters as $lf) { ?>
                            <?php
                            $id = preg_replace("/\W+/", "", preg_replace("/\s+/", "_", $lf['key']));
                            $isRange = !!$lf['range'];
                            ?>
                            <div class="col col-auto mb-2">
                                <?= Html::label($lf['label'], $id . '_0'); ?>
                                <div class="d-flex">
                                    <?php if ($lf['type'] !== 'sort' && empty($lf['values'])) { ?>
                                        <div class="col col-auto mr-2 pl-0 pr-0">
                                            <?= Html::input(
                                                $lf['type'],
                                                $lf['name'] . ($isRange ? '_0' : ''),
                                                $additional_filter_values[$lf['name'] . ($isRange ? '_0' : '')] ?: "",
                                                [
                                                    'class' => 'form-control',
                                                    'id' => $id . '_0',
                                                    'placeholder' => $lf['type'] === 'number' ? 'от' : '',
                                                    'style' => 'max-width: ' . ($lf['type'] === 'number' ? '80px;' : '150px;'),
                                                ]
                                            ) ?>
                                        </div>
                                        <?php if ($lf['range']) { ?>
                                            <div class="col col-auto pl-0 pr-0">
                                                <?= Html::input(
                                                    $lf['type'],
                                                    $lf['name'] . ($isRange ? '_1' : ''),
                                                    $additional_filter_values[$lf['name'] . ($isRange ? '_1' : '')] ?: "",
                                                    [
                                                        'class' => 'form-control',
                                                        'id' => $id . '_1',
                                                        'placeholder' => $lf['type'] === 'number' ? 'до' : '',
                                                        'style' => 'max-width: ' . ($lf['type'] === 'number' ? '80px;' : '150px;'),
                                                    ]
                                                ) ?>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php
                                        $values = [];
                                        foreach ($lf['values'] as $key => $v) {
                                            if (!is_array($v)) {
                                                $values[$key] = $v;
                                            } else {
                                                $values[$key] = $v['label'] . " " . ($v['order'] === SORT_DESC ? "↓" : "↑");
                                            }
                                        }
                                        ?>
                                        <?= Html::dropDownList(
                                            $lf['name'],
                                            $additional_filter_values[$lf['name']],
                                            array_merge([null => 'Выбрать'], $values),
                                            [
                                                'class' => 'form-control',
                                                'id' => $lf['name'] . '_0',
                                                'style' => 'max-width: 150px;',
                                            ]
                                        ); ?>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col col-12 col-md-6 d-flex">
                            <?= Html::textarea(
                                'f_asin_multiple',
                                $f_asin_multiple ?: "",
                                [
                                    'class' => 'form-control',
                                    'placeholder' => 'ASIN (multiple)',
                                    'id' => 'id_f_asin_multiple'
                                ]
                            ) ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap mb-4 col col-12 col-lg-6 additional-filters__right-products">
                    <div class="mb-2 w-100"><b>Товары <?= $source->name ?></b></div>
                    <div class="row">
                        <?php foreach ($right_filters as $rf) { ?>
                            <?php
                            $id = preg_replace("/\W+/", "", preg_replace("/\s+/", "_", $rf['name']));
                            $isRange = !!$rf['range'];
                            ?>
                            <div class="col col-auto mb-2">
                                <?= Html::label($rf['label'], $id . '_0'); ?>
                                <div class="d-flex">
                                    <?php if ($rf['type'] !== 'sort' && empty($rf['values'])) { ?>
                                        <div class="col col-auto mr-2 pl-0 pr-0">
                                            <?= Html::input(
                                                $rf['type'],
                                                $rf['name'] . ($isRange ? '_0' : ''),
                                                $additional_filter_values[$rf['name'] . ($isRange ? '_0' : '')] ?: "",
                                                [
                                                    'class' => 'form-control',
                                                    'id' => $id . '_0',
                                                    'placeholder' => $rf['type'] === 'number' ? 'от' : '',
                                                    'style' => 'max-width: ' . ($rf['type'] === 'number' ? '80px;' : '150px;'),
                                                ]
                                            ) ?>
                                        </div>
                                        <?php if ($rf['range']) { ?>
                                            <div class="col col-auto pl-0 pr-0">
                                                <?= Html::input(
                                                    $rf['type'],
                                                    $rf['name'] . ($isRange ? '_1' : ''),
                                                    $additional_filter_values[$rf['name'] . ($isRange ? '_1' : '')] ?: "",
                                                    [
                                                        'class' => 'form-control',
                                                        'id' => $id . '_1',
                                                        'placeholder' => $rf['type'] === 'number' ? 'до' : '',
                                                        'style' => 'max-width: ' . ($rf['type'] === 'number' ? '80px;' : '150px;'),
                                                    ]
                                                ) ?>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php
                                        $values = [];
                                        foreach ($rf['values'] as $key => $v) {
                                            if (!is_array($v)) {
                                                $values[$key] = $v;
                                            } else {
                                                $values[$key] = $v['label'] . " " . ($v['order'] === SORT_DESC ? "↓" : "↑");
                                            }
                                        }
                                        ?>
                                        <?= Html::dropDownList(
                                            $rf['name'],
                                            $additional_filter_values[$rf['name']],
                                            array_merge([null => 'Выбрать'], $values),
                                            [
                                                'class' => 'form-control',
                                                'id' => $rf['name'] . '_0',
                                                'style' => 'max-width: 150px;',
                                            ]
                                        ); ?>
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