<?php

namespace backend\controllers;

use common\models\Stats_import_export;
use common\models\StatsSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * StatsController implements the CRUD actions for Stats model.
 */
class StatsController extends Controller
{


    /**
     * @inheritDoc
     */

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index','export','import'],
                            'roles' => ['@'],
                        ],
                    ],
                ],

            ]
        );
    }


  public function actionExport(){

                  $s = Stats_import_export::find();
                  $s->where(['type' => 'EXPORT']);
                  $s->orderBy(['created' => SORT_DESC]);
    $dataProvider = new ActiveDataProvider([
      'query' =>  $s,
    ]);
    return $this->render('export',
      [
        'dataProvider_export' => $dataProvider,
      ]
    );

  }

  public function actionImport(){

                  $s = Stats_import_export::find();
                  $s->where(['type' => 'IMPORT']);
                  $s->orderBy(['created' => SORT_DESC]);
    $dataProvider = new ActiveDataProvider([
      'query' =>  $s,
    ]);
    return $this->render('import',
      [
        'dataProvider_import' => $dataProvider,
      ]
    );

  }


  /**
     * Lists all Stats models.
     * @return mixed
     */
  public function actionIndex(){

    // Stats__import_export
    // Имя_файла, дата, время, бд, профиль, количество записей
    // id   type      file_name                                         Comparison      cnt   raw         created               source_id       profile
    // 1    IMPORT    EBAY_YES_NO_OTHER_ALL_2022.07.27_11.00_32.xlsx    YES_NO_OTHER    32    {}          2022-07-27 11:00:32   1               ALL
    // 2    EXPORT

    $params = [];
    if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) === null) {
      $params ['user_id'] = \Yii::$app->user->id;
    } else {
      if (\Yii::$app->request->get('mode') == 'total') {
        $params ['total'] = true;
      }
    }
    $queryParams = $this->request->queryParams;
    $searchModel = new StatsSearch($params);
    $dataProvider = $searchModel->search($queryParams);

                  $s_import = Stats_import_export::find();
                  $s_import->where(['type' => 'IMPORT']);
                  $s_import->orderBy(['created' => SORT_DESC]);
                  $s_import->limit(5);
    $dataProvider_import = new ActiveDataProvider([
      'query' =>  $s_import,
      'pagination' => false,
    ]);

                  $s_export = Stats_import_export::find();
                  $s_export->where(['type' => 'EXPORT']);
                  $s_export->limit(5);
                  $s_export->orderBy(['created' => SORT_DESC]);
    $dataProvider_export = new ActiveDataProvider([
      'query' =>  $s_export,
      'pagination' => false,
    ]);



    return $this->render('index',
      [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'dataProvider_import' => $dataProvider_import,
        'dataProvider_export' => $dataProvider_export,
      ]
    );
  }



}
