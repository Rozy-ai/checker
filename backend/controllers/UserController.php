<?php

namespace backend\controllers;

use backend\models\Source;
use common\models\User__source_access;
use common\models\UserEntity;
use Yii;
use common\models\User as CommonUser;
use backend\models\User;
use backend\models\UserForm;
use backend\models\UserSearch;
use backend\models\AuthAssignment;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        // 'delete' => ['POST'],
                    ],
                ],

                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['admin'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $items = $dataProvider->getModels();

        $queryAuth = AuthAssignment::find();
        $queryAuth->select('user_id')
                  ->andWhere(['item_name' => 'admin'])
                  ->andWhere(['IN', 'user_id', ArrayHelper::getColumn($items, 'id')]);


        return $this->render('index', [
            'adminUsers' => $queryAuth->column(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
  public function actionCreate(){
    $model = new CommonUser();
    $form = new UserForm(['model' => $model, 'scenario' => 'create']);

    if ($this->request->isPost) {
      if ($form->load(Yii::$app->request->post()) && $form->save()) {

        $id = $form->getAttributes()['model']->id;
        $user_source_ids = $this->request->post('User__source_access')['source_id'];

        if ($user_source_ids)
        foreach ($user_source_ids as $us_id){
          $usa = new User__source_access();
          $usa->source_id = $us_id;
          $usa->user_id = $id;
          $usa->save();
        }

        return $this->redirect('/user/');
        //return $this->redirect(['view', 'id' => $model->id]);
      }
    }

    /***************/
    $model_2 = new User__source_access();
    $model_2_source_list = Source::get_sources();
    $model_2_source_list_out = [];
    foreach ($model_2_source_list as $s_list) {
      $model_2_source_list_out[$s_list->id] = $s_list->name;
    }

//    $model_2_res_user = User__source_access::find()->all();
//    $model_2_res_user_selected = [];
//    foreach ($model_2_res_user as $re) {
//      $model_2_res_user_selected[$re->source_id] = Source::get_source($re->source_id)['source_name'];
//    }

    /////////////////

    return $this->render('create', [
      'model' => $form,
      'model_2' => $model_2,
      'model_2_res_user_selected' => [],
      'model_2_source_list_out' => $model_2_source_list_out,
      ]
    );
  }


  /**
   * Updates an existing User model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param int $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id, \yii\web\User $user){
    //echo '<pre>'.PHP_EOL;
    //$user = User::find()->where(['id'=>$id])->one();

    //User::find()->where(['id'=>$id])->limit(1)->one()->user__source_access;

    $model = $this->findModel($id);

      if ((int)$user->getId() !== 1 && (int)$model->id !== (int)$user->getId()) {
          throw new ForbiddenHttpException();
      }

    $form = new UserForm(['model' => $model, 'scenario' => 'update']);
    if ($this->request->isPost) {
      $allowSave = true;
      $post = $this->request->post();
      if (\Yii::$app->authManager->getAssignment('admin', $model->id) !== null) {
        $data = $post [$form->formName()];
        if ($data['status'] != CommonUser::STATUS_ACTIVE) {
          $allowSave = false;
          $session = Yii::$app->session;
          $session->setFlash('danger', "Admin cannot be deleted and inactivated.");
        }
      }

      if ($allowSave && $form->load($post) && $form->save()) {

        $id = $form->getAttributes()['model']->id;
        $user_source_ids = $this->request->post('User__source_access')['source_id'];

        User__source_access::deleteAll(['user_id' => $id]);

        if ($user_source_ids)
        foreach ($user_source_ids as $us_id){
          $usa = new User__source_access();
          $usa->source_id = $us_id;
          $usa->user_id = $id;
          $usa->save();
        }

        return $this->redirect('/user/');
        //return $this->redirect(['view', 'id' => $model->id]);
      }
    }

    $model_2 = new User__source_access(['user_id' => $id]);
    $model_2_source_list = Source::get_sources();
    $model_2_source_list_out = [];
    foreach ($model_2_source_list as $s_list){
      $model_2_source_list_out[$s_list->id] = $s_list->name;
    }

    $model_2_res_user = User__source_access::find()->where(['user_id' => $id])->all();
    $model_2_res_user_selected = [];
    foreach ($model_2_res_user as $re){
      $model_2_res_user_selected[$re->source_id] = Source::get_source($re->source_id)['source_name'];
    }

    return $this->render('update', [
      'model' => $form,
      'model_2' => $model_2,
      'model_2_res_user_selected' => $model_2_res_user_selected,
      'model_2_source_list_out' => $model_2_source_list_out,
      ]
    );
  }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (\Yii::$app->authManager->getAssignment('admin', $model->id) === null)
        {
            $model->status = CommonUser::STATUS_DELETED;
            $model->save();
        }
        else {
            $session = Yii::$app->session;
            $session->setFlash('danger', "Admin cannot be deleted and inactivated.");
        }
        return $this->redirect(['index']);
    }


    public function actionAjax(): \yii\web\Response
    {
        $model = User::find();
        $model->where(['status' => UserEntity::STATUS_ACTIVE]);
        $model->andFilterWhere(['like', 'username', Yii::$app->request->get('search')]);
        $items = $model->select('username')->limit(10)->asArray()->column();

        return $this->asJson($items);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $query = CommonUser::find();
        $query->where(['id' => $id])
              ->andWhere(['<>', 'status', CommonUser::STATUS_DELETED]);
        if (($model = $query->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('site', 'The requested page does not exist.'));
    }


    public function actionSet_role(){
      $uid = $this->request->post('uid');

      // if ( (int)$this->user->getIdentity()->id !== (int)$uid ) return false;
      // if (! User::isAdmin($logined_user = $this->user->getIdentity()->id) ) return false;

      // проверить есть ли и установить  auth_assignment
      $u = AuthAssignment::findOne(['user_id' => $uid]);

      $roles = ['user','compare-products','compare-previous-and-untested-products','admin'];

      if ($u->item_name){
        $k = array_search($u->item_name,$roles) + 1;
        if (!isset($roles[$k])) $role = 'user';
        else $role = $roles[$k];
      }else{
        $role = 'user';
      }
      $u->delete();

      $u = new AuthAssignment();
      $u->user_id = $uid;
      $u->item_name = $role;
      $u->created_at = time();
      $u->save();



      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return [
        'res' => 'ok',
        'role' => $role
      ];

      //$this->asJson(['res' => 'ok']);
      //exit;

    }

    public function actionLogin_as_user($id){

      if (!User::isAdmin()) return false;

      $user_to_login = CommonUser::findOne($id);
      if($user_to_login)
      if(Yii::$app->user->login($user_to_login, true ? 3600 * 24 * 30 : 0)){
        $this->redirect('/product/index?page=1');
      }else{
        echo "Насяльника, я не смогла авторизоватися";
      }

    }

}
