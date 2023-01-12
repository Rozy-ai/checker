<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\ExternalUserProfileField $model */

$this->title = Yii::t('app', 'Create External User Profile Field');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'External User Profile Fields'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-user-profile-field-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
