<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Billing $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Billings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="billing-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'user_id',
                'value' => $model->user ? $model->user->login : null,
            ],
            [
                'attribute' => 'status',
                'value' => $model->getStatusText()
            ],
            'sum:currency',
            [
                'attribute' => 'admin_id',
                'value' => $model->admin ? $model->admin->username : null,
            ],
            'date:datetime',
            'description:ntext',
        ],
    ]) ?>

</div>
