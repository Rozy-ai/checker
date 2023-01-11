<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "external_user_profile_source".
 *
 * @property int $id
 * @property int $profile_id
 * @property int $source_id
 *
 * @property ExternalUserProfile $profile
 * @property Source $source
 */
class ExternalUserProfileSource extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'external_user_profile_source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['profile_id', 'source_id'], 'required'],
            [['profile_id', 'source_id'], 'integer'],
            [['profile_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExternalUserProfile::class, 'targetAttribute' => ['profile_id' => 'id']],
            [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => Source::class, 'targetAttribute' => ['source_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'profile_id' => Yii::t('app', 'Profile ID'),
            'source_id' => Yii::t('app', 'Source ID'),
        ];
    }

    /**
     * Gets query for [[Profile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(ExternalUserProfile::class, ['id' => 'profile_id']);
    }

    /**
     * Gets query for [[Source]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Source::class, ['id' => 'source_id']);
    }
}
