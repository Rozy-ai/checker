<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\UploadFields $model */

$this->title = 'Update Upload Fields: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Upload Fields', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="upload-fields-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
