<?php
/* @var $source */
/* @var $source_id */
/* @var $stat */
use common\models\Source;
?>
<div>
  <?php if ( !isset($is_hint) ) { ?>    
  <h2>Import: Result (<?=$source['source_name']?>)</h2>  
  <?php } ?> 
  <div class="p-t14">
    <?php 
        if (is_array($stat)) {
            $stat = (object) $stat; 
        } else {
            $stat = json_decode($stat);
        }
    ?>      
    Результат: <?=$stat->all;?>
    <hr size="12">
    Добавляемых было: <span class="text-info"><?=isset($stat->all) ? $stat->all : '180'; ?></span> товаров <br>
    Дубликатов по Asin: <span class="text-info"><?=isset($stat->asin_duplicate) ? $stat->asin_duplicate : '80';?></span> товаров <br>
    Заменено: <span class="text-info"><?=isset($stat->replaced) ? $stat->replaced : '80';?></span> товаров <br>    
    Проигнорировано: <span class="text-info"><?=isset($stat->ignored) ? $stat->ignored : '';?></span> товаров <br>   
    Добавлено новых: <span class="text-info"><?=isset($stat->added) ? $stat->added : '100';?></span> товаров <br>
    Добавлено левых: <span class="text-info"><?=isset($stat->added_product) ? $stat->added_product : '60';?></span> товаров <br>
    Добавлено правых: <span class="text-info"><?=isset($stat->added_product_right) ? $stat->added_product_right : '40';?></span> товаров <br>    
    <br>
  </div>

  <?php if ( !isset($is_hint) ) { ?> 
  <div class="row">
    <div class="col">
        <span onclick="history.back()" class="btn btn-secondary btn-block">Назад</span>
      <!--<span onclick="window.location = '/product/index?filter-items__source=<?=$source_id?>&filter-items__profile=%D0%92%D1%81%D0%B5&filter-items__show_n_on_page=10&filter-items__id=&filter-items__target-image=&filter-items__comparing-images=&filter-items__user=&filter-items__comparisons=NOCOMPARE&filter-items__sort=&filter-items__right-item-show=0&page=1';" class="btn btn-secondary btn-block">Назад</span>-->
    </div>
  </div>
  <?php } ?>
</div>
