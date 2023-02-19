<?php

use backend\assets\IconsAsset;
use backend\assets\ProductIndexAsset;
use backend\controllers\StatsController;
use common\models\HiddenItems;
use common\models\ProfileTypeSetting;
use common\models\Source;
use yii\helpers\Html;

/**
 * @var string $f_source
 * @var string $f_profile
 * @var string $f_count_products_on_page
 * @var int $f_number_page_current
 * @var string $f_asin
 * @var string $f_title
 * @var string $f_status
 * @var string $f_username
 * @var string $f_comparison_status
 * @var string $f_sort
 * @var string $f_detail_view
 * @var string $f_categories_root
 * @var string $f_batch_mode
 * @var string $f_hide_mode
 * @var bool $f_no_compare
 *
 * @var string $user_login
 *
 * @var array $list_source
 * @var array $list_profiles
 * @var array $list_categories_root
 * @var array $list_username
 * @var array $list_comparison_statuses
 * @var array $list
 *
 * @var int $count_products_all
 * @var int $count_products_right
 * @var string $default_price_name
 * @var int $count_pages
 * @var Source $source
 * @var array $local_import_stat
 * @var        $last_update
 */

$this->title = $source->name . " | " . Yii::t('site', 'Products');
$this->params['breadcrumbs'][] = Yii::t('site', 'Products');

$local_import_stat = null;

$last_local_import_txt = StatsController::getStatsLastLocalImportMessage();

IconsAsset::register($this);
ProductIndexAsset::register($this);
?>

