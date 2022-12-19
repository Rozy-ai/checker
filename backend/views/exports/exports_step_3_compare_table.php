
<?php

/* @var $table_items */
/* @var $comparisons */
/* @var $source_id */
/* @var $profile */
/* @var $ignore_step_3 */

use common\models\Source;
use yii\helpers\Html;
use yii\jui\JuiAsset;
use yii\widgets\ActiveForm;

$model = new \yii\base\DynamicModel(['id','name','source_id','type','selected','position','profile']);
$model->addRule(['id'], 'integer');
$model->addRule(['name'], 'string');
$model->addRule(['source_id'], 'integer');
$model->addRule(['comparisons'], 'required');
$model->addRule(['type'], 'trim');
$model->addRule(['selected'], 'integer');
$model->addRule(['position'], 'integer');
$model->addRule(['use_previous_saved'], 'integer');
$model->addRule(['profile'], 'string');

\backend\assets\ExportsAsset::register($this);
?>

<style>
  .table.table-striped.table-bordered td{

  }

  .ui-sortable-helper .td_4{
    width: 100px!important;
  }
  .ui-sortable-helper .td_3{
    width: 108px!important;
  }
  .ui-sortable-helper .td_2{
    width: auto!important;
  }
  .ui-sortable-helper .td_1{
    width: 41px!important;
  }
  .export_items-tr{
    width: 100%;
  }
  .td_1.left_item{
    border-left: 3px solid #e0e000;
  }
  .td_1.right_item{
    border-left: 3px solid #04ac00;
  }


  .ui-sortable-helper{
    width: 100%!important;
    display: table!important;
  }
  .export_items{
    position: relative;
    width: 100%;
  }
</style>

<div>
  <h2>Выгрузка: Step 3</h2>
  <div style="margin-bottom: 10px">
    Выбрано: <?=Source::get_source($source_id)['source_name']?> (<?= $comparisons?>)
  </div>

    <div id="w0" class="grid-view">
      <table class="table table-striped table-bordered js-table-root" data-source_id="<?=$source_id?>"
             data-comparisons='<?=serialize($comparisons)?>'
             data-profile='<?=$profile?>'
      >
        <thead>
        <tr class="export_items-tr">
          <th><input class="js-select-all" type="checkbox" value="" /></th>
          <th>key</th>
          <th>type</th>
          <th>position</th>
        </tr>
        </thead>
        <tbody class="export_items">

          <?php foreach ($table_items as $k => $item) :?>
          <tr class="[ item ]"
              data-item_id="<?=$item['id']?>"
              data-name="<?=$item['name']?>"
              data-type="<?=$item['type']?>"
          >
            <td class="td_1 <?= $item['type']?>">
              <input
                type="checkbox"
                <?php if ((int)$item['selected'] === 1): ?>checked="checked"<?php endif;?>
                class="item_checkbox"
              />
            </td>
            <td class="td_2"><?= $item['name']?></td>
            <td class="td_3"><?= $item['type']?></td>
            <td class="td_4 -position"><?= $item['position']?></td>
          </tr>
          <?php endforeach;?>

        </tbody>
      </table>
      <nav id="w1"></nav>
    </div>



  <div class="row">
    <div class="col">
      <span onclick="window.location = '/exports/step_2';" class="btn btn-secondary btn-block">Назад</span>
    </div>
    <div class="col">
      <button type="submit" class="btn btn-primary btn-block js-step-4">Далее</button>
    </div>

  </div>

</div>

<?php if ((int)$ignore_step_3 === 1): ?>
<script src="/js/exports_step_3.js" async defer></script>
<?php endif;?>
