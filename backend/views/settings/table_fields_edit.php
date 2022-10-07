<?

/* @var $dataProvider */
/* @var $item_res */
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\Url;

$this->title = Yii::t('site', 'Редактирование поля таблицы');
//$this->params['breadcrumbs'][] = $this->title;

\backend\assets\SettingsAsset::register($this);
?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>
  <? $form = ActiveForm::begin(['id' => 'settings__fields_edit']); ?>

  <div style="display: none">
  <?=$form->field($item, 'id')->input('text',['value' => $item_res->id])?>
  </div>

  <?=$form->field($item, 'title')->input('text',['value' => $item_res->title]);?>
  <?=$form->field($item, 'item_1_key')->input('text',['value' => $item_res->item_1_key]);?>


  <?= $form->field($item, 'item_2_key')->widget(\yii\jui\AutoComplete::class, [
    'clientOptions' => [
      'source' => new JsExpression("function(request, response) {
                                          $.getJSON('" . Url::to(['settings/ajax']) . "', {
                                              search: request.term
                                          }, response);
                                      }"),

    ],
    'options' => [
      'class' => 'form-control',
      'placeholder' => Yii::t('site', 'Key'),
      'value' => $item_res->item_2_key
    ]
  ]) ?>


<? if(0):?>
  <div class="js-wrapper-autocomplete [ ITEM_2_KEY ]">
    <?=$form->field($item, 'item_2_key')->input('text',['value' => $item_res->item_2_key]);?>

  </div>
<? endif;?>

  <?=$form->field($item, 'visible_for_user')->dropDownList([
    '0' => 'User не видит',
    '1' => 'User видит',
  ],[ 'prompt'=>'Select Category', 'options' => [ $item_res->visible_for_user =>['selected'=>true]] ]);?>

  <?= $form->field($item, 'visible')->checkbox(
    [
      'label' => 'Показывать',
      'checked' => $item_res->visible ? true : false
    ]
  )?>


  <? if ($item_res->id): ?>
  <div class="form-row">

    <div class="col-sm-6 mb-3">
      <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
    </div>

    <div class="col-sm-6 mb-3">
      <?= Html::a("Удалить", '/settings/table_fields_delete?id='.$item_res->id, ['class' => 'btn btn-danger btn-block'])?>
    </div>

  </div>
  <? else: ?>
    <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
  <? endif;?>

  <? ActiveForm::end(); ?>
<!--
  <form>
    <div class="form-group">
      <label for="exampleInputEmail1">Email address</label>
      <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
      <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
    </div>
    <div class="form-group">
      <label for="exampleInputPassword1">Password</label>
      <input type="password" class="form-control" id="exampleInputPassword1">
    </div>
    <div class="form-group form-check">
      <input type="checkbox" class="form-check-input" id="exampleCheck1">
      <label class="form-check-label" for="exampleCheck1">Check me out</label>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
  </form>
-->
</div>

