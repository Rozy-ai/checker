<?php

namespace backend\controllers;

use backend\assets\MessageAsset;
use Yii;
use backend\models\User;
use common\models\Message;
use common\models\MessageSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller{
  /**
   * @inheritDoc
   */
  public function behaviors(){
    return array_merge(
      parent::behaviors(),
      [
        'verbs' => [
          'class' => VerbFilter::className(),
          'actions' => [
            'batch' => ['POST'],
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
     * Lists all Message models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MessageSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user_list' => User::find()->where('status > 0')->all(),
        ]);
    }



    /**
     * Creates a new Message model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect('/message/');
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }




  /**
   * Updates an existing Message model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param int $id ID
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id){

    $model = $this->findModel($id);
    if ($this->request->isPost ) {
      if ($model->load($this->request->post()) && $model->save())
      return $this->redirect('/message/');
    }

    return $this->render('update', ['model' => $model,]);

  }

  /**
   * Displays a single Message model.
   * @param int $id ID
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id){
    return $this->render('view', [
      'model' => $this->findModel($id),
    ]);
  }







  public function actionUser()
    {
        $userId = Yii::$app->request->get('user');
        $modelUser = User::findOne($userId);
        if ($modelUser)
        {
            $searchModel = new MessageSearch();
            $searchParams = [$searchModel->formName() => ['user_id' => $modelUser->id]];
            $dataProvider = $searchModel->search($searchParams);
            
            return $this->render('user', [
                'user' => $modelUser,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
        
        throw new NotFoundHttpException(Yii::t('site', 'The requested page does not exist.'));
    }

    public function actionBatch()
    {
        if (Yii::$app->request->isPost)
        {
            $action = Yii::$app->request->post('action');
            $selection = Yii::$app->request->post('selection');
            if (count($selection) > 0)
            {
                $messages = Message::findAll($selection);
                $username = Yii::$app->request->post('username');
                $modelUser = User::findOne(['username' => $username]);
                switch($action)
                {
                    case 'link':
                        if ($modelUser && count($messages) > 0)
                        {
                            foreach ($messages as $message)
                            {
                                $modelUser->link('messages', $message);
                            }
                        }
                        break;
                    case 'unlink':
                        if ($modelUser && count($messages) > 0)
                        {
                            foreach ($messages as $message)
                            {
                                $modelUser->unlink('messages', $message, true);
                            }
                        }
                        break;
                }
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        
        throw new NotFoundHttpException(Yii::t('site', 'The requested page does not exist.'));
    }
    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id){
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('site', 'The requested page does not exist.'));
    }
}
