<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var string $link */

?>
<div class="password-reset">
    <p>Здравствуйте, <?= Html::encode($user->getName()) ?>,</p>

    <p>Перейдите по ссылке для сброса пароля:</p>

    <p><?= Html::a(Html::encode($link), $link) ?></p>
</div>
