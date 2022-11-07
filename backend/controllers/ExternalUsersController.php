<?php

namespace backend\controllers;

use backend\models\ExternalUser;
use backend\models\search\ExternalUsersSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\widgets\ActiveForm;

/**
 * ExternalUsersController implements the CRUD actions for ExternalUser model.
 */
class ExternalUsersController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin']
                    ],
                ],
            ]
        ];
    }

    /**
     * Lists all ExternalUser models.
     *
     * @return string
     */
    public function actionIndex(Request $request): string
    {
        $searchModel = new ExternalUsersSearch();
        $dataProvider = $searchModel->search($request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExternalUser model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ExternalUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate(Request $request)
    {
        $model = new ExternalUser();

        if ($request->getIsPost()) {
            if ($model->load($request->post())) {
                if ($request->post('ajax') === 'external-user-form') {
                    return $this->asJson(ActiveForm::validate($model));
                }
                if ($model->save()) {
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ExternalUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id, Request $request)
    {
        $model = $this->findModel($id);

        if ($model->load($request->post())) {
            if ($request->post('ajax') === 'external-user-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ExternalUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete(int $id): \yii\web\Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ExternalUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ExternalUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): ExternalUser
    {
        if (($model = ExternalUser::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
