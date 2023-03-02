<?php

namespace backend\models;

use Yii;
use common\models\Comparison;
use common\models\Message;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property int $status
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [['status'], 'integer'],
            [['username', 'email'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['email'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('site', 'Username'),
            'email' => Yii::t('site', 'Email'),
            'status' => Yii::t('site', 'Status'),
        ];
    }
    

    /**
     * Gets query for [[Comparison]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComparisons()
    {
        return $this->hasMany(Comparison::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Message]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(Message::className(), ['id' => 'message_id'])
                    ->viaTable('user_message', ['user_id' => 'id']);
    }
}
