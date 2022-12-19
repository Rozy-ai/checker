<?php

declare(strict_types=1);

use common\models\ProfileTypeSetting;
use common\models\Source;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var ProfileTypeSetting[] $profileTypeSettings
 * var Source[] $sources
 * @var string[] $types
 * @var \yii\data\DataProviderInterface $dataProvider
 */

$this->title = Yii::t('site', 'Настройки тарифов');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div>
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="table-responsive">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,

            'columns' => [
                'source.name',
                'profileTypeLabel',
                'price1',
                'price2',
                'max_views_count',
                'cancel_show_count',
                'inner_page',
                [
                    'attribute' => 'Edit',
                    'format' => 'raw',
                    'value' => static function (ProfileTypeSetting $itm) {
                        return Html::a(
                            'Edit',
                            '/settings/profile_types_edit?profile_type=' . $itm->profile_type . '&source_id=' . $itm->source_id,
                            ['type' => "submit", 'class' => 'btn btn-primary']
                        );
                    }
                ],
            ],
        ])
        ?>
    </div>
</div>