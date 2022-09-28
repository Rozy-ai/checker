<?php

namespace backend\controllers;



use backend\models\Articles;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;



class ArticlesController extends Controller{
  
  public function actionIndex(){

    $s = new Articles();
    $query = $s->find();
    $query = Articles::find();


    $dataProvider = new ActiveDataProvider([
      'query' => $query,

      'pagination' => [
        'pageSize' => 10,
      ],

      'sort' => [
        'defaultOrder' => [
          'id' => SORT_ASC,
        ]
      ],
    ]);


    return $this->render('index',[
      'dataProvider' => $dataProvider
    ]);
  }
  
  public function actionEdit(){
//    //echo '<pre>'.PHP_EOL;
//    print_r($this->request->post('Articles'));
//
//    //echo PHP_EOL;
//    exit;

//    $model = new Articles();
//    $model->load( Yii::$app->request->post() );
//    $model->date = date('Y-m-d H:i:s',time());
//    $model->save();

    $post = $this->request->post('Articles');

    $save = false;
    if ($this->request->isPost){
      $post_id = $post['id'];
      $title = $post['title']?: 'Без названия';
      $html = $post['html'];

      if ($post_id) {
        $s = Articles::findOne(['id' => $post_id]);
        $s->title = $title;
        $s->html = $html;
        $s->save();

        $save = true;
        return $this->redirect('/articles');
      }


      if (!$post_id || !Articles::findOne(['id' => $post_id]) && !$save){
        $s = new Articles();
        $s->title = $title;
        $s->html = $html;
        $s->date = date('Y-m-d H:i:s',time());
        $s->save();
        return $this->redirect('/articles');
      }
    }

    $id = $this->request->get('id',false);
    //if (isset($l_id)) $id = $l_id;

    if (!$id) return $this->redirect('/articles');
    $item = Articles::findOne(['id' => $id]);

    if (!$item) {
      $item = new Articles();
    }

    return $this->render('edit',[
      'item' => $item
    ]);
  }

  public function actionDel(){
    $id = $this->request->get('id',false);
    if (!$id) return $this->redirect('/');

    $a = Articles::findOne(['id' => $id]);
    if ($a) $a->delete();
    return $this->redirect('/articles');
  }
  public function actionItem(){
    $id = $this->request->get('id',false);
    if (!$id) return $this->redirect('/');

  }
}