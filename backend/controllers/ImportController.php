<?php

namespace backend\controllers;

use backend\models\P_all_compare;
use backend\models\P_updated;
use common\models\Source;
use backend\models\Stats__import_export;
use common\models\Comparison;
use common\models\HiddenItems;
use common\models\Product;
use common\models\Stats_import_export;
use backend\controllers\StatsController;
use PDO;
use PDOException;
use phpDocumentor\Reflection\Types\Null_;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;

class ImportController extends \yii\web\Controller
{
  private $db_import_name = 'checker_import';

  static private $db;
  static private $db2;

  public static function get_max_product_date_in_parser($source)
  {
    //    echo '<pre>'.PHP_EOL;
    //    print_r($source);
    //    print_r($source->import_local__db_import_name);
    //
    $db_name = $source['import_local__db_import_name'];
    $tbl_name = $source->table_1;
    if (!$db_name || !$tbl_name) return false;

    $db_name__tbl_name = '`' . $db_name . '`.`' . $tbl_name . '`';

    $sql = 'SELECT * FROM ' . $db_name__tbl_name . ' ORDER BY ' . $db_name__tbl_name . '.`date_add` DESC LIMIT 1';

    // SELECT * FROM `ebay`.`parser_trademarkia_com` ORDER BY `ebay`.`parser_trademarkia_com`.`date_add` DESC LIMIT 1
    $res_1 = self::sql_cmd($sql, [], 'select');

    if ($res_1) {
      return $res_1[0]['date_add'];
    }


    return false;
  }



  public function actionGet_connection()
  {
    /*
    // _definitions:yii\di\ServiceLocator:private
    echo '<pre>'.PHP_EOL;
    //print_r(Yii::$app->getDb());
    print_r(Yii::$app->getComponents()['db']);
    echo PHP_EOL;

    print_r(self::get_config());
//    print_r(Yii::$app->getDb()->password);
//    print_r(Yii::$app->getDb()->dsn);
    echo PHP_EOL;
    exit;
    */
  }

  /** берем user password из основного конфига
   * @param int $v
   * @return array
   */
  public static function get_config($v = 1)
  {

    $out = [];
    $out['host'] = \Yii::$app->params['dbHost'];

    /* v1  без указания БД
      'host' => 'localhost',
      'user' => 'XXXX_user',
      'password' => 'XXXXXXXXXXXX',
     */

    if ((int)$v === 1) {
      /* излишне
      $s1 = explode(';',Yii::$app->getComponents()['db']['dsn']);
      foreach ($s1 as $value){
        $s2 = explode('=',$value);
        if ($s2[0] === 'mysql:host') $s2[0] = 'host';
        $out[$s2[0]] = $s2[1];
      }
      */
      $out['user'] = Yii::$app->getComponents()['db']['username'];
      $out['password'] = Yii::$app->getComponents()['db']['password'];
    }

    /* v2   с указанием БД
      'host' => 'localhost',
      'dbname' => 'checker_import',
      'user' => 'XXXX_user',
      'password' => 'XXXXXXXXXXXX',
     */

    if ((int)$v == 2) {
      $out['user'] = Yii::$app->getComponents()['db']['username'];
      $out['password'] = Yii::$app->getComponents()['db']['password'];
      $out['dbname'] = \Yii::$app->params['dbname'];
    }

    return $out;
  }

  public static function getConnection($db = null)
  {
    if (!empty($db)) {
      self::$db = $db;
      return $db;
    } else {
      $params = self::get_config(1);
      $dsn = "mysql:host={$params['host']};dbname={$params['dbname']}";

      try {
        $db = new PDO($dsn, $params['user'], $params['password'] );
        //$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        //$db->exec("SET CHARSET utf8");
        //$db->exec("set names utf-8");

      } catch ( PDOException $e) {
        if ($isAjax) {            
            $data = array('error'=>true, 'message' => 'Подключение к источнику данных не удалось: ' . $e->getMessage());             
            echo json_encode($data);
            die();     
        } else {  
            echo '<pre>' . PHP_EOL;
            print_r('Подключение не удалось: ' . $e->getMessage());
            echo PHP_EOL;
            exit;
        }    
      }

      self::$db = $db;
      return $db;
    }
  }

