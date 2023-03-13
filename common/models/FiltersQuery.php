<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace common\models;

/**
 * @author kosten
 *
 * @function resetTables()
 * @function addTable(string $table_name)
 * $function addJoins(&$q, string $source_table_name, string $source_table2_name)
 * @function getSqlNoCompareItems()
 * @function getSqlId()
 * @function getSqlAsin()
 * @function getSqlIdGreater()
 * @function getSqlCategoriesRoot()
 * @function getSqlUsername()
 * @function getSqlComparisonStatus()
 * @function getSqlAddInfoExists()
 * @function getSqlNoInComparisons()
 * @function getSqlSettingsMessage()
 * @function getSqlProfile()
 */
class FiltersQuery extends \yii\db\ActiveQuery
{
    private $tables = [];

    public function resetTables()
    {
        $this->tables = [];
    }

    /**
     *
     * @param string $source_table_name
     * @param string $source_table2_name
     */
    public function addJoins(string $source_table_name, string $source_table2_name = '')
    {
        foreach ($this->tables as $table) {
            switch ($table) {
                case 'hidden_items':
                    $this->leftJoin('hidden_items', 'hidden_items.p_id = ' . $source_table_name . '.id ');
                    break;
                case 'comparisons_aggregated':
                    $this->leftJoin('comparisons_aggregated', 'comparisons_aggregated.product_id=' . $source_table_name . '.id');
                    break;
                case 'p_all_compare':
                    $this->leftJoin('p_all_compare', 'p_all_compare.p_id=' . $source_table_name . '.id ');
                    break;
                case 'p_updated':
                    $this->leftJoin('p_updated', 'p_updated.p_id = ' . $source_table_name . '.id ');
                    break;
                case 'comparisons':
                    $this->leftJoin('comparisons', 'comparisons.product_id = ' . $source_table_name . '.id ');
                    break;
                case 'messages':
                    $this->leftJoin('messages', 'messages.id = comparisons.messages_id');
                    break;
            }
        }
        //Условие на существование ASIN в таблице $source_table2_name
        //Изначально было. Но потом исправили косяк при импорте и оно больше не нужно
        //
        //if ($source_table2_name){
        //    $this->andWhere("`$source_table_name`.`asin` in (select `$source_table2_name`.`asin` from `$source_table2_name`)");
        //}
    }

    public function addTable(string $table_name)
    {
        if (!in_array($table_name, $this->tables)) {
            $this->tables[] = $table_name;
        }
    }

    /**
     * Фильтр проверки на отсутствие товара в таблице hidden_items
     * !!! Фильтр по правым товарам
     * @param type $f_no_compare
     * @param type $f_source id источника
     * @return array
     */
    public function getSqlNoCompareItems($f_no_compare, int $f_source): array
    {
        if ($f_no_compare && $f_source) {
            $this->addTable('hidden_items');
            return ['or',
                ['IS NOT', 'hidden_items.p_id', null],
                ['<>', 'hidden_items.source_id', $f_source]];
        } else {
            return [];
        }
    }


    /**
     * Фильтр проверки на отсутствие товара в таблице hidden_items
     * !!! Фильтр по правым товарам
     *
     * @param type $id_source
     * @param type $f_comparison_status
     * @return type
     */
    public function getSqlIsMissingHiddenItems($id_source, $f_comparison_status)
    {
        if ($id_source && (!$f_comparison_status || $f_comparison_status === 'NOCOMPARE')) {
            $this->addTable('hidden_items');
            return ['or',
                ['IS', 'hidden_items.p_id', null],
                ['<>', 'hidden_items.source_id', $id_source]];
        } else {
            return [];
        }
    }

    /**
     * Фильтр поиска по id
     *
     * @param string $source_table_name
     * @param int|string|null $f_id
     * @return array
     */
    public function getSqlId(string $source_table_name, $f_id): array
    {
        return ($f_id) ? [$source_table_name . '.id' => $f_id] : [];
    }

