<?php

/* @var $this yii\web\View */
/* @var $user common\models\ExternalUser */
/** @var string $verifyLink */

?>
Здравствуйте, <?= $user->getName() ?>,

Перейдите по ссылке для подтверждения почты:

<?= $verifyLink ?>
