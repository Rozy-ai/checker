<?php

namespace common\models;

use backend\models\P_updated;
use backend\models\Settings__fields_extend_price;
use common\models\Source;
use Yii;
use yii\BaseYii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use common\models\Comparison;

/**
 * This is the model class for table "{{%parser_trademarkia_com}}".
 * 
 * todo: Сделать этот класс универсальным под любую таблицу из списка:
 *          Parser_trademarkia_com
 *          Parser_Google
 *          Parser_china
 *          Parser_shopping
 *       В зависимости от источника
 *  
 * @property int $id
 * @property string|null $title
 * @property string|null $categories
 * @property string      $asin
 * @property string|null $info
 * @property string|null $comparsion_info
 * @property string|null $results_all_all
 * @property string|null $results_1_1
 * @property string|null $images
 * @property string|null $images_url
 * @property string|null $item_url
 * @property string|null $date_add
 * @property string|null $statuses
 *
 */
class Product extends \yii\db\ActiveRecord
{

    protected $_baseInfo = [];
    protected $_addInfo = [];
    protected Source $_source;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categories', 'info', 'comparsion_info', 'results_all_all', 'results_1_1', 'images', 'images_url'], 'string'],
            [['date_add'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['asin'], 'string', 'max' => 15],
            [['item_url'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('site', 'ID'),
            'title' => Yii::t('site', 'Title'),
            'categories' => Yii::t('site', 'Categories'),
            'asin' => Yii::t('site', 'Asin'),
            'info' => Yii::t('site', 'Info'),
            'comparsion_info' => Yii::t('site', 'Comparsion Info'),
            'results_all_all' => Yii::t('site', 'Results All All'),
            'results_1_1' => Yii::t('site', 'Results 1 1'),
            'images' => Yii::t('site', 'Images'),
            'images_url' => Yii::t('site', 'Images Url'),
            'item_url' => Yii::t('site', 'Item Url'),
            'date_add' => Yii::t('site', 'Date Add'),
        ];
    }

    /**
     * Получить модель Product по заданному id
     * 
     * @param string $class_source
     * @param int $id
     * @return Product | null
     */
    public static function getById(string $class_source, int $id)
    {
        return $class_source::findOne(['id' => $id]);
    }


    /**
     * Базовая информация о левом товаре
     * 
     * @return array
     */
    public function getBaseInfo()
    {
        if (!$this->_baseInfo && $this->info) {
            $this->_baseInfo = $this->info;
        }
        return $this->_baseInfo;
    }

    /**
     * Уттановить базовую информацию о левом товаре
     * 
     * @param type $base_info
     */
    public function setBaseInfo($base_info)
    {
        $this->_baseInfo = $base_info;
    }

    /**
     * Установить список правых товаров в поле addInfo
     * 
     * @return Product_right[]
     */
    public function getAddInfo()
    {
        // if (!$this->_addInfo) {
        //     $this->initAddInfo();
        // }
        return $this->_addInfo;
    }

    public function getSource()
    {
        return $this->_source ?? $this->_source = Source::findOne(['table_1' => str_replace('common\models\\', '', strtolower(get_called_class()))]);
    }

    public function setSource(Source $source)
    {
        $this->_source = $source;
    }

    /**
     * Заполняет свойство _addInfo массивом из правых элементов
     * @return Product_right[]
     */
    public function initAddInfo(Filters $filters)
    {
        $asin = $this->asin;
        $source = $this->source;
        $class_2 = $source->class_2;
        $table_2 = $class_2::find()->where(['asin' => $asin])->orderBy(['parse_at' => SORT_ASC])->asArray()->all() ?: [];
        $table_2 = self::filterProducts($table_2, $filters, 'right', false);

        foreach ($table_2 as $item) {
            $out = [];
            $res = [];

            // Копирует все ключи кроме results 
            foreach ($item as $k => $value) {     //Parser_trademarkia_com_result object
                if ($k === 'results')
                    continue;
                $out[$k] = $value;
            }

            // Если есть ключ results, то
            if (isset($item->results)) {
                $data = Json::decode($item->results, true) ?: [];
                //$data = Json::decode($item->results, true) ?: [];
                //$res = $this->get_all_elements_in_array_to_first_level($data,'>>>');
                $res = $this->get_all_elements_in_array_to_first_level($data, '.');
            }

            $pr = new Product_right($source, array_merge($out, $res, ['parent_item' => $this->baseInfo]));
            $this->_addInfo[] = $pr;
        }
    }

    /**
     * Сколько всего правых товаров имеется у данного левого
     * @return integer
     */
    public function getCountRightItems()
    {
        if ($this->_addInfo) {
            return count($this->_addInfo);
        }
        if (!$this->source) {
            throw new InvalidArgumentException('Установите id источника продукта');
        }

        return $this->source->class_2::find()->where(['asin' => $this->asin])->count();
    }

    /**
     * Количество сравнений с данным товаром
     * 
     * @param string $status
     * @return int
     */
    public function getCountComparisons($status = '')
    {
        return $status ?
            Comparison::find()->where(['product_id' => $this->id, 'source_id' => $this->source->id, 'status' => $status])->count() :
            Comparison::find()->where(['product_id' => $this->id, 'source_id' => $this->source->id])->count();
    }

    /**
     * Gets query for [[Comparison]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComparisons()
    {
        return Comparison::find()
            ->where(['product_id' => $this->id, 'source_id' => $this->source->id])
            ->indexBy('product_right_id')
            ->all();
    }

    /**
     * Имеет ли продукт правые товары со статусом STATUS_MATCH или STATUS_PRE_MATCH
     * @return bool
     */
    public function isExistsItemsWithMatch()
    {
        return Comparison::find()
            ->where(['product_id' => $this->id,])
            ->andWhere([
                'or',
                ['status' => Comparison::STATUS_MATCH],
                ['status' => Comparison::STATUS_PRE_MATCH]
            ])
            ->exists();
    }

    /** @TODO OLD Go to getListProductsAll
     * Получить список продуктов согласно фильтрам
     * 
     * @param Source $source
     * @param Filters $filters
     * @param bool $is_admin
     * @param type $favorites
     * @return type
     */
    public static function getListProducts(Source $source, Filters $filters, bool $is_admin, $favorites = [])
    {
        $source_table_name = $source->table_1;
        $source_table2_name = $source->table_2;

        $q = new FiltersQuery($source->class_1);
        // !!! Если менять тут то нужно менять getCountProducts
        $q->where([
            'and',
            $q->getSqlIsMissingHiddenItems($filters->f_source, $filters->f_comparison_status),
            $q->getSqlAsin($source_table_name, $filters->f_asin, $filters->f_asin_multiple),
            $q->getSqlCategoriesRoot($source_table_name, $filters->f_categories_root),
            $q->getSqlTille($source_table_name, $filters->f_title),
            $q->getSqlStatus($filters->f_status),
            $q->getSqlUsername($source_table_name, $filters->f_username),
            $q->getSqlComparisonStatus($filters->f_comparison_status),
            $q->getSqlProfile($is_admin, $source_table_name, $filters->f_profile),
            $q->getSqlNewProducts($filters->f_new, Stats_import_export::getLastLocalImport()),
            $q->getSqlFavorProducts($source, $filters->f_favor, $favorites),
            $q->getSqlAdditionalFilters($filters),
        ]);

        // Добавим сортировку:
        switch ($filters->f_sort) {
            case 'created_ASC':
                $q->orderBy($source_table_name . '.date_add ASC');
                break;
            case 'created_DESC':
                $q->orderBy($source_table_name . '.date_add DESC');
                break;
            case 'updated_ASC':
                $q->orderBy($source_table_name . '.date_update ASC');
                // $q->addTable('p_updated');
                // $q->orderBy('p_updated.date ASC');
                break;
            case 'updated_DESC':
                $q->orderBy($source_table_name . '.date_update DESC');
                // $q->addTable('p_updated');
                // $q->orderBy('p_updated.date DESC');
                break;
            default:
                $q->orderBy($source_table_name . '.id');
        }

        // Получим все необходимые join

        $q->addJoins($source_table_name, $source_table2_name);

        // Отсечем не нужные записи
        if ($filters->f_count_products_on_page !== 'ALL') {
            $count_products_on_page = (int) $filters->f_count_products_on_page;

            $offset = ($filters->f_number_page_current - 1) * $count_products_on_page;
            $q->limit($count_products_on_page);
            $q->offset($offset);
        }

        $list = $q->createCommand()->queryAll();
        $list = self::filterProducts($list, $filters);
        foreach ($list as $k => $product) {
            $list[$k] = self::getById($source->class_1, $product['id']);
            $list[$k]->initAddInfo($filters);
            $list[$k]->_source = $source;
            $list[$k]->_baseInfo = $list[$k]->info;
        }
        return $list;
    }

    /**
     * Получить запрос списка продуктов согласно фильтрам
     * 
     * @param Source $source
     * @param Filters $filters
     * @param bool $is_admin
     * @param type $favorites
     * @return type
     */
    public static function getListProductsQuery(Source $source, Filters $filters, bool $is_admin, $favorites = [])
    {
        $source_table_name = $source->table_1;
        $source_table2_name = $source->table_2;

        $query = new FiltersQuery($source->class_1);
        // !!! Если менять тут то нужно менять getCountProducts
        $query->where([
            'and',
            $query->getSqlIsMissingHiddenItems($filters->f_source, $filters->f_comparison_status),
            $query->getSqlAsin($source_table_name, $filters->f_asin, $filters->f_asin_multiple),
            $query->getSqlCategoriesRoot($source_table_name, $filters->f_categories_root),
            $query->getSqlTille($source_table_name, $filters->f_title),
            $query->getSqlStatus($filters->f_status),
            $query->getSqlUsername($source_table_name, $filters->f_username),
            $query->getSqlComparisonStatus($filters->f_comparison_status),
            $query->getSqlProfile($is_admin, $source_table_name, $filters->f_profile),
            $query->getSqlNewProducts($filters->f_new, Stats_import_export::getLastLocalImport()),
            $query->getSqlNoCompareItems($filters->f_no_compare, $filters->f_source),
            $query->getSqlFavorProducts($source, $filters->f_favor, $favorites),
        ]);

        // Добавим сортировку
        $query = self::setSortListProductsQuery($query, $filters->f_soft, $source_table_name);

        // Получим все необходимые join
        $query->addJoins($source_table_name, $source_table2_name);

        // Отсечем не нужные записи
        if ($filters->f_count_products_on_page !== 'ALL') {
            $count_products_on_page = (int) $filters->f_count_products_on_page;

            $offset = ($filters->f_number_page_current - 1) * $count_products_on_page;
            $query->limit($count_products_on_page);
            $query->offset($offset);
        }
        return $query;
    }

    /**
     * Установка сортировки запроса списка продуктов
     * 
     * @param type $query
     * @param type $soft
     * @param type $source_table_name
     * @return type
     */
    public static function setSortListProductsQuery(FiltersQuery $query, $soft, $source_table_name)
    {
        // Добавим сортировку:
        switch ($sort) {
            case 'created_ASC':
                $query->orderBy($source_table_name . '.date_add ASC');
                break;
            case 'created_DESC':
                $query->orderBy($source_table_name . '.date_add DESC');
                break;
            case 'updated_ASC':
                $query->orderBy($source_table_name . '.date_update ASC');
                break;
            case 'updated_DESC':
                $query->orderBy($source_table_name . '.date_update DESC');
                break;
            default:
                $query->orderBy($source_table_name . '.id');
        }
        return $query;
    }

    /**
     * Получить список всех продуктов согласно фильтрам
     * 
     * @param Source $source
     * @param Filters $filters
     * @param bool $is_admin
     * @param type $favorites
     * @return type
     */
    public static function getListProductsAll(Source $source, Filters $filters, bool $is_admin, $favorites = [])
    {
        $query = self::getListProductsQuery($source, $filters, $is_admin, $favorites);
        return self::getListProductsBuild($query, $source);
    }

    /**
     * Получить обработка запрос списка всех продуктов
     * 
     * @param Query $query
     * @param Source $source
     * @return type
     */
    public static function getListProductsBuild($query, Source $source)
    {
        $list = $query->createCommand()->queryAll();
        foreach ($list as $k => $product) {

            $list[$k] = self::getById($source->class_1, $product['id']);
            $list[$k]->_source = $source;
            $list[$k]->_baseInfo = $list[$k]->info;
        }
        return $list;
    }

    /**
     * Получить левое кол-во продуктов согласно фильтрам
     * 
     * @param Source  $source
     * @param Filters $filters
     * @param array $favorites
     * @return Product[]
     */
    public static function getCountProductsLeft(Source $source, Filters $filters, bool $is_admin, $favorites = [])
    {
        $query = self::getListProductsQuery($source, $filters, $is_admin, $favorites);
        return (int)$query->count();
    }

    /**
     * Получить кол-во левое кол-во продуктов согласно фильтрам
     * 
     * @param Source  $source
     * @param Filters $filters
     * @param array $favorites
     * @return Product[]
     */
    public static function getCountProductsRight(Source $source, Filters $filters, bool $is_admin, $favorites = [])
    {
        $query = self::getListProductsQuery($source, $filters, $is_admin, $favorites);
        $count_right = $query->select(['DISTINCT ( '
            . 'SELECT COUNT(*) as count_right '
            . 'FROM comparisons cp '
            . ($filters->f_comparison_status ? 'WHERE cp.status =  comparisons.status GROUP BY cp.status' : '')
            . ' ) as count_right'])->createCommand()->queryColumn('count_right');
        //echo $query->createCommand()->getRawSql();
        //die();
        return (int)$count_right[0];
    }

    /** 
     * Получить список + кол-во левое + кол-во правое продуктов согласно фильтра
     * @param Source $source
     * @param Filters $filters
     * @param bool $is_admin
     * @param type $favorites
     * @return array
     */
    public static function getListProductsBack(Source $source, Filters $filters, bool $is_admin, $favorites = []): array
    {
        $query = self::getListProductsQuery($source, $filters, $is_admin, $favorites);
        $list =  self::getListProductsAll($source, $filters, $is_admin, $favorites);
        $count = $query->count();
        $count_right = $query->select(['DISTINCT ( '
            . 'SELECT COUNT(*) as count_right '
            . 'FROM comparisons cp '
            . 'WHERE cp.status =  comparisons.status '
            . 'GROUP BY cp.status'
            . ' ) as count_right'])->createCommand()->queryColumn('count_right');

        return [$list, (int)$count, (int)$count_right[0]];
    }

    public static function getListProductsFront(Source $source, Filters $filters): array
    {
        $source_table_name = $source->table_1;
        $source_table2_name = $source->table_2;

        if ($filters->f_status === null) {
            $stasusF = [HiddenItems::STATUS_NOT_FOUND, HiddenItems::STATUS_CHECK, HiddenItems::STATUS_ACCEPT, HiddenItems::STATUS_NO_ACCEPT];
        } else {
            $stasusF = [$filters->f_status];
        }

        $q = new FiltersQuery($source->class_1);

        // !!! Если менять тут то нужно менять getCountProducts
        $q->where([
            'and',
            $q->getSqlIsMissingHiddenItems($filters->f_source, $filters->f_comparison_status),
            $q->getSqlAsin($source_table_name, $filters->f_asin),
            $q->getSqlCategoriesRoot($source_table_name, $filters->f_categories_root),
            $q->getSqlTille($source_table_name, $filters->f_title),
            $q->getSqlStatus($stasusF),
            $q->getSqlUsername($source_table_name, $filters->f_username),
            $q->getSqlComparisonStatus($filters->f_comparison_status),
            $q->getSqlProfileFront($source_table_name, $filters->f_profile, $filters->f_profile_type)
            //$q->getSqlAddInfoExists($source_table_name),
            //$q->getSqlNoInComparisons(),
            //$q->getSqlSettingsMessage(),
        ]);

        // Добавим сортировку:
        switch ($filters->f_sort) {
            case 'created_ASC':
                $q->orderBy($source_table_name . '.date_add ASC');
                break;
            case 'created_DESC':
                $q->orderBy($source_table_name . '.date_add DESC');
                break;
            case 'updated_ASC':
                $q->addTable('p_updated');
                $q->orderBy('p_updated.date ASC');
                break;
            case 'updated_DESC':
                $q->addTable('p_updated');
                $q->orderBy('p_updated.date DESC');
                break;
            default:
                $q->orderBy($source_table_name . '.id');
        }

        // Получим все необходимые join
        $q->addJoins($source_table_name, $source_table2_name);

        $count = $q->count();

        // Отсечем не нужные записи
        if ($filters->f_count_products_on_page !== 'ALL') {
            $count_products_on_page = (int) $filters->f_count_products_on_page;

            $offset = ($filters->f_number_page_current - 1) * $count_products_on_page;
            $q->limit($count_products_on_page);
            $q->offset($offset * 0);
        }

        $list = $q->all();

        foreach ($list as $k => $product) {
            $product->source = $source;
            $product->baseInfo = $product->info;
        }
        return [$list, (int)$count];
    }

    /** @TODO OLD 
     * Узнать количество всех левых продуктов
     * 
     * @param Source $source
     * @param Filters $filters
     * @param bool $is_admin
     * @param array $favorites
     * @return int
     */
    public static function getCountProducts(Source $source, Filters $filters, bool $is_admin, $favorites = [])
    {
        $source_table_name = $source->table_1;
        $source_table2_name = $source->table_2;

        $q = new FiltersQuery($source->class_1);
        // !!! Если менять тут то нужно менять getCountProducts
        $q->where([
            'and',
            $q->getSqlIsMissingHiddenItems($filters->f_source, $filters->f_comparison_status),
            $q->getSqlAsin($source_table_name, $filters->f_asin, $filters->f_asin_multiple),
            $q->getSqlCategoriesRoot($source_table_name, $filters->f_categories_root),
            $q->getSqlTille($source_table_name, $filters->f_title),
            $q->getSqlStatus($filters->f_status),
            $q->getSqlUsername($source_table_name, $filters->f_username),
            $q->getSqlComparisonStatus($filters->f_comparison_status),
            $q->getSqlProfile($is_admin, $source_table_name, $filters->f_profile),
            $q->getSqlNewProducts($filters->f_new, Stats_import_export::getLastLocalImport()),
            $q->getSqlFavorProducts($source, $filters->f_favor, $favorites),
            $q->getSqlAdditionalFilters($filters),
        ]);
        // Получим все необходимые join
        $q->addJoins($source_table_name, $source_table2_name);
        /*  if ($filters->f_profile == 'Free') {
            if ($source->max_free_show_count < $q->count()) {
                return $source->max_free_show_count;
            }
        }*/
        $list = $q->createCommand()->queryAll();
        $list = self::filterProducts($list, $filters);
        return count($list);
    }

    public static function filterProducts(
        array $list,
        Filters $filters,
        string $filter_type = 'left',
        $json_column = true
    ) {
        $filterValues = $filters->getAdditionalFilterValues();
        $pFilters = $filters->additionalFilters[$filter_type][$json_column ? 'json_column' : 'column'];
        $sortFilters = array_filter($pFilters, function ($f) use ($filterValues) {
            return $f['type'] === 'sort' && !empty($filterValues[$f['name']]);
        });

        $list = array_filter($list, function ($p) use ($filters, $json_column, $pFilters, $filterValues) {
            $suitable = true;
            $info = $json_column ? json_decode($p['info'], true) : $p;
            foreach ($pFilters as $f) {
                if ($f['type'] === 'sort') {
                    continue;
                }

                $isRange = $f['range'];
                $names = !$isRange ? [$f['name']] : [$f['name'] . "_0", $f['name'] . "_1"];
                $fValues = array_map(fn ($n) => $filterValues[$n], $names);
                if (empty(array_filter($fValues, fn ($v) => !empty($v)))) {
                    continue;
                }

                switch ($f['type']) {
                    case 'number':
                        if (!$isRange) {
                            $suitable = (float)$info[$f['key']] >= (float)$fValues[0];
                        } else {
                            $suitable = (float)$info[$f['key']] >= (float)$fValues[0] && (float)$info[$f['key']] <= (float)$fValues[1];
                        }
                        break;
                    case 'text':
                        $suitable = str_contains(strtolower($info[$f['key']]), $fValues[0]);
                        break;
                }
                if (!$suitable) {
                    break;
                }
            }
            return $suitable;
        });
        
        if (!empty($sortFilters)) {
            foreach($sortFilters as $sf) {
                $sortOrder = $filterValues[$sf['name']];
                usort($list, function ($a, $b) use ($sortOrder, $sf) {
                    $firstValue = is_numeric((float)$a[$sf['key']]) ? (float)$a[$sf['key']] : $a[$sf['key']];
                    $secondValue = is_numeric((float)$b[$sf['key']]) ? (float)$b[$sf['key']] : $b[$sf['key']];
                    if ($sortOrder === SORT_DESC) {
                        return $secondValue - $firstValue;
                    }
                    return $firstValue - $secondValue;
                });
            }
        }

        return $list;
    }

    /**
     * Получить одну модель продуктов согласно всем фильтрам
     * 
     * @return Product
     * @throws \yii\base\InvalidArgumentException
     */
    public static function getProduct(Source $source, Filters $filters)
    {
        $source_table_name = $source->table_1;
        $source_table2_name = $source->table_2;

        $q = new FiltersQuery($source->class_1);

        $q->where([
            'and',
            $q->getSqlComparisonStatus($filters->f_comparison_status),
            $q->getSqlNoCompareItems($filters->f_no_compare, $filters->f_source),
            $q->getSqlIdGreater($source_table_name, $filters->f_id)
        ]);

        $q->addJoins($source_table_name, $source_table2_name);

        $q->orderBy($source_table_name . '.id ASC');
        $q->limit(1);
        $product = $q->one();
        $product->source = $source;
        $product->baseInfo = $product->info;
        return $product;
    }

    /**
     * Удалить данный продукт из базы данных вместе во всеми сравнениями 
     *  и соответственными правыми роварами
     * 
     * @throws \Exception
     */
    public function delete()
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            Comparison::deleteAll(['product_id' => $this->id, 'source_id' => $this->source->id]);
            $this->source->class_2::deleteAll(['asin' => $this->asin]);

            $comparisons = $this->getComparisons();
            foreach ($comparisons as $comparison) {
                if (!$comparison->delete()) {
                    throw new \Exception("Не удалось удались запись сравнения");
                }
            }

            $items = $this->_source->class_2::findAll(['asin' => $this->asin]);
            foreach ($items as $item) {
                if (!$item->delete()) {
                    throw new \Exception("Не удалось удались правый товар");
                }
            }

            if (!parent::delete()) {
                throw new \Exception("Не удалось удались левый товар");
            }
            $transaction->commit();
        } catch (\Exception $ex) {
            $transaction->rollBack();
            throw new \Exception($ex->message);
        }
    }

    /**
     * Удалить из правой таблицы только запись с id = $id_item
     *      Не путать со столбцом таблицы item_id!!! Это какая-то шляпа !!!!!!
     * @param type $id_source
     * @param type $id_item
     */
    public static function deleteItemBy($id_source, $id_item)
    {
        $source = Source::getById($id_source);
        \Yii::$app->db->createCommand()->delete($source->table_2, ['id' => $id_item])->execute();
    }

    // ========================================================================
    // Старый шлак для того что бы этот сайт работал

    /**
     * <code>
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH', 'NOCOMPARE']); // 0
     *
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']); // nocompare
     * ->get_right_items(['NOCOMPARE']); // result
     *
     * ->get_right_items(['PRE_MATCH', 'OTHER',  'NOCOMPARE', 'MISMATCH']); // match
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'OTHER', 'NOCOMPARE']); // mismatch
     * ->get_right_items(['MATCH', 'OTHER', 'MISMATCH', 'NOCOMPARE']); // pre_match
     * ->get_right_items(['PRE_MATCH', 'MATCH', 'MISMATCH', 'NOCOMPARE']); // other
     *
     *
     * @param array $del_with_status
     * @return array
     */
    public function get_right_items($del_with_status = [])
    {
        //$filter = [Result, NOCOMPARE, PRE_MATCH, MATCH, OTHER, MISMATCH, YES_NO_OTHER, ALL, ALL_WITH_NOT_FOUND,];
        $right_products = $this->addInfo;

        $res = Comparison::find()
            ->where(['product_id' => $this->id])
            ->all();

        $node_to_status = [];
        foreach ($this->_addInfo as $k => $item) {
            $status = false;

            foreach ($res as $r) {
                if ((int) $k === (int) $r->node) {
                    $status = $r->status;
                }
            }
            $status = $status ?: 'NOCOMPARE';

            $this->_addInfo[$k]->setStatus($status);

            $node_to_status[$k] = $status;
            /*
              [0] => PRE_MATCH
              [1] => MISMATCH
              [2] => NOCOMPARE
              [3] => NOCOMPARE
              [4] => NOCOMPARE
              [5] => NOCOMPARE
              [6] => MISMATCH
              [7] => NOCOMPARE
             * */
        }
        $out = [];
        $ignore_nodes = [];
        // Result = YES_NO_OTHER
        //$del_with_status = ['=' => ['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']]; // result
        //$del_with_status = ['PRE_MATCH', 'MATCH', 'OTHER', 'MISMATCH']; // nocompare
        foreach ($del_with_status as $name => $status) {
            foreach ($node_to_status as $node_id => $status_p) {
                if ($status === $status_p)
                    $ignore_nodes[] = $node_id;
            }
        }

        foreach ($ignore_nodes as $n_id_del) {
            unset($right_products[$n_id_del]);
        }

        return $right_products;
    }

    public function get_img_main()
    {
        $imgs = $this->get_img_all() ?? false;
        return $imgs[0] ?? false;
    }

    public function get_img_all()
    {
        return explode(';', $this->baseInfo['Image']);
    }

    public function addition_info_for_price($section = 'price')
    {
        $source_id = $this->source->id;

        $keys = Settings__fields_extend_price::find()
            ->where(['source_id' => $source_id, 'section' => $section])
            ->orderBy(['default' => SORT_DESC])->all();

        $b = $this->getBaseInfo();

        $out = [];
        foreach ($keys as $item) {
            if ($item->title) {
                $k = $item->title;
            } else {
                $k = $item->name;
            }
            $out[$k] = $b[$item->name];
        }

        return json_encode($out);
    }

    public static function profiles_list($source_id)
    {
        $s = Source::get_source($source_id);
        if (!$s) {
            echo '<pre>' . PHP_EOL;
            print_r('Products::profiles_list() ... не найден source');
            echo PHP_EOL;
            exit;
        }
        $source_class = $s['source_class'];
        $q = $source_class::find()->distinct(true)->select(['profile'])->asArray();

        $profile_list['{{all}}'] = 'Все';
        foreach ($q->column() as $item) {
            //$item = strtolower($item);
            $e_items = explode(',', $item);
            foreach ($e_items as $e_item) {
                $e_item = trim($e_item);
                $profile_list[$e_item] = $e_item;
            }
        }

        return $profile_list;
    }

    /**
     * Gets query for [[Aggregated]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAggregated()
    {
        return $this->hasOne(Comparison\Aggregated::className(), ['product_id' => 'id'])->where(['source_id' => $this->source->id]);
    }

    public function getUpdated()
    {
        return $this->hasOne(P_updated::class, ['p_id' => 'id'])->where(['source_id' => $this->source->id]);
    }

    public function getUser_visible()
    {
        return $this->hasOne(P_user_visible::class, ['p_id' => 'id']);
    }

    // Добавляет ключи и зачения которые хранятся в поле addInfo
    private function get_all_elements_in_array_to_first_level($array, $separator = '_', $level_prefix = '')
    {
        $_tmp = [];
        $from_deep = [];

        if (!is_array($array))
            return $array;
        foreach ($array as $k => $val) {

            if (is_array($val)) {
                $from_deep = $this->get_all_elements_in_array_to_first_level($val, $separator, $k);
                $_tmp = array_merge($_tmp, $from_deep);
            } else {
                if ($level_prefix)
                    $key = $level_prefix . $separator . $k;
                else
                    $key = $k;

                $_tmp[$key] = $val;
            }
        }

        return array_merge($_tmp, $from_deep);
    }

    public static function getFavorites($source_id, $user_type = User__favorites::TYPE_USER_DEFAULT)
    {
        return User__favorites::find()->where([
            'source_id' => $source_id,
            'user_type' => $user_type,
            'user_id' => \Yii::$app->user->id,
        ])->indexBy('product_id')->asArray()->all();
    }
}