    /**
     * Фильтр поиска по asin
     *
     * @param string $source_table_name
     * @param string|null $f_asin
     * @return array
     */
    public function getSqlAsin(string $source_table_name, $f_asin, $f_asin_multiple): array
    {
        $f_asin_multiple = array_filter(preg_split("/\s+|\n+|(\s*,\s*)/", $f_asin_multiple), function ($v) {
            return !empty($v);
        });
        $asinCondition = [];

        if (!$f_asin && empty($f_asin_multiple)) {
            return $asinCondition;
        }

        $asinCondition = ['or'];

        if ($f_asin) {
            $asinCondition[] = ['like', $source_table_name . '.ASIN', "$f_asin%", false];
        }

        if (!empty($f_asin_multiple)) {
            foreach ($f_asin_multiple as $asin) {
                $asinCondition[] = ['like', $source_table_name . '.ASIN', "$asin%", false];
            }
        }

        return $asinCondition;
        // return ($f_asin) ?
        //     ['like', $source_table_name . '.ASIN', "$f_asin%", false] : [];
    }

    /**
     *
     * @param string $source_table_name
     * @param string|null $f_id
     * @return array
     */
    public function getSqlIdGreater(string $source_table_name, $f_id): array
    {
        return ($f_id) ? ['>', $source_table_name . '.id', $f_id] : [];
    }

    /**
     * Фильтр поиска товара по Categories: Root
     *
     * @param string $source_table_name
     * @param string|array $f_categories_root
     * @return array
     */
    public function getSqlCategoriesRoot(string $source_table_name, $f_categories_root): array
    {
        return ($f_categories_root) ?
            ['like', $source_table_name . '.info', '"Categories: Root": "' . $f_categories_root . '"'] : [];
    }

    /**
     * Фильтр username пользователя. В поле появляются имена пользователей, которые делали выбор на правых товарах.
     *
     * @param string $source_table_name
     * @param string|null $f_username
     * @return array
     */
    public function getSqlUsername(string $source_table_name, $f_username): array
    {
        if ($f_username) {
            $this->addTable('comparisons');
            return ['comparisons.user_id' => $f_username];
        } else {
            return [];
        }
        //return ($f_username)?['and', ]
        //return ($f_username)?['like', $source_table_name.'.users', $f_username]:[];
    }

    public function getSqlStatus($f_status): array
    {
        if ($f_status) {
            $this->addTable('hidden_items');
            return ['hidden_items.status' => $f_status];
        }
        return [];
    }

    /**
     * Фильтр отмеченных сравнений левого товара
     * Список статусов находится в common/models/Comparisons и является константой
     *
     * @param string|null $f_comparison_status
     * @return
     */
    public function getSqlComparisonStatus($f_comparison_status): array
    {
        switch ($f_comparison_status) {
            case 'MATCH':
            {
                $this->addTable('comparisons');
                return ['like', 'comparisons.status', 'MATCH', false];
            }
            case 'MISMATCH':
            {
                $this->addTable('comparisons');
                return ['like', 'comparisons.status', 'MISMATCH'];
            }
            case 'PRE_MATCH':
            {
                $this->addTable('comparisons');
                return ['like', 'comparisons.status', 'PRE_MATCH'];
            }
            case 'OTHER':
            {
                $this->addTable('comparisons');
                return ['like', 'comparisons.status', 'OTHER'];
            }
            case 'YES_NO_OTHER':
            {
                $this->addTable('comparisons');
                return ['and', ['IS NOT', 'comparisons.status', null], ['<>', 'comparisons.status', 'MISMATCH']];
            }
            //case 'ALL_WITH_NOT_FOUND':  return [];
            default:
                return [];
        }
    }

    /**
     * Если исходная таблица из EBay (parser_trademarkia_com)
     * Проверка источника на наличие поля add_info
     *
     * @param string $source_table_name
     * @return array
     */
    public function getSqlAddInfoExists(string $source_table_name): array
    {

        return ($source_table_name === 'parser_trademarkia_com') ?
            ['and',
                ['like', $source_table_name . '.info', 'add_info'],
                "info NOT LIKE '%\"add_info\":\"[]\"%'",
                "info NOT LIKE '%\"add_info\": \"[]\"%'"] : [];
    }

