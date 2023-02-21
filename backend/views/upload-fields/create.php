<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\UploadFields $model */

$this->title = 'Create Upload Fields';
$this->params['breadcrumbs'][] = ['label' => 'Upload Fields', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="upload-fields-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