<div class="[ PRODUCTS ]">
    <div class="position-1">
        <div class="[ FILTER-ITEMS ] products__filter-items">
            <!--<form method="get" action="change-filters" id="id_products__filter-form">-->
            <div class="form-row js-title-and-source_selector">
                <div class="form-group _col-sm-2" style="width: 128px">
                    <div class="titleName"
                         style="margin-top: 5px;"><?= Html::encode(Yii::t('site', 'Products')) ?></div>
                </div>

                <div class="form-group _col-sm-2" style="width: 128px">
                    <select name="f_source" id="id_f_source" class="form-control">
                        <?php
                        if ($list_source) {
                            foreach ($list_source as $item) {
                                $selected = ($item->id === $f_source) ? 'selected' : '';
                                echo "<option value=$item->id $selected>$item->name</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group _col-sm-2 filter-items__last-update">
                    last <?php echo Html::a('update','/import/local_import?source_id='.$f_source, ['title' => $last_local_import_txt]) ?>:<br>
                    <?= $last_update->created ?? 'Нет данных' ?>
                </div>
            </div>

            <div class="form-row form-inline" style="width: 100%;">
                <?php
                $profile_path = $f_profile ?? 'Все';
                if ($profile_path === '{{all}}')
                    $profile_path = 'Все';
                ?>
                <div class="cnt-items col-sm-6" id="id_block_count">Показаны
                    записи <?= min($f_count_products_on_page, $count_products_all) ?> из <?= $count_products_all; ?>
                    (<?= $count_products_right ?>) Источник <?= $source->name ?> / <?= $profile_path ?></div>

                <div class="cnt-items col-sm-6" style="    text-align: right; padding-right: 0;">
                    <span>Показывать по:&nbsp;&nbsp;</span>
                    <select name="f_count_products_on_page" id="id_f_count_products_on_page" class="form-control ">
                        <?php foreach ($list_count_products_on_page as $pnl): ?>
                            <option value="<?= $pnl ?>" <?= ((int)$f_count_products_on_page === $pnl) ? 'selected' : '' ?>><?= $pnl ?></option>
                        <?php endforeach; ?>
                        <option value="ALL" <?= ($f_count_products_on_page === 'ALL') ? 'selected' : '' ?>>ВСЕ</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group _col-sm-2" style="width: 128px">
                    <input value="<?= $f_asin ?>" type="text" class="form-control" placeholder="ASIN" id="id_f_asin"
                           name="f_asin">
                </div>


                <div class="form-group _col-sm-3" style="width: 200px">
                    <select name="f_categories_root" id="id_f_categories_root" class="form-control">
                        <option value="">Categories:Root</option>
                        <?php foreach ($list_categories_root as $where_3_item => $cnt): ?>
                            <option value="<?= $where_3_item ?>"
                                <?= ($f_categories_root == $where_3_item) ? 'selected' : '' ?>>
                                <?= $where_3_item ?> (<?= $cnt ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group _col-sm-2">
                    <input value="<?= $f_title ?>"
                           type="text" class="form-control" placeholder="Title" id="id_f_title" name="f_title">
                </div>

                <div class="form-group _col-sm-2">
                    <select name="f_profile_type" id="id_f_profile_type" class="form-control">
                        <option value="" <?= (empty($f_profile_type)) ? 'selected' : '' ?>>
                            Profile
                        </option>
                        <option value="<?= ProfileTypeSetting::GENERAL ?>"
                            <?= ($f_profile_type == ProfileTypeSetting::GENERAL) ? 'selected' : '' ?>
                        ><?= ProfileTypeSetting::TYPE_WITH_LABELS[ProfileTypeSetting::GENERAL] ?>
                        </option>
                        <option value="<?= ProfileTypeSetting::FREE ?>"
                            <?= ($f_profile_type == ProfileTypeSetting::FREE) ? 'selected' : '' ?>
                        ><?= ProfileTypeSetting::TYPE_WITH_LABELS[ProfileTypeSetting::FREE] ?>
                        </option>
                        <option value="<?= $user_login ?>"
                            <?= ($f_profile_type == $user_login) ? 'selected' : '' ?>
                        ><?= $user_login ?>
                        </option>
                    </select>
                </div>


                <div class="form-group _col-sm-2">
                    <select name="f_status" id="id_f_status" class="form-control">
                        <option value="">Status</option>
                        <option value="<?= HiddenItems::STATUS_CHECK ?>"
                            <?= ($f_status == HiddenItems::STATUS_CHECK) ? 'selected' : ''; ?>
                        ><?= HiddenItems::getTitleStatuses(HiddenItems::STATUS_CHECK) ?>
                        </option>
                        <option value="<?= HiddenItems::STATUS_ACCEPT ?>"
                            <?= ($f_status == HiddenItems::STATUS_ACCEPT) ? 'selected' : ''; ?>
                        ><?= HiddenItems::getTitleStatuses(HiddenItems::STATUS_ACCEPT) ?>
                        </option>
                        <option value="<?= HiddenItems::STATUS_NO_ACCEPT ?>"
                            <?= ($f_status == HiddenItems::STATUS_NO_ACCEPT) ? 'selected' : ''; ?>
                        ><?= HiddenItems::getTitleStatuses(HiddenItems::STATUS_NO_ACCEPT) ?>
                        </option>
                    </select>
                </div>

                <div class="form-group _col-sm-3">
                    <select name="f_comparison_status" id="id_f_comparison_status" class="form-control">
                        <?php
                        foreach ($list_comparison_statuses as $key => $data) {
                            $name = $data['name'];
                            $count = $data['count'];
                            $is_active = ($key == $f_comparison_status) ? 'selected' : '';
                            $st = "<option value=$key $is_active>$name ($count)</option>";
                            echo $st;
                        }
                        ?>
                        <option value="">All</option>
                    </select>
                </div>

                <div class="form-group _col-sm-3">
                    <select name="f_sort" id="id_f_sort" class="form-control">
                        <option value="">Сортировать по</option>
                        <option value="created_ASC" <?= ($f_sort === 'created_ASC') ? 'selected' : '' ?>>
                            дате добавления ↓
                        </option>
                        <option value="created_DESC" <?= ($f_sort === 'created_DESC') ? 'selected' : '' ?>>
                            дате добавления ↑
                        </option>
                        <option value="updated_ASC" <?= ($f_sort === 'updated_ASC') ? 'selected' : '' ?>>
                            дате обновления ↓
                        </option>
                        <option value="updated_DESC" <?= ($f_sort === 'updated_DESC') ? 'selected' : '' ?>>
                            дате обновления ↑
                        </option>
                    </select>
                </div>

                <?php if ($f_detail_view): ?>
                    <div class="form-group _col-sm-3">
                        <select name="f_detail_view" id="id_f_detail_view" class="form-control ">
                            <option value="0" <?= ($f_detail_view === '0') ? 'selected' : '' ?>>Кратко</option>
                            <option value="1" <?= ($f_detail_view === '1') ? 'selected' : '' ?>>Подробно</option>
                            <option value="2" <?= ($f_detail_view === '2') ? 'selected' : '' ?>>Кратко со списком
                            </option>
                            <option value="3" <?= ($f_detail_view === '3') ? 'selected' : '' ?>>Подробно со списком
                            </option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive__" id="id_table_container">
            <?=
            $this->render('index_table', [
                'list' => $list,
                'local_import_stat' => $local_import_stat,
                'f_comparison_status' => $f_comparison_status,
                'f_profile' => $f_profile,
                'f_no_compare' => $f_no_compare,
                'f_detail_view' => $f_detail_view,
                'source' => $source,
                'default_price_name' => $default_price_name,
            ]);
            ?>
        </div><!-- table-responsive -->

        <div class="row">
            <div class="col">
                <div class="featured-items">Показаны записи <?= min($f_count_products_on_page, $count_products_all) ?>
                    из <?= $count_products_all ?>.
                </div>

                <?php
                $e_comparison = isset($f_comparison_status) && $f_comparison_status ? strtolower($f_comparison_status) : 'match';
                $e_profile = isset($f_profile) && $f_profile && $f_profile !== 'Все' ? $f_profile : '{{all}} ';
                ?>

            </div>
        </div>

        <?php if ($f_count_products_on_page !== 'ALL'): ?>
            <div class="products__pager">
                <nav aria-label="Page navigation example ">
                    <ul id="id_paginator" class="pagination justify-content-center">
                        <?php
                        echo $this->context->indexPresenter->getHTMLPaginator($f_number_page_current, $count_pages);
                        ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </div>