<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\ExternalUser $model */

$this->title = 'Create External User';
$this->params['breadcrumbs'][] = ['label' => 'External Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="external-user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