    /**
     * Если пользователь не Admin то включить записи правленые этим пользователем или никем
     *
     * @return array
     */
    public function getSqlNoInComparisons(): array
    {
        $user = \Yii::$app->user->identity;
        $is_admin = ($user && $user->isAdmin());

        if (!$is_admin && $user->id) {
            $this->addTable('comparisons');
            return ['or', ['is', 'comparisons.user_id', null], ['comparisons.user_id' => $user->id]];
            //return ["IN", 'comparisons.user_id', [$user->id, null]];
        } else {
            return [];
        }
    }

    /**
     * Включить в выборку только товары, с пометкой "Не удалось установить точное соответствие"
     * ...но это не точно
     *
     * @return array
     */
    public function getSqlSettingsMessage(): array
    {
        $this->addTable('messages');
        return ['messages.settings__visible_all' => '1'];
    }

    /**
     * Фильтр профиля. Отображается только для администратора, значит и работает только для администратора
     * Отображается список профилей товара (Prepod, General, ...)
     * Указано в таблице parser_trademarkia_com в поле profile. И список выбирвется из него
     * @param bool $is_admin
     * @param string $source_table_name
     * @param string|null $f_profile
     * @return array
     */
    public function getSqlProfile(bool $is_admin, string $source_table_name, $f_profile): array
    {
        // admin-доступ
        if ($is_admin) {
            if($f_profile && $f_profile !== '{{all}}' && $f_profile !== 'Все')
                return ['like', $source_table_name . '.profile', $f_profile];
            return [];
        }

        $add_profiles = [];
        $add_profiles[] = $f_profile;
        // general-доступ pro-доступ

        if($f_profile == 'Pro'){
            $add_profiles[] = 'General';
        }
        // free-доступ
        $sql = ["or"];
        foreach ($add_profiles as $add_profile){
            $sql[] = ['like', $source_table_name . '.profile', $add_profile . '%', false];
            $sql[] = ['like', $source_table_name . '.profile', $add_profile];
        }
        return $sql;
    }

    /**
     * надо поле у источника добавить, где прописать максимальное кол-во
     * открытых просмотров для бесплатных товаров
     *
     * individual:
     *    юзер test - подходят с профилями
     *
     *    любой зареганый видит продукт с профилем начинающимся на General
     *
     *    free - доступен даже без авторизации, но ограниченное число
     * @param string $sourceTableName
     * @param string|null $fProfile
     * @param string|null $profileType
     * @return array
     */
    public function getSqlProfileFront(string $sourceTableName, ?string $fProfile, ?string $profileType): array
    {
        $user = \Yii::$app->user->identity;
        if ($fProfile) {
            if ($profileType !== null) {
                return ['or',
                //    ['like', $sourceTableName . '.profile', 'Free'],
                    ['like', $sourceTableName . '.profile', $fProfile],
                    ['like', $sourceTableName . '.profile', $fProfile . '%', false],
                  //  ['like', $sourceTableName . '.profile', 'Prepod'],
                ];
            } else {

            }
        }

        return ['like', $sourceTableName . '.profile', 'Free'];
    }

    public function getSqlTille(string $source_table_name, $f_title): array
    {
        return ($f_title) ?
            ['like', 'info', $f_title] : [];
    }

    public function getSqlNewProducts($f_new, $last_import) {
        if (!(int)$f_new || !isset($last_import->created)) {
            return [];
        }

        return ['date_update' => $last_import->created];
    }

    public function getSqlFavorProducts(Source $source, $f_favor, $favorites) {
        if (!(int)$f_favor || empty($favorites)) {
            return [];
        }

        return ['IN', $source->table_1 . ".id", array_keys($favorites)];
    }
}