<?

/* @var $dataProvider */
/* @var $item_res */
/* @var $common_fields */
/* @var $source_list */
/* @var $type_list */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('site', 'Редактирование сопоставления');
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="[ SETTINGS-MAIN ] settings-main">
  <h1><?= Html::encode($this->title) ?></h1>
  <? $form = ActiveForm::begin(['id' => 'settings__source_fields']); ?>



  <div style="display: none">
  <?=$form->field($item, 'id')->input('text',['value' => $item_res->id])?>
  </div>

  <div class="form-row">
    <div class="col-10">
    <?=$form->field($item, 'settings__common_fields_id')->dropDownList(
      $common_fields
      ,[ 'prompt'=>'Выбрат общее название', 'options' => [ $item_res->settings__common_fields_id => ['selected' => true] ] ])->label(false);;?>
    </div>
    <div class="col-2">
      <a href="/settings/common_field_edit?return=<?='/settings/source_fields_edit'.(($item_res->id)? '?id='.$item_res->id : '')?>" class="btn btn-primary btn-block">добавить</a>
    </div>
  </div>


  <?=$form->field($item, 'source_id')->dropDownList(
    $source_list
    ,[ 'prompt'=>'Source', 'options' => [ $item_res->source_id => ['selected' => true] ] ])->label(false);?>


  <?=$form->field($item, 'field_action')->dropDownList(
    ['0' => 'ключ','replace' => 'подстановка','formula' => 'формула']
    ,[ 'prompt'=>'Выбрать', 'options' => [ $item_res->field_action => ['selected' => true] ] ])->label(false);?>


  <?=$form->field($item, 'name')->input('text',['value' => $item_res->name, 'placeholder' => 'Название ключа из выбранного source'])->label(false);?>

  <?=$form->field($item, 'type')->dropDownList(
    $type_list
    ,[ 'prompt'=>'Правый/левый товар', 'options' => [ $item_res->type => ['selected' => true] ] ])->label(false);?>



  <? if ($item_res->id): ?>
  <div class="form-row">

    <div class="col-sm-6 mb-3">
      <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
    </div>

    <div class="col-sm-6 mb-3">
      <?= Html::a("Удалить", '/settings/source_fields_delete?id='.$item_res->id, ['class' => 'btn btn-danger btn-block'])?>
    </div>

  </div>
  <? else: ?>
    <?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>
  <? endif;?>

  <? ActiveForm::end(); ?>

  <div>
    <strong>ключ:</strong> <br/>
    можно использовать из result такие, [info.image_google] ( но без приставки result )
    <pre>

    "url": "https://gistgear.com/product/B092PWZTJS",
    "price": 13.99,
    "title": "Navani Body Molds Body Silicone Mold, 3D Durable Female ...",
    "is_delete": false,
    "message": "Successful",
    "results": {
      "info": {
        "images": [],
        "image_google": "https://gistgear.com/images/gistGearLogo.png|https://gistgear.com/images/gistGearLogoTheEssential.png|https://gistgear.com/images/SearchWhite50px.png|https://m.media-amazon.com/images/I/31eNlwCRZhS._SL500_.jpg|https://m.media-amazon.com/images/I/41CvrvN+o9S._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41XHBZ4AcvS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41gQp50olMS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/418AgbQnteS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41FEuJMAXKS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31LBNJFFlwS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51Of8FSrmQS._SL500_.jpg|https://m.media-amazon.com/images/I/41VlfKyYpyL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31xZHVKtSZL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41YUbnhxk0L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31JYT5kfARL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41FlEnCsedL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/418AasajvoL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31AcTIzGAcS._SL500_.jpg|https://m.media-amazon.com/images/I/41GitlBz04S._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41qNTOYkZ0S._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41-E52fXewS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41k5ySFdgOS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41YTGsXC1SS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41eoykoiGMS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41bEpdLP7OS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51ohbWWerAS._SL500_.jpg|https://m.media-amazon.com/images/I/51x2bEoyCvL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51tNsoS3VxL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51GM4epSuzL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51mwRwZZ9dL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51vOW1pFZYL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51j0yHQ7b7L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31EEcPcKdYL._SL500_.jpg|https://m.media-amazon.com/images/I/31eDGZ98QXL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41HT75E5xkL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51NLRvm7WQL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31thNgaa3BL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41DpFpbb1OL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51ZM9E3W0kL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/4151VvoU0yL._SL500_.jpg|https://m.media-amazon.com/images/I/515+R9XQT2L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51qp0rHuHTL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51UmFuAh6vL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41HvmD8+TPL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51J5TFsfXzL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51PGWl0mZWL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51-oXUS4-CL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41fDB1G7G8L._SL500_.jpg|https://m.media-amazon.com/images/I/51TfWbEYQjL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51TziQrQuBL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51QDw5Md1RL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/519X6eYGkwL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51WTL1ZVCCL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51wK6EgpjBL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51-HOw7DBhL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/512E72E0mfL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41hA3yB0nkS._SL500_.jpg|https://m.media-amazon.com/images/I/41sbbucEGJS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/3143bovApfS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31icfgSKt4S._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31bkxgnM42S._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/21o0W5N4h-S._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41Wp71WVnsS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/21X-2O1PQNS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51P0LcCo0+L._SL500_.jpg|https://m.media-amazon.com/images/I/418htwnY24L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51RkDeAZeOS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51yq4FBo5DL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/518wpt9EqDL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/411afBNtZZL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41uBnRaxdOL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51h1Rmkj1EL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51UQVMxX8bL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41ZCF6D-34S._SL500_.jpg|https://m.media-amazon.com/images/I/41PSHjK6pbS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/5132c0-XT5L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51Jyp2oTtlS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51hl5ESJJkL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51CBd2PpHRL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51SbWxAiMaL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/311EEamIGDL._SL500_.jpg|https://m.media-amazon.com/images/I/316wJYGuwlL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41umZo4-9QL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41-VexWG2fL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31oNTSWpSLL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31uOATJ6gIL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41iacUBmiML._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/5191edydIpS._SL500_.jpg|https://m.media-amazon.com/images/I/51hPuxYh2RL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51TF6UmWbjL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51LioRF4wyS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51hQrkNqIJS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41+5bbni8bS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51dMn3TZJOS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/312xiRI2cFS._SL500_.jpg|https://m.media-amazon.com/images/I/51ZYtPLloSL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41P6qFo1JXL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41crfZ5pBZL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41EaiDy3SHL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41yC7BXQAHS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/512TTFOFwEL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51d-yu+e3ZL._SL500_.jpg|https://m.media-amazon.com/images/I/418htwnY24L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51gv0bRVyrL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51XkXacITCL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51RB9Ro4NfL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/411afBNtZZL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41186elCWUL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51kasvOFEgL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51hcKH5LIWL._SL500_.jpg|https://m.media-amazon.com/images/I/51fOqkiSB8L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51Sn4WekEdL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51bCtetZvbL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/518GAokxACL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51zdbKAVpnL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51ldEVuT8kL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31mn7lJ14qL._SL500_.jpg|https://m.media-amazon.com/images/I/31EhANOMOVL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41E9tmGIX9L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/418tv6gNXiL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51d3dJy3VYL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/519EllbtkmL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31a8ccf51aL._SL500_.jpg|https://m.media-amazon.com/images/I/51UQllLbtwL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/416eUk6yRBL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51uBm-XKK4L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51gf8AdaebL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/415-QBjA59L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51JttzUK8bL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41jgYIRUYpL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51mELNbNaPS._SL500_.jpg|https://m.media-amazon.com/images/I/41v6tohlqTS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41F9f+no6GS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51x5lkeu77S._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51I3yzzW9uS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/515AdENp-VS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51qnu6w9MkS._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51rWoH24PQL._SL500_.jpg|https://m.media-amazon.com/images/I/51X4cyTGHyL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/31yK61FUNmL._SL500_.jpg|https://m.media-amazon.com/images/I/51XkM1Jec1L._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41zeA22j8vL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41S8aUsEYdL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/51qvkpEtzQL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41gMY3JbxnL._SL500_._SL90_.jpg|https://m.media-amazon.com/images/I/41SBMBfLhOL._SL500_._SL90_.jpg|https://gistgear.com/images/SearchWhite50px.png",
        "is_cart": 0,
        "is_shop": 1,
        "price": ""
      }
    }

    </pre>
  </div>
  <div>
    <strong>подстановка:</strong> <br/>
    значение ключей будет подставлено в {{name}}<br/>
    {{Find_PriceMin_Ali::int}} ({{Find_PriceMax_Ali::string::-}})<br/>
    ↓<br>
    785 ( 558 )<br/>
    <br/>
    Find_PriceMax_Ali::string::-<br/> тип вывода <i>string</i>, а если будет пусто:"" то выведет: -
    <br/>
    <br/>
    Ebay_item::function::parse_ebay_item_id<br/> тип вывода через <i>function</i> например если надо распарсить то что
    лежит в ключе Ebay_item (пример: 1/2/3/4/5). Метод с именем parse_ebay_item_id($data){... добавляй в классе Product_right
    <br/>
    <br/>
    типы: <br>
    int|float|string|function
  </div>

  <br>

  <div>
    <strong>формула:</strong> <br/>
    1/ двойные (( ... )) - математический приоритет<br/>
    2/ двойные {{ ... }} - ключи из товара<br/>
    3/ двойные [[ ... ]] - операторы + - / *<br/>
    <i>пример:</i> <br/>( $item->Find_PriceMin_Ali + $item->Find_PriceMax_Ali ) / 2 <br/> (( {{Find_PriceMin_Ali}}[[+]]{{Find_PriceMax_Ali}} )) [[/]] 2
  </div>
</div>

