<?php

/* @var $this \yii\web\View */

/* @var $content string */

use backend\assets\ProductAsset;
use common\models\Comparison;
use common\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\web\Session;

ProductAsset::register($this);

$list_comparison_statuses = $this->params['list_comparison_statuses'] ?? '';
$active_comparison_status = $this->params['active_comparison_status']?? '';
$product = $this->params['product'];

?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>" class="h-100">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>

    <body class="d-flex flex-column h-100 p-page">

    <?php $this->beginBody() ?>

    <header>
      <?php echo \backend\components\NavBarWidget::widget([]); ?>
    </header>

    <section
      style="box-shadow: 0 0px 6px 0px #00000047; z-index: 1000000"
      class="[ FIXED-SLIDER ] navbar__fixed-slider navbar navbar-expand-md fixed-top -hidden">
      <div class="container fixed-slider__container position-2"></div>
    </section>

    <section class="container home [ PRODUCT-PAGE ] product-page">

        <div class="navigation" style="margin-bottom: 14px;">

          <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'tag' => 'ul',
            'itemTemplate' => "<li>{link}</li>\n",
            'options' => ['class' => 'link'],
          ]) ?>

          <?
          $list_comparison_statuses = [
            Comparison::STATUS_PRE_MATCH => 0,
            Comparison::STATUS_MATCH => 0,
            Comparison::STATUS_MISMATCH => 0,
            Comparison::STATUS_OTHER => 0,
          ];

          //foreach ($product->comparisons as $comparison):
          //  $list_comparison_statuses [$comparison->status]++;
          //endforeach;
          ?>

            <div class="[ PRODUCT-STATISTICS ]  product-page__product-statistics">

              <div class="product-page__product-statistics-part-2">
                <span class="product-page__product-statistics-title">Обработано:</span>
                <?
                $counted = $product->aggregated ? $product->aggregated->counted : 0;
                $ret = Html::tag('span',"{$counted}/" . count($product->addInfo),
                  ['class' => 'name product-list-item__processed']);
                //$ret .= '<br/>';
                echo $ret;
                ?>
              </div>

              <?php
                echo $ret = Html::tag(
                  'div',

                  "<span class='js-pre_match pre_match' data-text_brief='Yes?'>{$list_comparison_statuses[Comparison::STATUS_PRE_MATCH]}</span>"
                  ."<span class='js-match match' data-text_brief='Yes'>{$list_comparison_statuses[Comparison::STATUS_MATCH]}</span>"
                  ."<span class='js-mismatch mismatch' data-text_brief='No'>{$list_comparison_statuses[Comparison::STATUS_MISMATCH]}</span>"
                  ."<span class='js-other other' data-text_brief='Other'>{$list_comparison_statuses[Comparison::STATUS_OTHER]}</span>"
                  ."<span class='js-nocompare nocompare' data-text_brief='Nocompare'>".count(Comparison::get_no_compare_ids_for_item($product))."</span>"
                  ."<span data-text_brief='reset' class='js css -reset_filter_1234 -hidden'>показать все</span>",

                  ['class' => 'product-list-item__compare-statistics product-page__product-statistics-1234','style' => '']
                );
              ?>
              <span
                style="float: left;"
                class="js-reset-compare product-page__reset-compare -margin" data-p_id="<?=$product->id?>" data-source_id="<?=$this->params['source_id']?>">
                Отменить выбор
              </span>

            </div>

            <!-- LINE где id -->
            <div class="tbl [ id-layout ] LINE -hidden">

                <div class="td id-layout__id-and-arrows">

                    <ul class="pageLink">
                      <li>
                        <?//= Html::a('', $link, ['class' => $this->params['prev'] ? 'prev' : '']) ?>

                        <a
                          class="-hidden prev js__arrow"
                          data-direction="prev"
                        ></a>

                      </li>
                      <li class="num"><?= Html::encode($this->title) ?></li>
                      <li class="id-layout__right-arrow">

                        <a
                          class="-hidden next js__arrow"
                          data-direction="next"
                        ></a>

                        <?//= Html::a('', $this->params['next'] ? ['product/view', 'id' => $this->params['next']->id,'direction' => 'next', 'item_1__ignore_red' => $this->params['item_1__ignore_red']] : '#', ['class' => $this->params['next'] ? 'next' : '']) ?>


                        <div class="arrow__filter-comparison">
                          <select name="" id="arrow__filter-comparison">
                            <?php 
                                $filter_statuses = Comparison::get_filter_statuses();
                                foreach ($filter_statuses as $k_status => $status_data):?>
                                <option
                                    value="<?=$k_status?>"
                                    <?= isset($get_['comparisons'])?     $get_['comparisons'] == $k_status ? 'selected' : ''    : '' ?>
                                >11
                                </option>
                            <?php endforeach;?>
                          </select>
                        </div>

                        <? if (0): ?>
                        <div class="view-settings__amazon">
                          <input type="checkbox" id="view-settings__amazon"
                                 <?= ($this->params['item_1__ignore_red'])? 'checked' : '' ?>

                          >
                          <label class="form-check-label" for="view-settings__amazon">
                            пропускать ненайденные (Х)
                          </label>
                        </div>
                        <? endif; ?>

                      </li>
                    </ul>
                </div>


                <div class="td [ VIEW-SETTINGS ] id-layout__view-settings">

                  <? if (0): ?>
                  <div class="view-settings__on-of-right-items">
                    <input
                      <?//= ($this->params['item_2__show_all'])? 'checked' : '' ?>
                      <?= ( (int)(new Session())->get('item_2__show_all') === 1 )? 'checked' : '' ?>
                      type="checkbox" id="view-settings__on-of-right-items">
                    <label for="view-settings__on-of-right-items">
                      показать все
                    </label>
                  </div>
                  <? endif; ?>

                </div>

            </div>
          <div class="clear"></div>

        </div><!-- .navigation -->

        <?= Alert::widget() ?>

        <?= $content;
            // backend/controllers/ProductController.php::actionView()
            // backend/views/product/view.php
        ?>

    </section>

    <footer class="footer mt-auto py-3 text-muted">
        <div class="container">
            <p class="float-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
            <p class="float-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage();
