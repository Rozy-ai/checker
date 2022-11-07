<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\ExternalUser $model */

$this->title = 'Update External User: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'External Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="external-user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