  public static function getConnection_2($db2 = null)
  {
    if (!empty($db2)) {
      self::$db2 = $db2;
      return $db2;
    } else {
      $params = self::get_config(2);
      $dsn = "mysql:host={$params['host']};dbname={$params['dbname']}";

      try {
        $db2 = new PDO($dsn, $params['user'], $params['password']);
        $db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db2->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $db2->exec("SET CHARSET utf8");
        

        //$db->exec("set names utf-8");

      } catch (PDOException $e) {
        echo '<pre>' . PHP_EOL;
        print_r('Подключение не удалось: ' . $e->getMessage());
        echo PHP_EOL;
        exit;
      }

      self::$db2 = $db2;
      return $db2;
    }
  }

  public function __construct($id, $module, $cnf)
  {
    parent::__construct($id, $module, $cnf);
  }

  private function drop_tables_by_source($source)
  {
    $tbl_1 = $source['source_table_name'];
    $tbl_2 = $source['source_table_name_2'];
    if ($tbl_2) $this->drop_table($tbl_2);
    if ($tbl_1) $this->drop_table($tbl_1);
  }

  public function actionStep_2()
  {
    $dynamicModel = $this->request->post('DynamicModel');
    $source_id = $dynamicModel['source_id'] ?? Null;

    if (!$source_id) $source_id = $this->request->post('source_id', false);
    if (!$source_id) $source_id = $this->request->get('source_id', false);
    if (!$source_id) {
      echo '<pre>' . PHP_EOL;
      print_r('нет $source_id');
      echo PHP_EOL;
      exit;
    }

    $source = Source::get_source($source_id);

    $q_1 = $dynamicModel['q_1'] ?? Null;
    if (!$q_1) {
      $q_1 = $source['import__default_q_1'] ?? 'IGNORE';
    }


    if ($this->request->post('use_import_sql_file_path', false)) {
      $path_file = $source['import__sql_file_path'];
    } else {
      // загружаем sql фаил в папку tmp
      $f = UploadedFile::getInstanceByName('DynamicModel[load_file]');
      
      if (is_dir('/tmp/')) {
        $path = '/tmp/';
      } else {
        $path = __DIR__ . '/../tmp/';  
      }
      
      if (!is_dir($path)) {
        $res_mkdir = mkdir($path, 0777, true);
        if (!$res_mkdir) {
            echo '<pre>' . PHP_EOL;
            print_r('не указан $source_id');
            echo PHP_EOL;
            exit;
        }
      }
      
      $path_file = $path . strtolower($source['source_name']) . '.' . $f->extension;
      $f->saveAs($path_file);
    }

    // удаляем таблицы из checker_import если они были
    //    $this->drop_tables_by_source($source);



    // импортируем таблицы
    //    $use_1 = 'USE ' . $this->db_import_name . ';' . PHP_EOL;
    //    $file_sql = $use_1;
    //    $file_sql .= file_get_contents($path_file);
    $file_sql = file_get_contents($path_file);      // todo ????



    // todo разбиваем фаил на части
    $explode_sql = function ($file_sql, $source) {
      $tbl_1 = $source['source_table_name'];
      $tbl_2 = $source['source_table_name_2'];
      $create_str = 'CREATE TABLE `' . $tbl_1 . '`';
      $insert_str = 'INSERT INTO `' . $tbl_1 . '`';

      $array[] = $file_sql;
      $exploder = function ($delimiter_str, $array) {
        $out = [];

        foreach ($array as $a) {
          $_array = explode($delimiter_str, $a);

          foreach ($_array as $k1 => $part) {
            if ((int)$k1 > 0) $part = $delimiter_str . $part;
            $out[] = $part;
          }
        }

        return $out;
      };

      $create_str = 'CREATE TABLE `' . $tbl_1 . '`';
      $step_1 = $exploder($create_str, $array);
      $create_str = 'CREATE TABLE `' . $tbl_2 . '`';
      $step_2 = $exploder($create_str, $step_1);
      $insert_str = 'INSERT INTO `' . $tbl_1 . '`';
      $step_3 = $exploder($insert_str, $step_2);
      $insert_str = 'INSERT INTO `' . $tbl_2 . '`';
      $step_4 = $exploder($insert_str, $step_3);


      return $step_4;
    };

    $parts = $explode_sql($file_sql, $source);

    // удаляем таблицы из checker_import
    $this->drop_tables_by_source($source);

    foreach ($parts as $k => $part) {
      $r = $this->sql_cmd_2($part);
    }




    //    echo '<pre>'.PHP_EOL;
    //    print_r($r = $this->sql_cmd('SELECT * FROM checker.parser_trademarkia_com;'));
    //    echo PHP_EOL;
    //    exit;

    //$asin = 'B07YH8RPNY';
    //$sql = 'SELECT * FROM checker.parser_trademarkia_com WHERE asin = :asin';
    //    $sql = 'CREATE DATABASE IF NOT EXISTS `checker_import_2`';
    //    $r = $this->sql_cmd($sql);
    //    echo '<pre>'.PHP_EOL;
    //    print_r($r);
    //    echo PHP_EOL;
    //    exit;
    /*
    $db = self::getConnection();
    //$sql = $file;
    $stmt = $db->prepare('SELECT * FROM checker.parser_trademarkia_com WHERE asin = :asin');
    $stmt->bindParam(':asin',$asin);
    $stmt->execute();
    echo '<pre>'.PHP_EOL;
    foreach ($stmt->fetchAll(2) as $row) {
      print_r($row);
    }
    echo PHP_EOL;
*/

    //$res_execute = exec($cmd);

    // импортируем из checker_import в основную базу данных checker в зависимости от условия $q_1

    // todo :: удаляем фаил из временной папки
    $stat = $this->import_from_tmp_db($source, $q_1,time());



    // удаляем таблицы из checker_import
    $this->drop_tables_by_source($source);


    if (isset($f)) $file_name = $f->getBaseName() . '.' . $f->getExtension();
    else $file_name = 'ИМПОРТ ИЗ БД';

    self::save_stat($file_name, $stat['all'], $source_id, $stat);

    // считаем статистику и отправляем ее на import/result_statistics.php

    if ($this->request->post('use_import_sql_file_path', false)) {
      // json
      \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return [
        'res' => 'ok',
        'info' => $stat
      ];
    }


    return $this->render('result_statistics', [
      'stat' => $stat,
      'source_id' => $source_id,
      'source' => $source,
    ]);
  }

