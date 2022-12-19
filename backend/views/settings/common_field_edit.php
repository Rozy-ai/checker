<?

/* @var $dataProvider */
/* @var $item_res */
/* @var $common_fields */
/* @var $source_list */
/* @var $type_list */
/* @var $return */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('site', 'Редактирование общих ключей');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>
  <?php $form = ActiveForm::begin(['id' => 'settings__common_field_edit']); ?>



  <div style="display: none">
    <?=$form->field($item, 'id')->input('text',['value' => $item_res->id])?>
    <input type="hidden" class="form-control" name="return" value="<?=$return?>" />
  </div>


  <?=$form->field($item, 'name')->input('text',['value' => $item_res->name, 'placeholder' => 'Название ключа'])->label(false);?>
  <?=$form->field($item, 'description')->input('text',['value' => $item_res->description, 'placeholder' => 'Описание'])->label(false);?>


  <?php if ($item_res->id): ?>
    <div class="form-row">

      <div class="col-sm-6 mb-3">
        <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
      </div>

      <div class="col-sm-6 mb-3">
        <?= Html::a("Удалить", '/settings/common_field_delete?id='.$item_res->id, ['class' => 'btn btn-danger btn-block'])?>
      </div>

    </div>
  <?php else: ?>
    <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
  <?php endif;?>

  <?php ActiveForm::end(); ?>
</div>
