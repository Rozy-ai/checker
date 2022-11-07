<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%profile_name}}".
 *
 * @property int $id
 * @property string $name
 * @property int $position
 */
class ProfileName extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile_name}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['position'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'position' => 'Position',
        ];
    }
}
