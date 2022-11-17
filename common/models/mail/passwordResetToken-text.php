<?php

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var string $link */

?>
Здравствуйте, <?= $user->getName() ?>,

Перейдите по ссылке для сброса пароля:

<?= $link ?>
