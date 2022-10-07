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
$menuItems = [
  ['label' => 'Home', 'url' => ['/site/index']],
];
if (Yii::$app->user->isGuest) {
  $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
} else {
  //$menuItems[] = ['label' => 'Products', 'url' => ['/product/index?filter-items__source=1&filter-items__show_n_on_page=10&filter-items__id=&filter-items__target-image=&filter-items__comparing-images=&filter-items__user=&filter-items__comparisons=ALL&filter-items__sort=&filter-items__right-item-show=0&page=1']];
  $menuItems[] = ['label' => 'Products', 'url' => ['/product/index']];
  if (Yii::$app->user->can('admin')) {
    $menuItems[] = ['label' => 'Messages', 'url' => ['/message/index']];
    $menuItems[] = ['label' => 'Users', 'url' => ['/user/index']];
    $menuItems[] = ['label' => 'Stats', 'url' => ['/stats/index']];
    $menuItems[] = ['label' => 'Settings', 'url' => ['/settings/index']];
  }
  $menuItems[] = '<li>'
    . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
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

?>