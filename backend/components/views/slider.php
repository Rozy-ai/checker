<?php
/**
 * @var $product
 * @var $page
 * @var $comparisons
 * @var $product_id
 * @var $options
 */

use common\models\Comparison;
use yii\helpers\Html;

$canCompare = \Yii::$app->user->can('compare-products', ['product' => $product]);
?>

<div class='<?= $options['class'] ?>'>
    <? foreach (array_values($items) as $index => $item): ?>
        <div class="item<?= $page === $index ? " slick-current" : '' ?>">
        <span class="grade<?= isset($comparisons[$index])
            ? ($comparisons[$index]->status === 'MATCH'
                ? ' match'
                : ($comparisons[$index]->status === 'MISMATCH'
                    ? ' mismatch'
                    : ' other'
                ))
            : '' ?><?= $page === $index ? ' current' : '' ?>">
            <?= $item[$options['gradeKey']] ?>
        </span>
            <?= Html::a(
                "<img src='{$item[$options['srcKey']]}'>",
                ['view', 'id' => $product_id, 'node' => $index + 1],
                ['class' => 'linkImg']
            ) ?>

            <? if (!empty($options['salesKey'])): ?>
                <span class="sales">
                <? if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null): ?>
                    <a href="<?= $item[$options['urlKey']] ?>"><?= $item[$options['salesKey']] ?> Sales</a>
                <? else: ?>
                    <?= $item[$options['salesKey']] ?> Sales
                <? endif; ?>
                </span>
            <? endif; ?>

            <? if (!empty($options['delBtn']) && $options['delBtn'] && $canCompare): ?>
                <?= Html::a("", [
                    'compare',
                    'id' => $product_id,
                    'node' => $index+1,
                    'status' => Comparison::STATUS_MISMATCH,
                    'return' => true,
                ], ['class' => 'btn del']
                ) ?>
            <? endif; ?>
        </div>
    <? endforeach; ?>
</div>

<script language="JavaScript">
    window.addEventListener('load', function (e) {
        e.preventDefault();
        (<?=$page?>) && setTimeout(function () {
            $('.<?=$options['class']?>').slick('slickGoTo', <?=$page?>);
        });
    });
</script>