  public static function save_stat($file_name, $cnt, $source_id, $raw, $created_timestamp = null)
  {
    $stat_log = new Stats__import_export();
    $stat_log->type = 'IMPORT';
    $stat_log->file_name = $file_name;
    $stat_log->comparison = '';
    $stat_log->cnt = $cnt;
    $stat_log->raw = json_encode($raw);
    $stat_log->source_id = $source_id;
    $stat_log->profile = '';
    $stat_log->created = $created_timestamp ? date('Y-m-d H:i:s', $created_timestamp) : date('Y-m-d H:i:s', time());
    $stat_log->insert();
  }


  public function actionLocal_import($source_id = null, $p_date_in_parser = null)
  {
    $stop = 0;
    $isAjax = $this->request->isAjax ;
    
    if ( empty($source_id) ) {
        
      $source_id = $this->request->get('source_id', false);                        
      $stop = 1;
      
      if (!$source_id) {                
          if ($isAjax) {
             $data = array('error'=>true, 'message' => 'Не указан ид.источника ($source_id)');             
             echo json_encode($data);
             die();     
          } else {
             echo '<pre>' . PHP_EOL;
             print_r('не указан $source_id');
             echo PHP_EOL;
             exit;       
          }   
      }
    }

    $source = Source::get_source((int)$source_id);

    $q_1 = $source['import__default_q_1'] ?? 'IGNORE';
    $created_timestamp = time();

    $this->db_import_name = $source['import_local__db_import_name'];
    $stat = $this->import_from_tmp_db($source, $q_1, $created_timestamp);
    $this->getView()->params['local_import_stat'] = $stat;

    self::save_stat('LOCAL_IMPORT', $stat['all'], $source_id, $stat, $created_timestamp);

    $source_bo = Source::findOne(['id' => (int)$source_id]);
    
    if ($p_date_in_parser) {
        $source_bo->import_local__max_product_date = $p_date_in_parser;
        $source_bo->update();
    } else {
        $max_date = ImportController::get_max_product_date_in_parser(Source::findOne(['id' => (int)$source_id]));
        if ($source_bo->import_local__max_product_date !== $max_date) {
            $source_bo->import_local__max_product_date = $max_date;
            $source_bo->update();
        }
    }       
    
    if ($stop === 1) {
        if ($isAjax) {
            $data = array('error'=>true, 'message' => $message);             
            echo json_encode($data);
            die();            
        }else{    
            echo $message;
            echo '<pre>' . PHP_EOL;
            print_r('Готово');
            echo PHP_EOL;
            print_r($stat);
            echo PHP_EOL;
            exit;
        }         
    }        
            
    if ($isAjax) {   
        $data = array('error'=>false, 'message' => StatsController::getStatsLastLocalImportMessage());             
        echo json_encode($data);
        die();     
    } else {
        return $this->render('result_statistics', [
          'stat' => $stat,
          'source_id' => $source_id,
          'source' => $source,                      
        ]);
    }
  }



