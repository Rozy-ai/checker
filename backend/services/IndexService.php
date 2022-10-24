<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace backend\services;

use backend\services\FilterService;

use backend\models\Source;
use backend\models\User;

/**
 * Description of IndexService
 *
 * @author koste
 */
class IndexService {
    private $filterService;
    
    public  $source_id;
    private $source_class;
    private $source_table_name;
    private $source_table_name_2;

    public function __construct(FilterService $filterService) {
        $this->filterService = $filterService;
    }

    public function loadParamsToFilters($params) {
        $this->filterService->filter_items__profile       = $params['filter-items__profile']         ?? null;
        $this->filterService->f_items__right_item_show    = $params['filter-items__right-item-show'] ?? null;
        $this->filterService->f_items__show_n_on_page     = $params['filter-items__show_n_on_page']  ?? 10;
        $this->filterService->f_items__id                 = $params['filter-items__id']              ?? null;
        $this->filterService->f_items__comparing_images   = $params['filter-items__comparing-images']?? null;
        $this->filterService->f_items__target_image       = $params['filter-items__target-image']    ?? null;
        $this->filterService->f_items__user               = $params['filter-items__user']            ?? null;
        $this->filterService->f_items__comparisons        = $params['filter-items__comparisons']     ?? null;
        $this->filterService->f_items__no_compare         = $params['filter-items__no-compare']      ?? null;
    }

    public function set_source($source_id = false) {
        
        if ($source_id === false) {
            /*
              if ($s_source = (new Session())->get('source')){
              $source_id = $s_source['id'];
              }else{
              $source_id = 1;
              }
             */
            $source_id = 1;
        }

        $source = Source::findOne(['id' => (int) $source_id]);
        if (!$source) {
            return null;
        }
        //(new Session())->set('source',$source);
        $this->source_id = $source_id;
        $this->source_table_name = $source->table_1;
        $this->source_table_name_2 = $source->table_2;
        $this->source_class = 'common\models\\' . ucfirst($source->table_1);
        
        return $this->source_id;
    }

    public function is_source_access($user_id = false, $source_id = false) {
        if (!$user_id) {
            $user_id = \Yii::$app->getUser()->id;
        }
        if (!$source_id) {
            $source_id = Source::get_source()['source_id'];
        }

        $u = User::find()->where(['id' => $user_id])->limit(1)->one();
        $res = $u->user__source_access;

        // если нет записей... то можно все источники
        if (!$res)
            return true;
        foreach ($res as $r) {
            if ((int) $r->source_id === (int) $source_id)
                return true;
        }
        return false;
    }

}
