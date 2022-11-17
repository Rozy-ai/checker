<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\ExternalUser */
/** @var string $verifyLink */

?>
<div class="verify-email">
    <p>Здравствуйте <?= Html::encode($user->getName()) ?>,</p>
    <p>Перейдите по ссылке для подтверждения почты:</p>
    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>
