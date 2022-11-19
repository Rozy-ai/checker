<?php
namespace backend\controllers;


use backend\models\Settings__common_fields;
use backend\models\Settings__fields_extend_price;
use backend\models\Settings__list;
use backend\models\Settings__source_fields;
use backend\models\Settings__table_rows;
use common\models\Source;
use common\models\Product;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;
use yii\web\Controller;

class SettingsController extends Controller{

  /**
   * {@inheritdoc}
   */
/*  public function rules()
  {
    return [
      [['id', 'title', 'route'], 'required'],
    ];
  }
*/



  public function actionTable_fields_delete(){
    $id = false;
    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!$id || !Yii::$app->user->can('admin')) exit;


    $item = Settings__table_rows::findOne($id);
    $item->delete();

    Yii::$app->session->setFlash('success','Удалено');
    return $this->redirect('/settings/table_fields');

  }

  public function actionTest(){
    Yii::$app->mailer->compose('contact/html', ['contactForm' => 'tttttt'])
      ->setFrom('roman.trohimenko@gmail.com')
      ->setTo('info@map.in.ua')
      ->setSubject('test send')
      ->send();

    echo '<pre>'.PHP_EOL;
    print_r('email');
    echo PHP_EOL;
    exit;
  }

  public function actionTable_fields_edit(){

//    //echo '<pre>'.PHP_EOL;
//    print_r($this->request->post());
//    //echo PHP_EOL;
//    exit;
    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!Yii::$app->user->can('admin')) exit;

    if ($this->request->getIsPost()){
      $id = $this->request->post('Settings__table_rows')['id'];
      if (isset($id) && $id){
        $item = Settings__table_rows::findOne($id);
        if ($item->load($this->request->post())){
          $item->save();
          //Yii::$app->session->setFlash('success','Данные приняты');
          return $this->redirect('/settings/table_fields');
        }
      }else{
        $item = new Settings__table_rows();
        if ($item->load($this->request->post())) {
          $item->insert();
        }
        return $this->redirect('/settings/table_fields');
      }

    }else{
      $item = new Settings__table_rows();
    }


    $item_res = Settings__table_rows::find()->where(['id' => $id])->limit(1)->one();


    return $this->render('table_fields_edit',[
      'item' => $item,
      'item_res' => $item_res
    ]);

  }



  public function actionTable_fields(){

    $s = new Settings__table_rows();
    $query = $s->find();
    $query = Settings__table_rows::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 100,
      ],
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_ASC,
        ]
      ],
    ]);

    return $this->render('table_rows', [
      'dataProvider' => $dataProvider,
    ]);


  }

  public function actionSource_list(){

    $s = new Source();
    $query = $s->find();
    $query = Source::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 100,
      ],
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_ASC,
        ]
      ],
    ]);

    return $this->render('settings__source_list', [
      'dataProvider' => $dataProvider,
    ]);


  }






/************* Common_field */
  public function actionCommon_fields(){

    $s = new Settings__common_fields();
    $query = $s->find();
    $query = Settings__common_fields::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 100,
      ],
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    return $this->render('settings__common_fields', [
      'dataProvider' => $dataProvider,
    ]);
  }
  public function actionCommon_field_edit(){

    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!Yii::$app->user->can('admin')) exit;

    if ($this->request->getIsPost()){
      $id = $this->request->post('Settings__common_field_edit')['id'];
      if (isset($id) && $id){
        $item = Settings__common_fields::findOne($id);

        if ($item->load($this->request->post())){
          $item->save();
          //Yii::$app->session->setFlash('success','Данные приняты');
          return $this->redirect('/settings/common_fields');
        }
      }else{

        $item = new Settings__common_fields();
        if ($item->load($this->request->post())) {
          $item->insert();
        }

        if ($return = $this->request->post('return')) return $this->redirect($return);

        return $this->redirect('/settings/common_fields');
      }

    }else{
      $item = new Settings__common_fields();

    }


    $item_res = Settings__common_fields::find()->where(['id' => $id])->limit(1)->one();


    return $this->render('common_field_edit',[
      'item' => $item,
      'item_res' => $item_res,
      'return' => $this->request->get('return')
    ]);

  }
  public function actionCommon_field_delete(){
    $id = false;
    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!$id || !Yii::$app->user->can('admin')) exit;

    $item = Settings__common_fields::findOne($id);
    $item->delete();

    Yii::$app->session->setFlash('success','Удалено');
    return $this->redirect('/settings/common_fields');

  }
