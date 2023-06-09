<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\presenters;

use common\models\Session;
use common\models\Filters;
use common\models\Stats_import_export;
use common\models\Comparison;
use common\models\HiddenItems;
use common\models\P_user_visible;
use common\models\Source;
use common\models\Product;

/**
 * Превставитель для страницы product/index, содержащий логику
 * То, что здесь содерится можно былло раскидать по моделям. Но многих моделей нет
 *
 * @author kosten
 */
class IndexPresenter {

    private Source $source;

    use TraitListFilters;

    public function loadFromParams(array $params) {
        $this->filters->loadByParams($params);
    }

    public function setSource(Source $source) {
        $this->source = $source;
        $this->loadTraitListFilters($this->source);
    }

    public function getListSource() {
        return array_merge(
                Source::getSourcesPaidByIdUser(\Yii::$app->user->id),
                Source::getSourcesFree());
    }

    public function getListCountProductsOnPage() {
        return [10, 20, 50, 100, 200];
    }

    public function getNumberPageCurrent() {
        return $this->filters->f_number_page_current;
    }

    public function getCountProductsOnPageRight($list) {
        if (!$list) {
            return 0;
        }
        $cnt_all_right = 0;
        foreach ($list as $product) {
            $items = $product->addInfo;
            $cnt_all_right += count($items);
        }
        return $cnt_all_right;
    }

    public function getLastLocalImport() {
        $s_import = Stats_import_export::find();
        $s_import->where(['type' => 'IMPORT']);
        $s_import->orderBy(['created' => SORT_DESC]);
        $s_import->limit(1);
        return $s_import->one();
    }

    /**
     * Сколько товаров отображается на странице
     * 
     * @param int $countProducts Общее количество товаров
     * @param int $countProductsOnPage Количество отображаемых товаров на страние
     * @return int
     * @throws InvalidArgumentException
     */
    public function getCountPages(int $countProducts, int $countProductsOnPage): int {
        if (!$countProductsOnPage) {
            throw new InvalidArgumentException('Количество отображаемых товаров на страние не может равняться нулю');
        }
        return ceil($countProducts / $countProductsOnPage);
    }

    /**
     * Функция для отображения пагинатора
     * Всю логику работы оставил старой. Позже вернусь
     * 
     * @param type $countPages Сколько всего страниц
     * @param type $numberPageCurrent Номер текущей страницы
     * @param type $left_right_n - настройка области просмотра
     */
    public function getPager(int $countPages, int $numberPageCurrent, $left_right_n = 3) {
        $from = $numberPageCurrent - $left_right_n; //1
        $add_to_v2 = 0;
        if ($from <= 0) {
            $add_to_v2 = abs($from) + 1; //2
            $from = 1;
        }
        $to = $numberPageCurrent + $left_right_n + $add_to_v2;

        if ($countPages < $to) {
            //$to = $countPages;
            $add_to_from = $countPages - $to;
            $from = $numberPageCurrent - $left_right_n + $add_to_from;
            if ($from < 1) {
                $from = 1;
            }
        }

        if ($from > $countPages && $from > $to) {
            $from = 1;
        }
        if ($to > $countPages) {
            $to = $countPages;
        }

        $pager['from'] = $from;
        $pager['to'] = $to;
        $pager['this_n'] = $numberPageCurrent;
        $pager['in'] = ['pages_cnt' => $countPages, 'left_right_n' => $left_right_n];

        return $pager;
    }

