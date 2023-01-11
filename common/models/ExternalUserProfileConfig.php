<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "external_user_profile_config".
 *
 * @property int $id
 * @property int|null $profile_id
 * @property int|null $field_id
 *
 * @property ExternalUserProfileField $field
 * @property ExternalUserProfile $profile
 */
class ExternalUserProfileConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'external_user_profile_config';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['profile_id', 'field_id'], 'integer'],
            [['field_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExternalUserProfileField::class, 'targetAttribute' => ['field_id' => 'id']],
            [['profile_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExternalUserProfile::class, 'targetAttribute' => ['profile_id' => 'id']],
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
            'field_id' => Yii::t('app', 'Field ID'),
        ];
    }

    /**
     * Gets query for [[Field]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getField()
    {
        return $this->hasOne(ExternalUserProfileField::class, ['id' => 'field_id']);
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
}