  public function drop_table($tbl_name)
  { //
    //    $tbl_name = 'parser_trademarkia_com';
    //    $command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
    //    $command->bindValue(':id', $_GET['id']);
    //    $post = $command->query();

    // небольшая проверка на уязвимость
    $res_source = Source::find()->where(['OR', ['table_1' => $tbl_name], ['table_2' => $tbl_name]])->one();

    if ($res_source) {
      $sql = 'DROP TABLE IF EXISTS ' . $this->db_import_name . '.' . $tbl_name;
      $res = $this->sql_cmd($sql);
    }
  }

  public static function sql_cmd($sql, $bind_array = [], $type = 'execute')
  {
    ///$sql = SELECT * FROM `ebay`.`parser_trademarkia_com` ORDER BY `ebay`.`parser_trademarkia_com`.`date_add` DESC LIMIT 1
    // $sql = "SELECT * FROM checker.$tbl_2 WHERE checker.$tbl_2.asin = :asin";
    // $res = $this->sql_cmd($sql,'query',[':asin' => $asin]);
    $db = self::getConnection();
    //$sql = $file;
    $stmt = $db->prepare($sql);

    if ($bind_array) {
      foreach ($bind_array as $k => $value) {
        $stmt->bindParam($k, $value);
      }
    }

    if ($stmt->execute()) {
      if ( $type === 'select' )  {
       $result = $stmt->fetchAll(2);       
        return $result;
      } else if ( $type === 'insert') {     
        return $stmt->rowCount();         
      }
    } 
    return [];

    /*
    $connection = new \yii\db\Connection($this->connect_config);
    $c = $connection->open();
    $command = $connection->createCommand($sql);

    if ($bind_array){
      foreach ($bind_array as $k => $value){
        $command->bindValue($k, $value);
      }
    }
*/

    //    $command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
    //    $command->bindValue(':id', $_GET['id']);
    //    $post = $command->query();

    //    if ($type === 'query'){
    //      return $posts = $command->queryAll();
    //    }else{
    //      return $posts = $command->execute();
    //    }

  }

