<?php
/**
 * Created by PhpStorm.
 * User: Professional
 * Date: 14.03.2022
 * Time: 7:01
 */

namespace backend\components;


use common\models\Message;
use yii\base\Widget;

class ComparisonWidget extends Widget
{
    public $comparison;
    public $canCompare;
    public $product_id;
    public $item_id;
    public $node_idx;
    public $source_id;

    public function __construct(array $config = []){
        parent::__construct($config);
    }

    public function run(){
        $messages = Message::find()
            ->joinWith('users')
            ->where(['{{%user_message}}.user_id' => \Yii::$app->user->id])
            ->andWhere(["messages.settings__table_rows_id" => -1])
            ->all();


        return $this->render('comparison', [
            'model' => $this,
            'messages' => $messages,
            'product_id' => $this->product_id,
            'item_id' => $this->item_id,
            'node_idx' => $this->node_idx,
            'source_id' => $this->source_id,
            'comparison' => $this->comparison,
        ]);
    }
}