    /**
     * Функция для присвоения статуса STATUS_MISMATCH всем правым товарам 
     * и присвоения левому товару статуса STATUS_NOT_FOUND
     *  
     * @param string $url значение поля url. Пока не известно зачем нужно
     * @param int $id_product
     * @param int $id_source
     * @param bool $confirmToAction Если подтверждение на переприсвоение правым товарам статусов missmatch
     * @return boolean
     *     false Выставление статусов прервано по причине того что товар имеет сравнения. Нужно подтверждение
     *     true  Выставление статусов прошло успешно
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function missmatchToAll($url, int $id_product, int $id_source, bool $confirmToAction = false) {
        $source = Source::getById($id_source);
        if (!$source) {
            throw new \InvalidArgumentException('Не удалось найти источник');
        }

        $product = $source->class_1::getById($source->class_1, $id_product);
        if (!$product) {
            throw new \InvalidArgumentException('Не удалось найти продукт');
        }

        // Если нет подтверждения и товар имеет левые товары со статусами отличными от misssmatch
        if (!$confirmToAction && $product->isExistsItemsWithMatch()) {
            return false;
        }

        $add_info = $product->addInfo;

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //Всем товарам справа присвоить статус STATUS_MISMATCH
            if (is_array($add_info)) {
                foreach ($add_info as $item) {
                    Comparison::setStatus(Comparison::STATUS_MISMATCH, $id_source, $id_product, $item->id);
                }
            }

            // Добавить товар в список скрытых (Добавить в таблицу hidden_items)
            $find = HiddenItems::find()->where(['p_id' => $id_product, 'source_id' => $id_source])->one();
            if (!$find) {
                $h = new HiddenItems([
                    'p_id' => $id_product,
                    'source_id' => $id_source,
                    'status' => HiddenItems::STATUS_NOT_FOUND,
                ]);
                $h->save();
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollback();
            \Yii::error($ex->getLine() . ':' . $ex->getMessage());
            throw new \Exception('При выставлении статусов возникла ошибка');
        }

        return true;
    }

    /**
     * Установить статус правого товара
     * 
     * @param type $id_product Левый товар
     * @param type $id_item Правый товар
     * @param type $id_source id источника
     * @param type $status Статус, в который нужно установить товар
     * @return type
     */
    /*
      public function setStatusProductRight($id_product, $id_item, $id_source, $status, $message=''){

      try{
      Comparison::setStatus($status, $id_source, $id_product, $id_item, $message);

      if ($status == Comparison::STATUS_OTHER && $message && User::isAdmin()){
      if (!P_user_visible::findOne(['p_id' => $id_product])) {

      $puv = new P_user_visible();
      $puv->p_id = $id_product;
      $puv->save();
      }
      }

      } catch (\Exception $ex) {
      return [
      \Yii::error($ex->getLine().':'.$ex->getMessage()),
      'status' => 'error',
      'message' => 'При обновлении базы данных возникла ошибка'
      ];
      }

      return [
      'status' => 'ok'
      ];
      }
     * 
     */

