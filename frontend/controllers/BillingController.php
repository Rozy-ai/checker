<?php

declare(strict_types=1);

namespace frontend\controllers;

use frontend\components\User;
use frontend\models\search\BillingSearch;
use yii\web\Controller;
use yii\web\Request;

class BillingController extends Controller
{
    public function actionIndex(User $user, Request $request): string
    {
        $searchModel = new BillingSearch();
        $dataProvider = $searchModel->search($user->getId(), $request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
