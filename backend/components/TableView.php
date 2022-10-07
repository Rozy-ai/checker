<?php
/**
 * Created by PhpStorm.
 * User: Professional
 * Date: 19.03.2022
 * Time: 12:17
 */

namespace backend\components;


use yii\grid\Column;
use yii\grid\GridView;
use yii\helpers\Html;

class TableView extends GridView
{
    public function renderItems(){
        $caption = $this->renderCaption();
        $columnGroup = $this->renderColumnGroup();
        $tableHeader = $this->showHeader ? $this->renderTableHeader() : false;
        $tableBody = $this->renderTableBody();

        $tableFooter = false;
        $tableFooterAfterBody = false;

        if ($this->showFooter) {
            if ($this->placeFooterAfterBody) {
                $tableFooterAfterBody = $this->renderTableFooter();
            } else {
                $tableFooter = $this->renderTableFooter();
            }
        }

        $content = array_filter([
            $caption,
            $columnGroup,
            $tableHeader,
            $tableFooter,
            $tableBody,
            $tableFooterAfterBody,
        ]);

        return implode("\n", $content);
    }

    /**
     * Renders the table header.
     * @return string the rendering result.
     */
    public function renderTableHeader(){
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderHeaderCell();
        }
        $content = Html::tag('table', implode('', $cells), $this->headerRowOptions);
        if ($this->filterPosition === self::FILTER_POS_HEADER) {
            $content = $this->renderFilters() . $content;
        } elseif ($this->filterPosition === self::FILTER_POS_BODY) {
            $content .= $this->renderFilters();
        }

        return $content;
    }


    /**
     * Renders the filter.
     * @return string the rendering result.
     */
    public function renderFilters(){
        if ($this->filterModel !== null) {
            $cells = [];
            foreach ($this->columns as $column) {
                /* @var $column Column */
                $cells[] = $column->renderFilterCell();
            }

            return Html::tag('div', implode('', $cells), $this->filterRowOptions);
        }

        return '';
    }

    /**
     * Renders a data cell.
     * @param mixed $model the data model being rendered
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data item among the item array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    public function renderDataCell($model, $key, $index){
        if ($this->contentOptions instanceof Closure) {
            $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
        } else {
            $options = $this->contentOptions;
        }

        return Html::tag('td', $this->renderDataCellContent($model, $key, $index), $options);
    }

    /**
     * Renders a table row with the given data model and key.
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the model array returned by [[dataProvider]].
     * @return string the rendering result
     */
    public function renderTableRow($model, $key, $index){
        $cells = [];
        /* @var $column Column */
        foreach ($this->columns as $column) {
            $cells[] = $column->renderDataCell($model, $key, $index);
        }
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;

        return Html::tag('div', implode('', $cells), $options);
    }

    /**
     * Runs the widget.
     */
    public function run(){


      if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
        $content = preg_replace_callback('/{\\w+}/', function($matches){
          $content = $this->renderSection($matches[0]);

          return $content === false ? $matches[0] : $content;
        }, $this->layout);
      } else {
        $content = $this->renderEmpty();
      }
      echo $content;
    }
}
