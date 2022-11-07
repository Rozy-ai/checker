<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\web\JsExpression;
use yii\jui\AutoComplete;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MessageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $user_list  */

$this->title = Yii::t('site', 'Messages');
$this->params['breadcrumbs'][] = $this->title;

\backend\assets\MessageAsset::register($this);
?>
<div class="message-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Html::beginForm(['message/batch'], 'post', []) ?>
  <p>
  <div class="form-inline">
    <?= Html::a(Yii::t('site', 'Create Message'), ['create'], ['class' => 'btn btn-success']) ?>
    <div class="input-group ml-2">
      <div class="input-group-prepend">

        <select name="username" id="" class="form-control">
          <option value="">выбрать пользователя</option>
          <? foreach ($user_list as $user):?>
            <option value="<?=$user->username?>"><?=$user->username?></option>
          <? endforeach;?>
        </select>

        <? if (0): ?>
        <?= AutoComplete::widget(['name' => 'username', 'clientOptions' => ['source' => new JsExpression("function(request, response) {
                                                $.getJSON('" . Url::to(['user/ajax']) . "', {
                                                    search: request.term
                                                }, response);
                                            }"),], 'options' => ['class' => 'form-control', 'placeholder' => Yii::t('site', 'Username')]]); ?>
        <? endif;?>

      </div>
      <div class="input-group-append">
        <?= Html::submitButton(Yii::t('site', 'Link'), ['name' => 'action', 'value' => 'link', 'class' => 'btn btn-primary']) ?>
      </div>
    </div>
    <?= Html::submitButton(Yii::t('site', 'Unlink'), ['name' => 'action', 'value' => 'unlink', 'class' => 'ml-2 btn btn-danger']) ?>
    <div>Для добавления пользователя к сообщению 1. выбрать из списка 2. отметить [&nbsp;&nbsp;] сообщение 3. нажать Link</div>
  </div>
  </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\CheckboxColumn'],

            [
                'attribute' => 'text',
                'format' => 'raw',
                'value' => function ($data) {
                    $html = Html::a($data->text, ['update', 'id' => $data->id], ['data-pjax' => '0']);
                    $html .= '<div style="color: #7d7d7d">' .$data->description.'</div>';
                    return $html;
                }
            ],

            //'text:ntext',

            [
                'attribute' => 'user',
                'format' => 'raw',
                'value' => function ($model) {
                    $links = [];
                    foreach ($model->users as $user)
                    {
                        $links [] = Html::a($user->username, ['message/user', 'user' => $user->id], ['data-pjax' => 0]);
                    }
                    return implode($links, ', ');
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => true,
                    'view' => false,
                    'update' => false
                ]

            ],
        ],
    ]); ?>
    <?= Html::endForm() ?>
    <?php Pjax::end(); ?>

</div>
