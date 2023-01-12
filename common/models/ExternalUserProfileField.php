<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "external_user_profile_field".
 *
 * @property int $id
 * @property string $name
 * @property string|null $comment
 * @property string $type
 *
 * @property ExternalUserProfileConfig[] $externalUserProfileConfigs
 */
class ExternalUserProfileField extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'external_user_profile_field';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['comment'], 'string'],
            [['name'], 'string', 'max' => 1024],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'comment' => Yii::t('app', 'Comment'),
            'type' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * Gets query for [[ExternalUserProfileConfigs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExternalUserProfileConfigs()
    {
        return $this->hasMany(ExternalUserProfileConfig::class, ['field_id' => 'id']);
    }
}
