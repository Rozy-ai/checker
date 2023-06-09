<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\helpers\Html;

NavBar::begin([
  'brandLabel' => Yii::$app->name,
  'brandUrl' => Yii::$app->homeUrl,
  'options' => [
    'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top hover-drop',
  ],
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav'],
    'activateParents' => true,
    'items' => [
        [
            'label' => 'Home',
            'url' => ['/site/index']
        ],
        [
            'label' => 'Products',
            'url' => ['/product/index'],
            'items' => $sourceItems,
            'visible' => !Yii::$app->getUser()->getIsGuest()
        ],
        [
            'label' => 'Messages',
            'url' => ['/message/index'],
            'visible' => Yii::$app->user->can('admin')
        ],
        [
            'label' => 'Billing',
            'url' => ['/billing/index'],
            'visible' => Yii::$app->user->can('admin')
        ],
        [
            'label' => 'Users',
            'url' => '#',
            'visible' => Yii::$app->user->can('admin'),
            'items' => [
                [
                    'label' => 'External users',
                    'url' => ['/external-users/index'],
                ],
                [
                    'label' => 'Admins',
                    'url' => ['/user/index'],
                ],
            ],
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
            'label' => 'Logout (' . Yii::$app->getUser()->getName() . ')',
            'url' => ['auth/logout'],
            'linkOptions' => [
                'data-method' => 'post',
                'class' => 'logout-link'
            ],
            'visible' => !Yii::$app->getUser()->getIsGuest()
        ],
        [
            'label' => 'Login',
            'url' => ['auth/login'],
            'visible' => Yii::$app->getUser()->getIsGuest()
        ]
    ],
]);
NavBar::end();
