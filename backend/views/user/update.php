<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $model_2 backend\models\User */
/* @var $model_2_source_list_out backend\models\User */
/* @var $model_2_res_user_selected backend\models\User */

$this->title = Yii::t('site', 'Update User: {name}', [
    'name' => $model->username,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->model->id]];
$this->params['breadcrumbs'][] = Yii::t('site', 'Update');
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'model_2' => $model_2,
        'model_2_res_user_selected' => $model_2_res_user_selected,
        'model_2_source_list_out' => $model_2_source_list_out,
    ]) ?>

</div>
