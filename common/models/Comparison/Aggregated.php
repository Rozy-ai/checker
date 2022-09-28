<?php
namespace common\models\Comparison;

use Yii;

/**
 * This is the model class for table "{{%comparisons_aggregated}}".
 *
 * @property int $product_id
 * @property string $users
 * @property string $nodes
 * @property int $counted
 */
class Aggregated extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%comparisons_aggregated}}';
    }

}