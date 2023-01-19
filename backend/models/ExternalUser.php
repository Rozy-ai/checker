<?php

namespace backend\models;

use common\models\ExternalUserProfile;

/**
 * @inheritDoc
 */
class ExternalUser extends \common\models\ExternalUser
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['login', 'email'], 'required'],
            [['created_at', 'updated_at', 'ex_profile_id'], 'integer'],
            [['login'], 'string', 'max' => 24],
            [['email'], 'string', 'max' => 64],
            ['status', 'in', 'range' => array_keys(self::STATUSES)],
            [['password'], 'string', 'max' => 60],
            [['login'], 'unique'],
            [['email'], 'unique'],
            [
                ['ex_profile_id'], 'exist', 'skipOnError' => true,
                'targetClass' => ExternalUserProfile::class, 'targetAttribute' => ['ex_profile_id' => 'id']
            ],
        ];
    }
}
