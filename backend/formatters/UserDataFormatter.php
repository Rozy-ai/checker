<?php

namespace backend\formatters;

use Yii;
use common\models\User;

class UserDataFormatter extends \yii\i18n\Formatter
{
    public function asStatus($value)
    {
        $statuses = User::getStatuses();
        if (isset($statuses[$value]))
        {
            return Yii::t('site', $statuses[$value]);
        }
        else {
            return Yii::t('site', 'USER_STATUS_UNKNOWN');
        }
    }
}