////////////// Common_field */

/************* Fields_extend_price */
  public function actionFields_extend_price(){

    $s = new Settings__fields_extend_price();
    $query = $s->find();
    $query = Settings__fields_extend_price::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 100,
      ],
      'sort' => [
        'defaultOrder' => [
          'source_id' => SORT_ASC,
          'id' => SORT_ASC,
        ]
      ],
    ]);

    return $this->render('settings__fields_extend_price', [
      'dataProvider' => $dataProvider,
    ]);
  }

  public function actionFields_extend_price_edit(){
    $source_id = $this->request->get('source_id',false);

    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!Yii::$app->user->can('admin')) exit;


    if ($this->request->getIsPost()){
      $id = $this->request->post('Settings__fields_extend_price')['id'];
      if (isset($id) && $id){
        $item = Settings__fields_extend_price::findOne($id);

        if ($item->load($this->request->post())){
          $item->save();
          //Yii::$app->session->setFlash('success','Данные приняты');
          return $this->redirect('/settings/fields_extend_price');
        }
      }else{

        $item = new Settings__fields_extend_price();
        if ($item->load($this->request->post())) {
          $item->insert();
        }

        return $this->redirect('/settings/fields_extend_price');
      }

    }else{
      $item = new Settings__fields_extend_price();

    }

    $item_res = Settings__fields_extend_price::find()->where(['id' => $id])->limit(1)->one();

    $source_list_formatted = [];
    $source_list = Source::find()->all();
    foreach ($source_list as $k => $v){
      $source_list_formatted[$v->id] = $v->name . (($v->table_1)? ' ['.$v->table_1.']' : '') . (($v->table_2)? ' ['.$v->table_2.']' : '');
    }
    unset($k);
    unset($v);

    $source_class = Source::get_source($source_id ?: 1)['source_class'];

    // тяжкий запрос! возможно надо будет переделать
    $cnt = $source_class::find()->count();
    $q = $source_class::find()->select('id, info')->asArray();
    if ($cnt > 100){
      $q->limit(100);
    }
    $all_p = $q->all();

    $keys = [];
    foreach ($all_p as $p){
      $_baseInfo = Json::decode($p['info'], true) ?: ['add_info' => '[]'];
      foreach ($_baseInfo as $k => $item_) {
        $keys[$k] = $k;
      }
    }

    $fields_in_source = $keys;

    return $this->render('fields_extend_price_edit',[
      'item' => $item,
      'item_res' => $item_res,
      'source_list' => $source_list_formatted,
      'source_id' => $source_id,
      //'source_id_for_input' => $source_id ?: $item_res->source_id,
      'fields_in_source' => $fields_in_source,
    ]);

  }

////////////// Fields_extend_price */




/************** Source EBAY CHINA GOOGLE */
  public function actionSource_edit(){

    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!Yii::$app->user->can('admin')) exit;

    if ($this->request->getIsPost()){
      $id = $this->request->post('Source')['id'];
      if (isset($id) && $id){
        $item = Source::findOne($id);

        if ($item->load($this->request->post())){
          $item->save();
          //Yii::$app->session->setFlash('success','Данные приняты');
          return $this->redirect('/settings/source_list');
        }
      }else{
        $item = new Source();
        if ($item->load($this->request->post())) {
          $item->insert();
        }
        return $this->redirect('/settings/source_list');
      }

    }else{
      $item = new Source();

    }

    $item_res = Source::find()->where(['id' => $id])->limit(1)->one();

    return $this->render('source_edit',[
      'item' => $item,
      'item_res' => $item_res,
    ]);

  }
  public function actionSource_delete(){
    $id = false;
    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!$id || !Yii::$app->user->can('admin')) exit;

    $item = Source::findOne($id);
    $item->delete();

    Yii::$app->session->setFlash('success','Удалено');
    return $this->redirect('/settings/source_list');

  }
