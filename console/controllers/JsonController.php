<?php

namespace console\controllers;

use yii\db\Expression;
use yii\helpers\Json;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use common\models\Product;

class JsonController extends Controller
{
    public function actionParse($id)
    {
        $model = Product::findOne($id);
        if (is_null($model))
        {
            return ExitCode::USAGE;
        }
        
        $json = Json::decode($model->info, true);

        foreach ($json as $key => $value)
        {
            print("{$key}\n");
        }
        
        return Exitcode::OK;
    }
    
    public function actionAddInfo($id, $url = '')
    {
        $model = Product::findOne($id);
        if (is_null($model))
        {
            return ExitCode::USAGE;
        }
        
        $json = Json::decode($model->info, true);
        if (isset($json['add_info']))
        {
            $addInfo = Json::decode($json['add_info'], true);
            if ($url)
            {
                $addInfo = $addInfo [$url];
            }
            foreach ($addInfo as $key => $value)
            {
                print("{$key}\n");
            }
        }
    }
}
