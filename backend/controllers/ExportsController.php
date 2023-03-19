<?php

namespace backend\controllers;

use backend\models\Exports__saved_keys;
use common\models\Source;
use backend\models\Stats__import_export;
use common\models\Comparison;
use common\models\Product;
use Yii;
use yii\db\ActiveRecord;
use yii\web\Controller;
use common\models\Stats_import_export;
use common\models\Filters;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class ExportsController extends Controller
{
  public $source_id;
  /**
   * @var Product
   */
  public $source_class;
  public $source_table_name;
  public $source_table_name_2;

  public function actionIndex()
  {


    // вывести таблицу список источников
    // [X] ebay   [ ] china   [ ] google
    // ---------------------------------
    // [X] match  [ ] mismatch  [ ] other  [ ] nocompare
    // [Далее→]   /exports/step_2?source_id=1&compare=match

    //  /exports/step_2


    return $this->render('index', [
      'dataProvider' => [],
    ]);
  }

  public function actionStep_2()
  {
    $data = $this->request->post('DynamicModel');
    $source_id = $data['source_id'];
    $comparisons = $data['comparisons'];
    $this->get_source($source_id);

    $q = $this->source_class::find()->distinct(true)->select(['profile'])->asArray();

    $profiles_list = Product::profiles_list($source_id);

    return $this->render('exports_step_2', [
      'data' => $data,
      'source_id' => $source_id,
      'profiles_list' => $profiles_list,
    ]);
  }



  public function actionStep_3()
  {

    $data = $this->request->post('DynamicModel');
    $ignore_step_3 = $data['ignore_step_3'];
    $source_id = $data['source_id'];
    $profile = $data['profile'];
    $is_new = $data['is_new'];

    $comparisons = $data['comparisons'];
    if (!$source_id || !$comparisons) {
      echo '<pre>' . PHP_EOL;
      print_r('нет source_id или comparisons');
      echo PHP_EOL;
      exit;
    }

    $this->get_source($source_id);


    //     [DynamicModel] => Array
    //        (
    //            [source_id] => 1
    //            [comparisons] => Array
    //                (
    //                    [0] => match
    //                )
    //
    //        )

    // todo:: вытягиваем сохраненные ранее для этого источника ключи

    // таблица ключей
    $amazon_keys = [];
    $source_data_keys = [];
    $table_items = [];


    $q_ = Exports__saved_keys::find()
      ->where(['source_id' => $source_id])
      ->orderBy(['position' => SORT_ASC]);
    $res = $q_->all();

    // [use_previous_saved] => 0
    if (!$data['use_previous_saved'] || !$res) { // создаем заново

      $q = $this->source_class::find()
        //->select('*')
        ->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id = ' . $this->source_table_name . '.id')
        ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source_table_name . '.id ')
        ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source_table_name . '.id ')
        ->leftJoin('p_updated', 'p_updated.p_id = ' . $this->source_table_name . '.id ')
        ->leftJoin('comparisons', 'comparisons.product_id = ' . $this->source_table_name . '.id ')
        ->leftJoin('messages', 'messages.id = comparisons.messages_id')

        //      ->where([$this->source_table_name.'.ASIN' => 'B0012NGQS4'])
      ;
      $q->limit(1000);
      $res = $q->all();

      if (!$res) {
        echo '<pre>' . PHP_EOL;
        print_r('-------');
        echo PHP_EOL;
        exit;
      }

      foreach ($res as $k => $item) {
        $getBaseInfo = $item->getBaseInfo() ?? [];
        $amazon_keys = array_unique(array_merge(array_keys($getBaseInfo), $amazon_keys));
        $source_data_keys = array_unique(array_merge(array_values($item->getAddInfo() ? $item->getAddInfo()[0]->attributes() : []), $source_data_keys));
      }

      $amazon_keys = $this->format_keys($amazon_keys, $source_id, 'left_item');
      $source_data_keys = $this->format_keys($source_data_keys, $source_id, 'right_item');

      $table_items = array_merge($amazon_keys, $source_data_keys);

      Exports__saved_keys::deleteAll(['source_id' => $source_id]);

      foreach ($table_items as $k => $t_item) {

        if ($t_item['name'] !== 'parent_item') {
          $e_insert = new Exports__saved_keys();
          $e_insert->name = $t_item['name'];
          $e_insert->type = $t_item['type'];
          $e_insert->selected = $t_item['selected'];
          $e_insert->source_id = $t_item['source_id'];
          $e_insert->position = $k;
          $e_insert->insert();
        }
      }

      /*    [id] => 1
      [name] => url_ebay
      [source_id] => 1
      [type] => left_item
      [selected] => 0
      [position] => 0
*/

      $q_ = Exports__saved_keys::find()
        ->where(['source_id' => $source_id])
        ->orderBy(['position' => SORT_ASC]);
      $res = $q_->all();
    } // if (!$data['use_previous_saved'])

    $table_items_out = [];
    if ($res) {
      foreach ($res as $k => $db_item) {
        foreach ($db_item as $db_k => $db_value)
          $table_items_out[$k][$db_k] = $db_value;
      }
    }

    return $this->render('exports_step_3_compare_table', [
      //      'amazon_keys' => $amazon_keys,
      //      'source_data_keys' => $source_data_keys,
      'table_items' => $table_items_out,
      'comparisons' => $comparisons,
      'source_id' => $source_id,
      'profile' => $profile,
      'ignore_step_3' => $ignore_step_3,
      'is_new' => $is_new === 'is_new',
    ]);

    // [Экспортировать→]

  }

  public function actionSelect_one()
  {
    $source_id = $this->request->post('source_id');
    $checked = $this->request->post('checked');
    $id = $this->request->post('id');


    $e = Exports__saved_keys::findOne(['id' => $id]);
    $e->selected = $checked;
    $e->update();

    // json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];
  }

  public function actionSelect_all()
  {
    $source_id = $this->request->post('source_id');
    $checked = $this->request->post('checked');

    Exports__saved_keys::updateAll(['source_id' => $source_id, 'selected' => $checked], 'source_id = ' . $source_id);

    // json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];
  }

  public function actionChange_position()
  {
    $data = $this->request->post('id_position');

    if ($data)
      foreach ($data as $item) {
        $e = Exports__saved_keys::findOne(['id' => $item['id']]);
        $e->position = $item['position'];
        $e->save();
      }

    // json
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
      'res' => 'ok',
    ];
  }


  /**
   * @param $comparison
   * @return \yii\db\ActiveQuery
   */
  private function prepare_record_1($comparison)
  {

    /* @var $source_class yii\db\ActiveRecord */
    $source_class = $this->source_class;

    $q = $source_class::find()
      ->select('*')
      ->addSelect($this->source_table_name . '.id as id')
      //->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
      ->leftJoin('hidden_items', 'hidden_items.p_id = ' . $this->source_table_name . '.id ')
      ->leftJoin('p_all_compare', 'p_all_compare.p_id = ' . $this->source_table_name . '.id ')
      ->leftJoin('comparisons', 'comparisons.product_id = ' . $this->source_table_name . '.id ');

    //->where(['<=>','hidden_items.source_id', $this->source_id])
    //->where('comparisons_aggregated.source_id = '.(int)$this->source_id );


    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $q->andWhere("info NOT LIKE '%\"add_info\":\"[]\"%'");
      $q->andWhere("info NOT LIKE '%\"add_info\": \"[]\"%'");
    } else {
      //$q->innerJoin($this->source_table_name_2,$this->source_table_name_2.'.`asin` = '.$this->source_table_name.'.asin');
    }

    //$q->addGroupBy('`'.$this->source_table_name.'`.`id`');


    $where_0 = [];
    if (0 && $this->source_table_name === 'parser_trademarkia_com') {
      $where_0 = ['like', 'info', 'add_info'];
    }

    //$where_2 = [];
    $compareCondition = ['or'];
    // $where_3 = [
    //   'and',
    //   ['hidden_items.p_id' => null],
    //   ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]],
    // ];  
    // $item_1__ignore_red = 1

    foreach ($comparison as $c) {
      switch ($c) {
        case 'YES_NO_OTHER':
          $compareCondition[] = "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'";
          break;
        case 'NOCOMPARE':
          $compareCondition[] = ['and', ['p_all_compare.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]]];
          break;
        default:
          $compareCondition[] = ['`comparisons`.`status`' => $comparison];
      }
    }

    // if ($comparison === 'ALL') {
    // } elseif ($comparison === 'ALL_WITH_NOT_FOUND') {

    //   $where_3 = [];
    // } elseif ($comparison === 'YES_NO_OTHER') {
    //   $where_2 =
    //     [
    //       'OR',
    //       ['`comparisons`.`status`' => 'MATCH'],
    //       ['`comparisons`.`status`' => 'MISMATCH'],
    //       ['`comparisons`.`status`' => 'OTHER'],
    //       ['`comparisons`.`status`' => 'PRE_MATCH'],
    //     ];
    //   $where_2 = ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH'"];
    // } elseif ($comparison === 'NOCOMPARE') {
    //   $where_2 = ['and', ['p_all_compare.p_id' => null], ['OR', ['hidden_items.source_id' => null], ['<>', 'hidden_items.source_id', $this->source_id]]];
    // } else {
    //   $where_2 = ['`comparisons`.`status`' => $comparison];
    // }

    //$where = ['and',  $where_0, $compareCondition];
    $q->andWhere(['and',  $where_0, $compareCondition]);

    return $q;
  }

  public function actionStep_4()
  {
    // http://checker.loc/exports/step_4?source_id=2&comparisons[]=match&profile={{all}}

    $method = 'POST';
    /*
Array (   [0] => Array (
                    [id] => 119
                    [checked] => 0
                    [name] => Locale
                    [type] => left_item
        ) [1] => Array (
                    [id] => 123
                    [checked] => 1
                    [name] => Title
                    [type] => left_item
        ) ... */
    $ids_keys = $this->request->post('items');
    $source_id = $this->request->post('source_id');
    $comparison = $this->request->post('comparisons');
    $profile = $this->request->post('profile');
    $is_new = !!(int)$this->request->post('is_new');
    $filtered = false;
    $ids = [];

    if (!$ids_keys || !$source_id || !$comparison || !$profile) {
      // http://checker.loc/exports/step_4?source_id=2&comparisons[]=match&profile={{all}}

      $source_id = $this->request->get('source_id');
      $comparison = $this->request->get('comparisons') ?? [];
      $profile = $this->request->get('profile');
      $filtered = $this->request->get('filtered') === 'true';
      $ids_keys = $this->get_keys_from_db($source_id);
      $is_new = !!(int)$this->request->get('is_new');
      $method = 'GET';
    }

    $comparison = is_string($comparison) ? unserialize($comparison) : $comparison;
    $comparison = array_map(fn ($c) => strtoupper($c), $comparison);

    if (count($comparison) == 1 && ($comparison[0] === 'NOCOMPARE' || $comparison[0] === 'ALL')) {
      echo '<pre>' . PHP_EOL;
      print_r($comparison[0] . ' не поддерживается, выберите другой вариант');
      echo PHP_EOL;
      exit;
    }

    if (!$ids_keys) {
      echo '<pre>' . PHP_EOL;
      print_r('нет $ids_keys');
      echo PHP_EOL;
      exit;
    }

    if (!$source_id || empty($comparison)) {
      echo '<pre>' . PHP_EOL;
      print_r('нет source_id или comparisons');
      echo PHP_EOL;
      exit;
    }

    $this->get_source($source_id);
    if ($filtered) {
      $filters = new Filters();
      $filters->loadFromSession();
      $list = Product::getListProducts(Source::getById($source_id), $filters, \Yii::$app->user->identity->isAdmin());
      $ids = array_map(fn ($p) => $p->id, $list);
    }

    /*
    $q = $this->source_class::find()
      ->select('*')
      //->leftJoin('comparisons_aggregated','comparisons_aggregated.product_id = '.$this->source_table_name.'.id')
      ->leftJoin('comparisons','comparisons.product_id = '.$this->source_table_name.'.id ')
    ;
    if ($comparison === 'YES_NO_OTHER'){
      $where_2 = ['and', "`comparisons`.`status` IS NOT NULL AND comparisons.`status` <> 'MISMATCH' AND '`comparisons`.`source_id`' = '$source_id'"];
    }else{
      $q->where(['comparisons.status' => $comparison, 'comparisons.source_id' => $source_id]);
    }
*/
    $q = $this->prepare_record_1($comparison);

    if (trim($profile) && $profile !== '{{all}}') {
      $q->andWhere(['like', $this->source_table_name . '.`profile`', $profile]);
    }

    $last_import = Stats_import_export::getLastLocalImport($source_id);
    if ($is_new && $last_import) {
      if ($source_id == 1) {
        $q->andWhere(['date_update' => $last_import->created]);
      } else {
        $q->andWhere(['parse_at' => $last_import->created]);
      }
    }

    if (!empty($ids)) {
      $q->andWhere(['IN', $this->source_table_name . '.id', $ids]);
    }
    $q->asArray();

    //var_dump($q->createCommand()->rawSql); die;
    $connection = Yii::$app->getDb();
    $command = $connection->createCommand($q->createCommand()->getRawSql());
    $res = $command->queryAll();

    $out = [];

    foreach ($res as $k => $r) {

      $_array = [];
      $id = $r['id'];
      $node_id = $r['node'];
      $res_ = $this->source_class::findOne(['id' => $id]);

      if (!$res_) {
        $addInfo = [];
      } else {
        $addInfo = $res_->getAddInfo();
      }

      $item = $addInfo[$node_id];
      $r['item_right'] = $item;
      $out[] = $r;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();


    /*
    $sheet->fromArray(
      [1, 2, 3],
      null,
      'A1'
    );
*/
    unset($item);

    foreach ($out as $item) { // row
      $itm = $item['item_right'];

      $cell = 1;
      foreach ($ids_keys as $id_key) { // cell
        if ((int)$id_key['checked'] === 1) {
          $key_name = $id_key['name'];
          $val = $key_name;

          $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cell, 1, $val);
          $range_1 = $spreadsheet->getActiveSheetIndex();


          $coo = $spreadsheet->getActiveSheet()->getCellByColumnAndRow($cell, 1)->getCoordinate();

          if ($id_key['type'] === 'left_item') {
            $style_2 = 1;

            $color = new \PhpOffice\PhpSpreadsheet\Style\Color();
            $spreadsheet->getActiveSheet()->getStyle($coo . ":" . $coo)->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->setStartColor($color->setRGB('E0E000'));
          }
          if ($id_key['type'] === 'right_item') {
            $style_1 = 2;

            $color = new \PhpOffice\PhpSpreadsheet\Style\Color();
            $spreadsheet->getActiveSheet()->getStyle($coo . ":" . $coo)->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->setStartColor($color->setRGB('04AC00'));
          }

          //echo '<pre>'.PHP_EOL;
          //print_r($activeCell->getStyle()->getFill()->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_GREEN));
          //echo PHP_EOL;
          $cell++;
        }
      }

      //      $start = $spreadsheet->getActiveSheet()->getCell([1,1])->getCoordinate();
      //      $end = $spreadsheet->getActiveSheet()->getCell([$cell,1])->getCoordinate();
      //      $spreadsheet->getActiveSheet()->getStyle($start.':'.$end)->getFill()
      //        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
      //        ->getStartColor()->setARGB('FFFF0000');
    }
    /*
    $conditional2 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
    $conditional2->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
    $conditional2->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
    $conditional2->addCondition(10);
    $conditional2->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED);
    $conditional2->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $conditional2->getStyle()->getFill()->getStartColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

    //$conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('A1:A10')->getConditionalStyles();
    $conditionalStyles[] = $conditional2;
    $spreadsheet->getActiveSheet()->getStyle('A1:A10')->setConditionalStyles($conditionalStyles);
    */

    $row = 2;
    foreach ($out as $item) { // row
      $itm = $item['item_right'];

      $cell = 1;
      foreach ($ids_keys as $id_key) { // cell

        if ((int)$id_key['checked'] === 1) {
          $key_name = $id_key['name'];

          $val = '---';
          if ($id_key['type'] === 'right_item') {
              $val = $itm->$key_name;
          }
          if ($id_key['type'] === 'left_item') {
              $val = $itm['parent_item'][$key_name];
          }

          if (is_array($val)) $val = 'ARRAY!!!';
          if (is_object($val)) $val = 'OBJECT!!!';

          $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($cell, $row, $val);
          $cell++;
        }
      }
      $row++;
    }

    // При выгрузке даенных в файл, в имени файла писать:
    // Источник.check.profile_дд.мм.гггг._кол.записей,
    // напимер:
    // Ebay.check.general_21.06.2022_11, profile пишем если он был выбран

    // EBAY.MATCH.2022_07_01__12_40.27.xlsx
    $source_name = Source::get_source($source_id)['source_name'];
    $comparison_db = $comparison;
    if ($comparison === 'YES_NO_OTHER') $comparison = 'RESULT';
    $_comparison = '_' . $comparison ?: '';
    $_profile = $profile ?: '';
    if ($_profile === '{{all}}') $_profile = '_ALL';
    else $_profile = '_' . $_profile;
    $date = '_' . date('Y.m.d_H.i', time());
    $cnt_items = '_' . ($row - 2);

    // EBAY_MATCH_All_2022.07.02_12.59_19

    $filename = $source_name . $_comparison . $_profile . $date . $cnt_items . '.xlsx';

    $stat_log = new Stats__import_export();
    $stat_log->type = 'EXPORT';
    $stat_log->file_name = $filename;
    $stat_log->comparison = $comparison_db;
    $stat_log->cnt = $row - 2;
    //$stat_log->raw = json_encode($out);
    $stat_log->raw = '';
    $stat_log->source_id = $source_id;
    $stat_log->profile = $profile;
    $stat_log->created = date('Y-m-d H:i:s', time());
    $stat_log->insert();


    $writer = new Xlsx($spreadsheet);
    $writer->save('export/' . $filename);


    if ($method === 'POST') {
      // json
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return [
        'res' => 'ok',
        'file' => 'export/' . $filename
      ];
    }

    if ($method === 'GET') {
      //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; filename="''"');
      header("Location: /export/" . $filename);
      //header('Content-Disposition: attachment; filename="'.$filename.'"');
    }

    // https://www.codexworld.com/export-data-to-excel-in-php/
    exit;
  }

  private function format_keys($data, $source_id, $type)
  {
    /*
    * [id] => 1
      [name] => url_ebay
      [source_id] => 1
      [type] => left_item
      [selected] => 0
      [position] => 0
     * */

    $out = [];
    foreach ($data as $k => $value) {
      $out[$k]['id'] = -1;
      $out[$k]['name'] = $value;
      $out[$k]['source_id'] = $source_id;
      $out[$k]['type'] = $type;
      $out[$k]['selected'] = 0;
      $out[$k]['position'] = 0;
    }
    return $out;
  }

  private function get_source($source_id)
  {
    $s = Source::get_source($source_id);
    if ($s) {
      $this->source_id = $s['source_id'];
      $this->source_class = $s['source_class'];
      $this->source_table_name = $s['source_table_name'];
      $this->source_table_name_2 = $s['source_table_name_2'];
    }
  }

  private function get_keys_from_db($source_id)
  {
    $out = [];
    $res = Exports__saved_keys::find()->where(['source_id' => $source_id])->orderBy(['position' => SORT_ASC])->all();
    if ($res) {
      foreach ($res as $k => $item) {
        $_arr['id'] = $item->id;
        $_arr['checked'] = $item->selected;
        $_arr['name'] = $item->name;
        $_arr['type'] = $item->type;

        $out[] = $_arr;
      }
    }

    return $out;
  }
}
