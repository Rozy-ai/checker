<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var backend\models\Billing $model */

$this->title = 'Create Billing';
$this->params['breadcrumbs'][] = ['label' => 'Billings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="billing-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