  public static function sql_cmd_2($sql, $bind_array = [], $type = 'execute')
  {
    // $sql = "SELECT * FROM checker.$tbl_2 WHERE checker.$tbl_2.asin = :asin";
    // $res = $this->sql_cmd($sql,'query',[':asin' => $asin]);
    $db = self::getConnection_2();
    //$sql = $file;
    /*if ($type === 'exec') {

      try {
        $db->beginTransaction();
        $res = $db->query($sql);
        $db->commit();
      } catch (PDOException $e) {
      }

      return [];
    }*/
    $stmt = $db->prepare($sql);

    if ($bind_array) {
      foreach ($bind_array as $k => $value) {
        $stmt->bindParam($k, $value);
      }
    }

    if ($stmt->execute()) {
      if ( $type === 'select') {
         return $stmt->fetchAll(2);
      } else if ( $type === 'insert') {
         return $stmt->rowCount();          
      }
    }
    return [];
  }

  public function actionStep_1()
  {

    $source_id = $this->request->get('source_id');


    return $this->render('step_1', [
      'source_id' => $source_id,
    ]);
  }

  public function import_from_tmp_db($source, $q_1, $import_timestamp)
  {
    $stat['all'] = 0;
    $stat['asin_duplicate'] = 0;
    $stat['replaced'] = 0;
    $stat['ignored'] = 0;
    $stat['added'] = 0;
    $stat['p_with_right_p'] = 0;
    $stat['added_product'] = 0;
    $stat['added_product_right'] = 0;
    
    $source_id = $source['source_id'];

    $tbl_1 = $source['source_table_name'];
    $tbl_2 = $source['source_table_name_2'];

    /* ключи в таблице 1 */
    $field_names_tbl_1 = [];
    if ($tbl_1) {
      $sql_select = "SHOW COLUMNS FROM checker.$tbl_1";
      $res = $this->sql_cmd($sql_select, [], 'select');
      if ($res) {
        foreach ($res as $k => $v) {
          $field_names_tbl_1[] = $v['Field'];
        }
      }
    }
    /*///*/

    /* ключи в таблице 2 */
    $field_names_tbl_2 = [];
    if ($tbl_2) {
      $sql_select = "SHOW COLUMNS FROM checker.$tbl_2";
      $res = $this->sql_cmd($sql_select, [], 'select');
      if ($res) {
        foreach ($res as $k => $v) {
          if ($v['Field'] === 'id') continue;
          $field_names_tbl_2[] = '`' . $v['Field'] . '`';
        }
      }
    }

    /*///*/


    /* @var $class Product */
    $class = $source['source_class'];

    //  tmp items
    $res_1 = $this->sql_cmd('SELECT * FROM ' . $this->db_import_name . '.' . $tbl_1, [], 'select');

    foreach ($res_1 as $k_1 => $r_1) {
      $q = $class::find()->where(['asin' => $r_1['asin']]);
      $res_2 = $q->one();
      $stat['all'] += 1;
      $asin = $r_1['asin'];


      if (!$this->has_p_with_right_p($tbl_2, $asin)) {
        $stat['p_without_right_p'] += 1;
        $stat['ignored'] += 1;
        $stat['p_without_right_p_asins'][] = $asin;
        continue;
      }

      if ($res_2) {
        $type = 'product_has';
        $stat['asin_duplicate'] += 1;
      } else $type = 'ADD';

      $_id = $res_2->id;
      //$connection = Yii::$app->getDb();


      if ($type === 'product_has' && strtolower($q_1) === 'replace') {
        //  `title`='a', `categories`='a'

        $_set = [];
        foreach ($r_1 as $f_name => $f_value) {
          if ($f_name === 'id') continue;
          if (!in_array($f_name, $field_names_tbl_1)) continue;

          //$_set[] = "`".$f_name."`= :". $f_name . "";

          if ($f_value === null || $f_value == '0000-00-00 00:00:00') {
            $_set[] = "`" . $f_name . "`= NULL";
          } else {
            $_set[] = "`" . $f_name . "`= '" . addslashes($f_value) . "'";
          }
        }

        $sql_update = 'UPDATE checker.' . $tbl_1 . ' SET ' . implode(', ', $_set) . ' WHERE id = ' . $_id;
        $this->sql_cmd($sql_update);
        $this->sql_cmd(
          "UPDATE checker.$tbl_1 SET date_update = '" . date('Y-m-d H:i:s', $import_timestamp) . "' WHERE id = $_id",
        );


        //$res = $command->execute();
        $this->clear_old_settings($res_2->id, $source_id);

        if ($tbl_2) {
          // для правых товаров пока просто... находим удаляем загруаем новые

          // DELETE FROM checker.parser_alibaba_results WHERE asin = 'B09MT1PKM7';
          $sql_delete = "DELETE FROM checker.$tbl_2 WHERE asin = :asin";
          $this->sql_cmd($sql_delete, [':asin' => $asin]);
          //$command = $connection->createCommand($sql_delete);
          //$command->bindValue(':asin', $asin);
          //$res = $command->execute();


          //            $sql = "SELECT * FROM checker.$tbl_2 WHERE checker.$tbl_2.asin = :asin";
          //            $res = $this->sql_cmd($sql,'query',[':asin' => $asin]);

          $field_names_tbl_2_str = implode(', ', $field_names_tbl_2);
          $sql_copy =  "INSERT INTO checker.$tbl_2 ($field_names_tbl_2_str)
                          SELECT $field_names_tbl_2_str
                          FROM $this->db_import_name.$tbl_2
                          WHERE $this->db_import_name.$tbl_2.asin = :asin;";

          $res = $this->sql_cmd($sql_copy, [':asin' => $asin],'insert');
          
          // Добавляем в статистику кол-во правых товаров
          $stat['added_product_right']+=$res;
          
          $this->sql_cmd(
            "UPDATE checker.$tbl_2 SET parse_at = '" . date('Y-m-d H:i:s', $import_timestamp) . "' WHERE checker.$tbl_2.asin = :asin",
            [':asin' => $asin],
          );
          //$command = $connection->createCommand($sql_copy);
          //$command->bindValue(':asin', $asin);
          //$res = $command->execute();

          if ($this->has_p_with_right_p($tbl_2, $asin)) $stat['p_with_right_p'] += 1;    
        }

        $stat['replaced'] += 1;
      } else if ($type === 'product_has' && strtolower($q_1) !== 'replace') {
        $stat['ignored'] += 1;
      } else {

        $_set_1 = [];
        $_set_2 = [];
        foreach ($r_1 as $f_name => $f_value) {
          if ($f_name === 'id') continue;
          if (!in_array($f_name, $field_names_tbl_1)) continue; // если в новой таблице есть поле котрого нет в старом
          $_set_1[] = '`' . $f_name . '`';
          $_set_2[] = ':' . $f_name;
        }

        // INSERT INTO `checker`.`parser_trademarkia_com_new` (`asin`) VALUES ('aaa');

        /*
          $sql_insert = 'INSERT INTO `checker`.`'. $tbl_1 . '` ('. implode(', ', $_set_1) . ') VALUES ('. implode(', ', $_set_2) .');';
          //$command = $connection->createCommand($sql_update);
          $prepare_2 = [];
          foreach ($r_1 as $f_name => $f_value) {
            if ($f_name === 'id') continue;
            if (!in_array($f_name,$field_names_tbl_1)) continue; // если в новой таблиуе поле котрого нет в старом
            if ($f_name === 'info')
              $prepare_2[':'.$f_name] = '{}';
            else
              $prepare_2[':'.$f_name] = $f_value;
            //$command->bindValue(':'.$f_name, $f_value);
          }


          $res_insert = $this->sql_cmd($sql_insert,$prepare_2);
*/
        //          echo '<pre>'.PHP_EOL;
        //          print_r($prepare_2);
        //          echo PHP_EOL;
        //          print_r($sql_insert);
        //          echo PHP_EOL;
        //          exit;

        $sql_insert =  "
                          INSERT INTO checker.$tbl_1 (" . implode(', ', $_set_1) . ")
                          SELECT " . implode(', ', $_set_1) . "
                          FROM $this->db_import_name.$tbl_1
                          WHERE $this->db_import_name.$tbl_1.asin = :asin
                          ;";

        $this->sql_cmd(
          "UPDATE checker.$tbl_1 SET date_update = '" . date('Y-m-d H:i:s', $import_timestamp) . "' WHERE checker.$tbl_1.asin = :asin",
          [':asin' => $asin],
        );
        //$res_insert = $this->sql_cmd($sql_insert);
        $res_insert = $this->sql_cmd($sql_insert, [':asin' => $asin],'insert');
        
        /* Кол-во добавленных левых товаров */
        $stat['added_product']+=$res_insert;
        
        //          echo '<pre>'.PHP_EOL;
        //          print_r($sql_insert);
        //          echo PHP_EOL;
        //          exit;
        //$res = $command->execute();

        if ($tbl_2) {
          $field_names_tbl_2_str = implode(', ', $field_names_tbl_2);
          $sql_copy =  "
                          INSERT INTO checker.$tbl_2 ($field_names_tbl_2_str)
                          SELECT $field_names_tbl_2_str
                          FROM $this->db_import_name.$tbl_2
                          WHERE $this->db_import_name.$tbl_2.asin = :asin
                          ;";
          $res = $this->sql_cmd($sql_copy, [':asin' => $asin],'insert');
          $this->sql_cmd(
            "UPDATE checker.$tbl_2 SET parse_at = '" . date('Y-m-d H:i:s', $import_timestamp) . "' WHERE checker.$tbl_2.asin = :asin",
            [':asin' => $asin],
          );
          
          $stat['added_product_right']+=$res;
          //$command = $connection->createCommand($sql_copy);
          // $command->bindValue(':asin', $asin);
          // $res = $command->execute();

          if ($this->has_p_with_right_p($tbl_2, $asin)) $stat['p_with_right_p'] += 1;
        }

        $stat['added'] += 1;
      }
    }

    return $stat;
  }

