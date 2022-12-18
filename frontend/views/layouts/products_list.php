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
        NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav'],
            'items' => [
                [
                    'label' => 'Home',
                    'url' => ['/site/index']
                ],
                [
                    'label' => 'Products',
                    'url' => ['/products']
                ],
                [
                    'label' => 'About',
                    'url' => ['/site/about']
                ],
                [
                    'label' => 'Contact',
                    'url' => ['/site/contact']
                ],
            ],
        ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav ml-auto'],
            'items' => [
                [
                    'label' => 'Signup',
                    'url' => ['/auth/register'],
                    'visible' => Yii::$app->getUser()->getIsGuest()
                ],
                [
                    'label' => 'Login',
                    'url' => ['/auth/login'],
                    'visible' => Yii::$app->getUser()->getIsGuest()
                ],
                [
                    'label' => Yii::$app->getUser()->getName(),
                    'url' => '#',
                    'visible' => !Yii::$app->getUser()->getIsGuest(),
                    'items' => [
                        [
                            'label' => 'Billing',
                            'url' => ['billing/index'],
                            'visible' => !Yii::$app->getUser()->getIsGuest()
                        ],
                        [
                            'label' => 'Logout',
                            'url' => ['/auth/logout'],
                            'linkOptions' => [
                                'data-method' => 'post'
                            ],
                            'visible' => !Yii::$app->getUser()->getIsGuest()
                        ]
                    ]
                ],
            ],
        ]);
        NavBar::end();
        ?>
    </header>

    <section style="width: 100%;    padding-right: 30px;box-shadow: 0 0px 6px 0px #00000047;"
             class=" [ FIXED-SLIDER ] navbar__fixed-slider navbar navbar-expand-md fixed-top -hidden">
        <div style="padding: 0" class="p_index  fixed-slider__container position-2">

        </div>
    </section>

    <section class="home">
        <div class="_container" style="padding: 0 17px;">
            <div class="navigation">

                <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    'tag' => 'ul',
                    'itemTemplate' => "<li>{link}</li>\n",
                    'options' => ['class' => 'link'],
                ]) ?>

                <? if (!empty($this->params['prev']) || !empty($this->params['next'])): ?>
                    <ul class="pageLink">
                        <li><?= Html::a('', $this->params['prev'] ? ['product/view', 'id' => $this->params['prev']->id] : '#', ['class' => $this->params['prev'] ? 'prev' : '']) ?></li>
                        <li class="num"><?= Html::encode($this->title) ?></li>
                        <li><?= Html::a('', $this->params['next'] ? ['product/view', 'id' => $this->params['next']->id] : '#', ['class' => $this->params['next'] ? 'next' : '']) ?></a></li>
                    </ul>
                <? endif; ?>

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
