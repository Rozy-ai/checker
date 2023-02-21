<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "external_user_profile_field_val".
 *
 * @property int $id
 * @property int $field_id
 * @property int $ex_user_id
 * @property string $value
 *
 * @property ExternalUser $exUser
 * @property ExternalUserProfileField $field
 */
class ExternalUserProfileFieldVal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'external_user_profile_field_val';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['field_id', 'ex_user_id', 'value'], 'required'],
            [['field_id', 'ex_user_id'], 'integer'],
            [['value'], 'string'],
            [['field_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExternalUserProfileField::class, 'targetAttribute' => ['field_id' => 'id']],
            [['ex_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExternalUser::class, 'targetAttribute' => ['ex_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'field_id' => Yii::t('app', 'Field ID'),
            'ex_user_id' => Yii::t('app', 'Ex User ID'),
            'value' => Yii::t('app', 'Value'),
        ];
    }

    /**
     * Gets query for [[ExUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExUser()
    {
        return $this->hasOne(ExternalUser::class, ['id' => 'ex_user_id']);
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
}