  private function has_p_with_right_p($tbl_2, $asin)
  {
    /******START*******/
    $sql_has_right_p = "
                          SELECT *
                          FROM $this->db_import_name.$tbl_2
                          WHERE $this->db_import_name.$tbl_2.asin = :asin 
                          LIMIT 1
                          ;";
    $res_sql_has_right_p = $this->sql_cmd($sql_has_right_p, [':asin' => $asin], 'select');
    if ($res_sql_has_right_p) return true;
    return false;
    /*******END********/
  }


  public function clear_old_settings($p_id, $source_id)
  {
    $res_hidden = HiddenItems::findOne(['p_id' => $p_id, 'source_id' => $source_id]);
    if ($res_hidden) $res_hidden->delete();

    $res_comparisons = Comparison::find()->where(['product_id' => $p_id, 'source_id' => $source_id])->all();
    if ($res_comparisons)
      foreach ($res_comparisons as $res_comparison) {
        $res_comparison->delete();
      }

    $res_p_all_compare = P_all_compare::findOne(['p_id' => $p_id, 'source_id' => $source_id]);
    if ($res_p_all_compare)  $res_p_all_compare->delete();

    $res_p_updated = P_updated::findOne(['p_id' => $p_id, 'source_id' => $source_id]);
    if ($res_p_updated) $res_p_updated->delete();
  }
}