    public function changeFiltersByParams(array $params) {
        foreach ($params as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'filter_asin': $this->filters->f_asin = $value;
                        break;
                }
            }
        }
    }

    public function deleteProduct(int $id_source, int $id_product) {
        $source = Source::getById($id_source);
        if (!($source instanceof Source)) {
            throw new \Exception('Не удаось найти источник по данному id');
        }

        $product = Product::getById($source->class_1, $id_product);
        $product->source = $source;
        if (!($product instanceof Product)) {
            throw new \Exception('Не удалось найти товар по данному id');
        }

        $product->delete();
    }

    public function resetCompareProduct(int $id_source, int $id_product) {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            Comparison::deleteAll(['product_id' => $id_product, 'source_id' => $id_source]);
            HiddenItems::deleteAll(['p_id' => $id_product, 'source_id' => $id_source]);
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }

    /**
     * 
     * @param type $status
     * @param type $id_source
     * @param type $id_product
     * @param type $id_item
     * @param type $message
     * @param type $is_last
     * @throws \Exception
     */
    public function changeStatusProductRight($status, $id_source, $id_product, $id_item, $message, $is_last) {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!Comparison::setStatus($status, $id_source, $id_product, $id_item, $message)) {
                throw new \Exception('Не удалось сохранить данные в базу данных');
            }
            //После смены статуса на правом товаре если это быд последний товар,
            //То необходимо добавить левый товар в таблицу hidden_items
            if ($is_last) {
                // Добавить товар в список скрытых (Добавить в таблицу hidden_items)
                $find = HiddenItems::find()->where(['p_id' => $id_product, 'source_id' => $id_source])->one();
                if (!$find) {
                    $h = new HiddenItems([
                        'p_id' => $id_product,
                        'source_id' => $id_source,
                        'status' => HiddenItems::STATUS_NOT_FOUND,
                    ]);
                    if (!$h->save()) {
                        throw new \Exception('Не удалось занест левый товар в базу данных');
                    }
                } else {
                    throw new \Exception('Запись товара уже существует в таблице hidden_items');
                }
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
        /**
     * Выставить статусы для всех правых товаров из списка по data-атрибутам
     * @param array $list_data_product_right - data атрибуты правых товаров
     */
    public function changeStatusProductsRight($list_data_product_right) {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $ids_product_and_source = [];
            foreach ($list_data_product_right as $data) {
                $status = $data['status'];
                $id_source = $data['id_source'];
                $id_product = $data['id_product'];
                $id_item = $data['id_item'];
                $message = $data['message'];
                if (!Comparison::setStatus($status, $id_source, $id_product, $id_item, $message)) {
                    throw new \Exception('Не удалось статус ддя правого товара c id = ' . $id_item);
                }
                $ids_product_and_source[$id_product] = $id_source;
            }

            // Если у левого товара не осталось, не отмеченных правых, то левый заносим в hidden_items
            $source = null;
            foreach ($ids_product_and_source as $id_p => $id_s) {
                if ($source === null || $source->id !== $id_s) {
                    $source = Source::getById($id_s);
                    if (!$source) {
                        throw new \Exception('Не удалосб найти источник для продукта с id = ' . $id_p);
                    }
                    $product = $source->class_1::findOne(['id' => $id_p]);
                    if (!$product) {
                        throw new \Exception('Не удалосб найти продукт с id = ' . $id_p);
                    }
                    $product->source = $source;

                    $comparisons = $product->comparisons; //$product->countComparisons
                    $right_items = $product->addInfo;     //$product->countRightItems
                    if ((count($right_items) - count($comparisons)) <= 0) {
                        $find = HiddenItems::find()->where(['p_id' => $id_p, 'source_id' => $id_s])->one();
                        if (!$find) {
                            $h = new HiddenItems([
                                'p_id' => $id_product,
                                'source_id' => $id_source,
                                'status' => HiddenItems::STATUS_NOT_FOUND
                            ]);
                            if (!$h->save()) {
                                throw new \Exception('Не удалось занест левый товар в базу данных');
                            }
                        } else {
                            throw new \Exception('Запись товара уже существует в таблице hidden_items');
                        }
                    }
                }
            }

            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Если сравнение левого товара, это значит что все правые товары нужно сравнить так как левый и левый пометить как отмеченый всеми
     * 
     * @param type $list_data_product_left Масив data даных левых товаров
     * @param type $list_data_product_right Масив data даных правых товаров
     * @return boolean
     * @throws \Exception
     */
    public function changeStatusProducts($list_data_product_left = [], $list_data_product_right = [], $list_data_product_left_delete = []) {
        if (!is_array($list_data_product_left) &&
            !is_array($list_data_product_right) &&
            !is_array($list_data_product_left_delete)){
                return false;
        }
        $is_change_all = true;
        $ids_product_and_source = [];
        
        // Нужно удалить левые товары вместе со всеми правыми ели они есть
        foreach ($list_data_product_left_delete as $data){
            $id_source = $data['id_source'];
            $id_product = $data['id_product'];
            
            // Если в массиве левых товаров на missmatch есть этот товар то убираем его
            $list_data_product_left = array_filter($list_data_product_left, function($el) use ($id_source, $id_product){
                return ($el['id_product'] !== $id_product || !$el['id_source'] !== $id_source);
            });
            // Если в массива правых товаров на изменение  есть этот товар - убираем его
            $list_data_product_right = array_filter($list_data_product_right, function($el) use ($id_source, $id_product){
                return ($el['id_product'] !== $id_product || !$el['id_source'] !== $id_source);
            });
            
            try{
                $this->deleteProduct($id_source, $id_product);
            } catch (\Exception $ex) {
                $is_change_all = false;
            }
        }
        
        // Сохраним статусы всех правых товаров
        foreach($list_data_product_right as $data){
            $id_source  = $data['id_source'];
            $id_product = $data['id_product'];
            $id_item    = $data['id_item'];
            $status     = $data['status'];
            $message    = $data['message'];
            try{
                $this->changeStatusProductRight($status, $id_source, $id_product, $id_item, $message, false);
                $ids_product_and_source[$id_product] = $id_source; // Надеюсь что тут не будет одинаковых id c разными источниками
            } catch(\Exception $ex) {
                $is_change_all = false;
            }
        }
        
        // Если есть левые товары, записывам в них статусы вместе со всеми правыми
        foreach ($list_data_product_left as $data) {
            $id_source = $data['id_source'];
            $id_product = $data['id_product'];
            try {
                $this->missmatchToAll('', $id_product, $id_source, true);
            } catch (\Exception $ex) {
                $is_change_all = false;
            }
        }
        
        // Теперь проходимся по левым товарам и смотрим, не остались ли товары, не именющие правых без сравнений
        foreach ($ids_product_and_source as $id_product => $id_source){
            try{
                $source = Source::getById($id_source);
                if (!$source) {
                    throw new \Exception("Не удалось найти источник для продукта с id = $id_product");
                }
                $product = Product::getById($source->class_1, $id_product);
                if (!$product) {
                    throw new \Exception('Не удалось найти продукт с id = ' . $id_product);
                }
                $product->source = $source;
                $count_items = $product->countRightItems;
                $count_comparisons = $product->countComparisons;
                if (($count_items -$count_comparisons) <= 0){
                    $is_exists = HiddenItems::find()->where(['p_id' => $id_product, 'source_id' => $id_source])->exists();
                    if (!$is_exists) {
                        $h = new HiddenItems([
                            'p_id' => $id_product,
                            'source_id' => $id_source,
                            'status' => HiddenItems::STATUS_NOT_FOUND
                        ]);
                        if (!$h->save()) {
                            throw new \Exception('Не удалось занест левый товар в базу данных');
                        }
                    } else {
                        throw new \Exception('Запись товара уже существует в таблице hidden_items');
                    }                    
                }
            } catch (\Exception $ex) {
                $is_change_all = false;
            }
        }
        return $is_change_all;
    }

    /**
     * Ренерит пагинатор на страницу
     * 
     * @param type $f_number_page_current
     * @param type $count_pages
     * @param type $delta
     * 
     */
    public function getHTMLPaginator($f_number_page_current, $count_pages, $delta = 5) {
        $html = '';
        // $count_pages             Общее количество страниц
        // $f_number_page_current   Номер текущей страницы
        // $delta                   Смещение влево и вправо от текущей страницы (Настраивается тут)
        //                     n6 - $f_number_page_current
        //1 ... n1,n2,n3,n4,n5,n6,n7,n8,n9,n10,n11 ... $count_numbers
        $number_min = max($f_number_page_current - $delta, 1);
        $number_max = min($f_number_page_current + $delta, $count_pages);

        $is_ellipsis_left = ($number_min - 1 ) > 1; //Нужно ли троеточие слева
        $is_ellipsis_right = ($count_pages - $number_max) > 1; //Нужно ли троеточие справа
        //Если перед первым момером(после посдеднего номера) стоит только один номер, то вместо ... проще отобращить одну цифру
        if (($number_min - 1) === 1) {
            $number_min -= 1;
        }
        if (($count_pages - $number_max) === 1) {
            $number_max += 1;
        }

        // Троеточие слева и первая страница
        if ($is_ellipsis_left) {
            //Отображаем первую страницу
            $i = 1;
            $is_active = ($i === $f_number_page_current) ? 'active' : '';
            $html.= "<li class=\"page-item $is_active\"><a class=\"page-link\" href=\"/product/index?page=$i\">$i</a></li>";

            $i = max($f_number_page_current - $delta - 1, 2);
            $html.= "<li class=\"page-item\"><a class=\"page-link\" href=\"/product/index?page=$i\">...</a></li>";
        }

        // Список номеров
        for ($i = $number_min; $i <= $number_max; $i++) {
            $is_active = ($i === $f_number_page_current) ? 'active' : '';
            $html.= "<li class=\"page-item $is_active\"><a class=\"page-link\" href=\"/product/index?page=$i\">$i</a></li>";
        }

        // Троеточие справа и последняя страница
        if ($is_ellipsis_right) {
            $i = min($f_number_page_current + $delta + 1, $count_pages - 1);
            $html.= "<li class=\"page-item\"><a class=\"page-link\" href=\"/product/index?page=$i\">...</a></li>";

            //Отображаем последнюю
            $i = $count_pages;
            $html.= "<li class=\"page-item $is_active\"><a class=\"page-link\" href=\"/product/index?page=$i\">$i</a></li>";
        }
        return $html;
    }

}