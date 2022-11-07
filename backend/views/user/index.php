<?php

use common\models\User;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('site', 'Users');
$this->params['breadcrumbs'][] = $this->title;

$authManager = \Yii::$app->authManager;

\backend\assets\UserAsset::register($this);

?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('site', 'Create User'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

  <div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],


            [
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function (\backend\models\User $user) {
                    if ((int)$user->id === 1) {
                        return $user->username;
                    }

                    $link = '/user/update?id='.$user->id;
                    return Html::a($user->username, $link, ['data-pjax' => '0']);

                }

            ],

            'email',
            [
                'attribute' => 'status',
                'value' => function ($model) { return Yii::$app->fmtUserData->asStatus($model->status); },
                'filter' => Html::activeDropDownList($searchModel, 'status', User::getStatuses(),
                                                     ['prompt' => '', 'class' => 'form-control'])
            ],
            [
                'attribute' => 'created_at',
                'format' =>  ['date', 'HH:mm, dd.MM.Y'],
                'contentOptions' => ['class' => 'text-nowrap']
            ],
            [
                'attribute' => 'updated_at',
                'format' =>  ['date', 'HH:mm, dd.MM.Y'],
                'contentOptions' => ['class' => 'text-nowrap']
            ],

            [
                'attribute' => 'admin/user',
                'format' => 'raw',
                'value' => function ($itm) {
                    if ($itm->id === 1 || (int)\Yii::$app->getUser()->id === (int)$itm->id) return '';
                    $role = null;
                    $allRoles = \Yii::$app->authManager->getAssignments($itm->id);
                    if ($allRoles) {
                        $role = array_keys($allRoles)[0];
                    }
                    return Html::button( $role ?: '---',['class' => '-set_role btn btn-primary','data-uid' => $itm->id]);
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',

                'urlCreator' => function($action, $model){
                    if ($action === 'view'){
                        return '/user/login_as_user?id='.$model->id;
                        //return '/product/index?filter-items__show_n_on_page=10&filter-items__id=&filter-items__target-image=&filter-items__comparing-images=&filter-items__user='.$model->username.'&filter-items__comparisons=&filter-items__sort=&filter-items__right-item-show=0&page=1';
                    }

                },
                'visibleButtons' => [
                    'view' => function(\backend\models\User $user) {
                        // Нельзя войти под самим собой и под суперадмином
                        return (int)$user->id !== 1 && (int)$user->id !== (int)Yii::$app->getUser()->getId();
                    },
                    'update' => false,
                    'delete' => false,
                ]
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'urlCreator' => function($action, $model){
                    return '/user/delete?id='.$model->id;
                },
                'visibleButtons' => [
                    'view' => false,
                    'update' => false,
                    'delete' =>
                        function (\backend\models\User $model) {
                            // Нельзя удалить суперадмина и себя
                            if ((int)$model->id === 1 || Yii::$app->getUser()->getId() === $model->id) {
                                return false;
                            }
                            // Удалять может только суперадмин
                            return Yii::$app->getUser()->getId() === 1;
                        },
                ]
            ],
        ],
    ]); ?>
  </div>

    <?php Pjax::end(); ?>

</div>
