<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $model_2 backend\models\User */
/* @var $model_2_source_list_out backend\models\User */
/* @var $model_2_res_user_selected backend\models\User */

$this->title = Yii::t('site', 'Create User');
$this->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'model_2' => $model_2,
        'model_2_res_user_selected' => $model_2_res_user_selected,
        'model_2_source_list_out' => $model_2_source_list_out,
    ]) ?>

</div>

