<?php

/**
 * Created by PhpStorm.
 * User: Professional
 * Date: 14.03.2022
 * Time: 14:11
 */

namespace backend\components;

use backend\models\Settings__source_fields;
use common\models\Comparison;
use common\models\Product;
use backend\models\Settings__fields_extend_price;
use backend\models\User;
use yii\base\Widget;

class TopSlider extends Widget {
    private $_options = [];
    private $is_admin;
    public $is_detail_view;
    public $is_hide_red = false;
    public $number_page_current;
    public $product;
    public $f_comparison_status;
    public $f_profile;
    public $f_no_compare;
    public $compare_item;
    public $source;

    function getVariablesLeft($product) {
        $p_key = Settings__fields_extend_price::get_default_price($this->source->id)->name ?: 'Price Amazon';
        $footer_left = '<span><span class="__blue-title">BSR:</span>' . number_format($product->baseInfo["Sales Rank: Current"], 0, '', ' ') . ' </span>' .
                '<span><span class="__blue-title">Sales30:</span>' . $product->baseInfo["Sales Rank: Drops last 30 days"] . ' </span>' .
                '<span><span class="__blue-title">Price:</span>' . $product->baseInfo[$p_key] ?: '-' . ' </span>' .
                '<span><span class="__blue-title">Status:</span>' . $product->baseInfo["Brand_R"] . ' </span>';
        //'<span><span class="__blue-title">ASIN:</span>'.$product->baseInfo["ASIN"].' </span>'.
        $this->is_admin ? $footer_left . '<span><span class="__blue-title">Profile:</span>' . $product->profile . ' </span>' : '';

        return [
            'description_left' => $product->baseInfo['Title'],
            'img_left' => explode(';', $product->baseInfo['Image'])[0],
            'footer_left' => $footer_left
        ];
    }

    private function getStock($item) {
        if ($grade_key = $item->gradeKey ?: 0) {
            return '<span class="_slider-item__cnt-1">
                            <span class="cnt-1__stock-title __blue-title">Stock:</span>
                            <span class="_grade cnt-1__stock-n">' . $grade_key . ' </span>
                        </span>';
        }
    }

    private function getSales($item) {
        if ($sales_key = $item->salesKey) {
            $sales_key_preg = preg_replace('|\D|', '', $sales_key);
            return '<span class="_slider__sales sales"><span class="__blue-title">Sales:</span>' . $sales_key_preg . ' </span>';
        } else {
            return '';
        }
    }

    private function getROIAli($item) {
        return '<span class="slider-item__cnt-1">
                        <span class="cnt-1__stock-title __blue-title">ROI:</span>
                        <span class="grade cnt-1__stock-n">' . $item->ROI_Ali . '</span>
                    </span>';
    }

    /**
     * 
     * @param type $source - источник
     * @param type $item - продукт, из которого берутся данные
     * @param type $is_short Скраткий или полный список отображать
     * @return type
     */
    function getVariablesRight($source, $item, $is_short = false) {
        $footer_right = '';

        if ($is_short) {
            if ($source->name === 'EBAY') {
                $footer_right .= $this->getSales($item);
                $footer_right .= $this->getStock($item);
            } elseif ($source->name === 'CHINA') {
                $footer_right .= getROIAli($item);
            }
            $footer_right .= '<span><span class=" __blue-title">Price:</span>' . $item->price . ' </span>';
        } else {
            if ($source->name === 'EBAY') {
                $footer_right .= $this->getSales($item);
                $footer_right .= $this->getStock($item);
                $footer_right .= '<span><span class=" __blue-title">Price:</span>' . $item->price . ' </span>' .
                        '<span><span class=" __blue-title">Rating:</span>' . $item->rating . ' </span>' .
                        '<span><span class=" __blue-title">ROI:</span>' . $item->ROI . ' </span>' .
                        '<span><span class=" __blue-title">Margin:</span>' . $item->Margin . ' </span>';
            } elseif ($source->name === 'CHINA') {
                $footer_right = '<span><span class=" __blue-title">MQO:</span>' . $item->MOQ_Ali . ' </span>' .
                        '<span><span class=" __blue-title">Total:</span>' . $item->Total_Ali . ' </span>' .
                        '<span><span class=" __blue-title">Price:</span>' . $item->price . ' </span>' .
                        '<span><span class=" __blue-title">ROI:</span>' . $item->ROI_Ali . ' </span>' .
                        '<span><span class=" __blue-title">Rating:</span>' . $item->rating . ' </span>';
            };
        };

        $images_right = preg_split("/[; |,|\|]/", $item->srcKey);

        return [
            'description_right' => $item->r_Title,
            'img_right' => $images_right[0],
            'footer_right' => $footer_right,
            'images_right' => $images_right,
            'count_images_right' =>
            "<div class='slider-camera-container float-right'>" .
            "<span class='slider-text'>" . count(preg_split("/[; |,|\|]/", $item->images_E)) .
            "</span>" .
            "<img class='fa slider-camera' src='/img/slider_camera.png' alt=''>" .
            "</div>"
        ];
    }

    public function run() {
        $this->is_admin = $this->is_admin;
        $source = $this->product->source;

        //$this->_options['srcKey'] = $srcKey = Settings__source_fields::name_for_source('srcKey', $source->id);
        //$this->_options['urlKey'] = Settings__source_fields::name_for_source('urlKey', $source->id);
        //$this->_options['class'] = '_sliderTop';
        //$this->_options['gradeKey'] = Settings__source_fields::name_for_source('gradeKey', $source->id);
        //$this->_options['price'] = Settings__source_fields::name_for_source('price', $source->id);
        //$this->_options['salesKey'] = Settings__source_fields::name_for_source('salesKey', $source->id);
        
        $items = $this->product->addInfo;

        return $this->render(($this->is_detail_view) ? 'slider-v-2' : 'slider-v-1', [
            'is_admin'              => $this->is_admin,
            'number_page_current'   => $this->number_page_current,
            'option_class_slider'   => $this->_options['class'],
            'option_sales_key'      => $this->_options['salesKey'],
            'option_del_btn'        => $this->_options['option_del_btn'],
            'product'               => $this->product,
            'f_comparison_status'   => $this->f_comparison_status,
            'f_profile'             => $this->f_profile,
            'items'                 => $items,
            'no_compare'            => $this->f_no_compare,
            'compare_item'          => $this->compare_item,
            'source'                => $this->source
        ]);
    }
}
