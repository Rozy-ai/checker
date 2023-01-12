<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "external_user_profile".
 *
 * @property int $id
 * @property string $name
 * @property string $comment
 * @property string $description
 * @property int $need_confirmation
 *
 * @property ExternalUserProfileConfig[] $externalUserProfileConfigs
 * @property ExternalUserProfileSource[] $externalUserProfileSources
 */
class ExternalUserProfile extends \yii\db\ActiveRecord
{
    public array $allowedSources = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'external_user_profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'comment', 'description'], 'required'],
            [['comment', 'description'], 'string'],
            [['need_confirmation'], 'integer'],
            [['name'], 'string', 'max' => 1024],
            [['allowedSources'], 'safe'],
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
            'description' => Yii::t('app', 'Description'),
            'need_confirmation' => Yii::t('app', 'Need Confirmation'),
        ];
    }

    /**
     * Gets query for [[ExternalUserProfileConfigs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExternalUserProfileConfigs()
    {
        return $this->hasMany(ExternalUserProfileConfig::class, ['profile_id' => 'id']);
    }

    /**
     * Gets query for [[ExternalUserProfileSources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExternalUserProfileSources()
    {
        return $this->hasMany(ExternalUserProfileSource::class, ['profile_id' => 'id']);
    }
}
