<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\ExternalUserProfile $model */

$this->title = Yii::t('app', 'Create External User Profile');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'External User Profiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-user-profile-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