/////////////// Source EBAY CHINA GOOGLE */


/************** Source_fields*/
  public function actionSource_fields_edit(){

    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!Yii::$app->user->can('admin')) exit;

    if ($this->request->getIsPost()){
      $id = $this->request->post('Settings__source_fields')['id'];

      if (isset($id) && $id){
        $item = Settings__source_fields::findOne($id);

        if ($item->load($this->request->post())){
          $item->save();
          //Yii::$app->session->setFlash('success','Данные приняты');
          return $this->redirect('/settings/source_fields');
        }
      }else{
        $item = new Settings__source_fields();
        if ($item->load($this->request->post())) {
          $item->insert();
        }
        return $this->redirect('/settings/source_fields');
      }

    }else{
      $item = new Settings__source_fields();

    }


    $item_res = Settings__source_fields::find()->where(['id' => $id])->limit(1)->one();


    $common_fields_formatted = [];
    $common_fields = Settings__common_fields::find()->all();
    // форматируем к такому виду ['0' => 'User не видит', '1' => 'User видит',]
    foreach ($common_fields as $k =>$v){
      $common_fields_formatted[$v->id] = $v->name . (($v->description)? ' ('.$v->description.')' : '');
    }
    unset($k);
    unset($v);

    $source_list_formatted = [];
    $source_list = Source::find()->all();
    foreach ($source_list as $k => $v){
      $source_list_formatted[$v->id] = $v->name . (($v->table_1)? ' ['.$v->table_1.']' : '') . (($v->table_2)? ' ['.$v->table_2.']' : '');
    }
    unset($k);
    unset($v);

    $type_list_formatted = [];
    $type_list = ['main' => 'относится к главному товару','compare' => 'относится к сравниваемому товару'];

    return $this->render('source_fields_edit',[
      'item' => $item,
      'item_res' => $item_res,
      'common_fields' => $common_fields_formatted,
      'source_list' => $source_list_formatted,
      'type_list' => $type_list
    ]);

  }
  public function actionSource_fields(){

    $s = new Settings__source_fields();
    $query = $s->find();
    $query = Settings__source_fields::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 100,
      ],
      'sort' => [
        'defaultOrder' => [
          'settings__common_fields_id' => SORT_ASC,
        ]
      ],
    ]);

    return $this->render('settings__source_fields', [
      'dataProvider' => $dataProvider,
    ]);


  }
  public function actionIndex(){

    $s = new Settings__list();
    $query = $s->find();
    $query = Settings__list::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 10,
      ],
      'sort' => [
        'defaultOrder' => [
          'position' => SORT_ASC,
        ]
      ],
    ]);

    return $this->render('index', [
      'dataProvider' => $dataProvider,
    ]);

  }
  public function actionSource_fields_delete(){
    $id = false;
    if ($this->request->getIsGet()){
      $id = $this->request->get('id',false);
    }
    if (!$id || !Yii::$app->user->can('admin')) exit;

    $item = Settings__source_fields::findOne($id);
    $item->delete();

    Yii::$app->session->setFlash('success','Удалено');
    return $this->redirect('/settings/source_fields');

  }
/////////////// Source_fields*/

  public function actionAjax(){
    $model = Settings__common_fields::find();
    $model->where(['like', 'name', Yii::$app->request->get('search')]);
    $items = $model->select('name')->limit(10)->asArray()->column();

    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $items;
  }


}