<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\helpers\Html;

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
            'url' => ['/product/index'],
            'visible' => !Yii::$app->getUser()->getIsGuest()
        ],
        [
            'label' => 'Messages',
            'url' => ['/message/index'],
            'visible' => Yii::$app->user->can('admin')
        ],
        [
            'label' => 'Users',
            'url' => ['/user/index'],
            'visible' => Yii::$app->user->can('admin')
        ],
        [
            'label' => 'Stats',
            'url' => ['/stats/index'],
            'visible' => Yii::$app->user->can('admin')
        ],
        [
            'label' => 'Settings',
            'url' => ['/settings/index'],
            'visible' => Yii::$app->user->can('admin')
        ],
    ],
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto'],
    'items' => [
        [
            'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
            'url' => ['/site/logout'],
            'linkOptions' => [
                'data-method' => 'post',
                'class' => 'logout-link'
            ],
            'visible' => !Yii::$app->getUser()->getIsGuest()
        ],
        [
            'label' => 'Login',
            'url' => ['/site/login'],
            'visible' => Yii::$app->getUser()->getIsGuest()
        ]
    ],
]);
NavBar::end();
