<?php
/**
 * @var \backend\components\ComparisonWidget $model
 * @var \common\models\Message[] $messages
 * @var  $source_id
 * @var  $product_id
 */

use common\models\Comparison;
use yii\helpers\Html;


?>
<?php if (0 && $model->canCompare): ?>
    <?php if ($model->comparison): ?>
        <?php if (\Yii::$app->authManager->getAssignment('admin', \Yii::$app->user->id) !== null): ?>
            <?php if ($model->comparison->status == Comparison::STATUS_MATCH): ?>
                <?= Html::a($model->comparison->url, null, ['class' => 'btn btn-link text-success text-nowrap']) ?>
            <?php endif ?>
            <?php if ($model->comparison->status == Comparison::STATUS_OTHER): ?>
                <?= Html::label($model->comparison->message, null, ['class' => 'font-weight-bold text-danger text-nowrap']) ?>
            <?php endif ?>
        <?php endif ?>
    <?php endif; ?>
<?php endif; ?>

<div class="control dropup">
    <?php if ($model->canCompare): ?>
        <?php if (0 && $model->comparison): ?>
            <button id="btnGroupDrop1" type="button" class="btn faq" data-toggle="dropdown"
                    aria-expanded="false"></button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop2">
                <?= Html::a(Yii::t('site', Comparison::STATUS_MATCH), ['compare', 'id' => $product_id, 'item_id' => $item_id, 'node' => $node_idx, 'status' => Comparison::STATUS_MATCH], ['class' => 'dropdown-item']) ?>
                <?= Html::a(Yii::t('site', Comparison::STATUS_MISMATCH), ['compare', 'id' => $product_id, 'item_id' => $item_id, 'node' => $node_idx, 'status' => Comparison::STATUS_MISMATCH, 'return' => 1], ['class' => 'dropdown-item']) ?>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header"><?= Yii::t('site', Comparison::STATUS_OTHER) ?></h6>
                <?php foreach ($messages as $message): ?>
                    <?= Html::a($message->text, ['compare', 'id' => $product_id, 'item_id' => $item_id, 'msgid' => $message->id, 'node' => $node_idx, 'status' => Comparison::STATUS_OTHER], ['class' => 'dropdown-item']) ?>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
        <?php
        $_hover_other = '';
        $_hover_match = '';
        $_hover_mismatch = '';
        
        if ($model->comparison->status === 'OTHER') $_hover_other = '-hover';
        if ($model->comparison->status === 'MATCH') $_hover_match = '-hover';
        if ($model->comparison->status === 'MISMATCH') $_hover_mismatch = '-hover';

        ?>
            <?= Html::tag("span", '',['data-url'=>'/product/compare', 'href'=>'#', 'data-url_next'=>'', 'data-product_id' => $product_id, 'data-item_id' => $item_id, 'data-source_id' => $source_id, 'data-node' => $node_idx, 'data-status' => Comparison::STATUS_MATCH,'class' => 'btn yes '.$_hover_match.' js-compare']) ?>
            <?= Html::tag("span", '',['data-url'=>'/product/compare', 'href'=>'#', 'data-url_next'=>'', 'data-product_id' => $product_id, 'data-item_id' => $item_id, 'data-source_id' => $source_id, 'data-node' => $node_idx, 'data-status' => Comparison::STATUS_MISMATCH, 'data-return' => 1, 'class' => 'btn del '.$_hover_mismatch.' js-compare']) ?>
            <button id="btnGroupDrop2" type="button" class="btn faq <?=$_hover_other?>" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">&nbsp;
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop2">
                <?php foreach ($messages as $message): ?>
                    <?= Html::a($message->text, ['compare', 'id' => $product_id, 'item_id' => $item_id, 'source_id' => $source_id, 'msgid' => $message->id, 'node' => $node_idx, 'status' => Comparison::STATUS_OTHER], ['class' => 'dropdown-item']) ?>
                <?php endforeach; ?>
            </div>

        <?php endif ?>

    <?php else: ?>

        <?php if ($model->comparison): ?>
            <button type="button" class="btn btn-dark" disabled>
                <?= Yii::t('site', $model->comparison->status) ?>
            </button>
        <?php endif ?>

    <?php endif ?>

  <div class="clearfix"></div>
</div>
