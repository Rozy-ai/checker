<?php

/* @var $this \yii\web\View */

/* @var $content string */

use backend\assets\ProductsAsset;
use common\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;

ProductsAsset::register($this);
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
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <header>

        <?php
        echo \backend\components\NavBarWidget::widget([]);

        if (0):
          NavBar::begin([
              'brandLabel' => Yii::$app->name,
              'brandUrl' => Yii::$app->homeUrl,
              'options' => [
                  'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
              ],
          ]);
          $menuItems = [
              ['label' => 'Home', 'url' => ['/site/index']],
          ];
          if (Yii::$app->user->isGuest) {
              $menuItems[] = ['label' => 'Login', 'url' => ['/auth/login']];
          } else {
              $menuItems[] = ['label' => 'Products', 'url' => ['/product/index']];
              if (Yii::$app->user->can('admin')) {
                  $menuItems[] = ['label' => 'Messages', 'url' => ['/message/index']];
                  $menuItems[] = ['label' => 'Users', 'url' => ['/user/index']];
                  $menuItems[] = ['label' => 'Stats', 'url' => ['/stats/index']];
              }
              $menuItems[] = '<li>'
                  . Html::beginForm(['/auth/logout'], 'post', ['class' => 'form-inline'])
                  . Html::submitButton(
                      'Logout (' . Yii::$app->user->identity->username . ')',
                      ['class' => 'btn btn-link logout']
                  )
                  . Html::endForm()
                  . '</li>';
          }
          echo Nav::widget([
              'options' => ['class' => 'navbar-nav'],
              'items' => $menuItems,
          ]);
          NavBar::end();
          endif;
        ?>

    </header>

    <section style="width: 100%;    padding-right: 30px;box-shadow: 0 0px 6px 0px #00000047;"
      class=" [ FIXED-SLIDER ] navbar__fixed-slider navbar navbar-expand-md fixed-top -hidden">
      <div style="padding: 0" class="p_index  fixed-slider__container position-2">

      </div>
    </section>

    <section class="home">
      <div class="_container" style="padding: 0 17px;">
      <div class="row">
      <div class="col">
        <div class="navigation">

          <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'tag' => 'ul',
            'itemTemplate' => "<li>{link}</li>\n",
            'options' => ['class' => 'link'],
          ]) ?>

            <?php if (!empty($this->params['prev']) || !empty($this->params['next'])): ?>
              <ul class="pageLink">
                <li><?= Html::a('', $this->params['prev'] ? ['product/view', 'id' => $this->params['prev']->id] : '#', ['class' => $this->params['prev'] ? 'prev' : '']) ?></li>
                <li class="num"><?= Html::encode($this->title) ?></li>
                <li><?= Html::a('', $this->params['next'] ? ['product/view', 'id' => $this->params['next']->id] : '#', ['class' => $this->params['next'] ? 'next' : '']) ?></a></li>
              </ul>
            <?php endif; ?>

        </div>
        </div>
        <? if ($this->params['breadtail']) : ?>
        <div class="col-auto"><?= $this->params['breadtail'] ?></div>
        <? endif ?>
        </div>

        <?= Alert::widget() ?>

        <?= $content ?>
      </div>
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
