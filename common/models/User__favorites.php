<?php

namespace common\models;

use yii\db\ActiveRecord;

class User__favorites extends ActiveRecord
{
    public const TYPE_USER = ['EXTERNAL', 'INTERNAL'];
    public const TYPE_USER_DEFAULT = 'INTERNAL';
}
