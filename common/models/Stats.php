<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%stats}}".
 *
 * @property int $user_id
 * @property string $period
 * @property string $status
 * @property int $count
 */
class Stats extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stats}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'period', 'status', 'count'], 'required'],
            [['user_id', 'count'], 'integer'],
            [['status'], 'string'],
            [['period'], 'string', 'max' => 10],
            [['user_id', 'period', 'status'], 'unique', 'targetAttribute' => ['user_id', 'period', 'status']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('site', 'User ID'),
            'period' => Yii::t('site', 'Period'),
            'status' => Yii::t('site', 'Status'),
            'count' => Yii::t('site', 'Count'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
