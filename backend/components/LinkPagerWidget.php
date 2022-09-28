<?php
/**
 * Created by PhpStorm.
 * User: Professional
 * Date: 13.03.2022
 * Time: 12:26
 */

namespace backend\components;


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

class LinkPagerWidget extends LinkPager
{
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }
        
        $buttons = [];
        $currentPage = $this->pagination->getPage();
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        list($beginPage, $endPage) = $this->getPageRange();
        if (($page = $currentPage - 1) < 0) {
            $page = 0;
        }
        if ($this->firstPageLabel){
            $beginPage++;
        }
        if ($this->lastPageLabel) {
            $endPage--;
        }
        
        // prev page
        $buttons[] = $this->renderPageButton($this->prevPageLabel, $currentPage - 1, $this->prevPageCssClass, $currentPage <= 0, false);
        $buttons[] = $this->renderPageButton($this->firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, $currentPage <= 0);
        if ($beginPage >= 2){
            $buttons[] = $this->renderPageButton('...', null, "pagination-separator", true, false);
        }
   
        // internal pages
        if ($firstPageLabel < $beginPage){
        }
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, $this->disableCurrentPageButton && $i == $currentPage, $i == $currentPage);
        }
        
        // last page
        if ($pageCount > $endPage + 2){
            $buttons[] = $this->renderPageButton('...', null, "pagination-separator", true, false);
        }

        $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, $currentPage >= $pageCount - 1);
    
        $buttons[] = $this->renderPageButton($this->nextPageLabel, $currentPage + 1, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
    
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'ul');
        return Html::tag($tag, implode("\n", $buttons), $options);
    }
}
