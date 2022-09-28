<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Message */

$this->title = Yii::t('site', 'Update Message #{id}', [
    'id' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Messages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => "Message #". $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('site', 'Update');
?>
<div class="message-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
