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
  <div>
    <?php
    echo '<pre>'.PHP_EOL;
    print_r($stat);
    echo PHP_EOL;
    ?>
       
    Результат: <br>
    Добавляемых было: <?=isset($stat['all']) ? $stat['all'] : '180'; ?> товаров <br>
    Дубликатов по Asin: <?=isset($stat['asin_duplicate']) ? $stat['asin_duplicate'] : '80';?> товаров <br>
    Заменено: <?=isset($stat['replaced']) ? $stat['replaced'] : '80';?> товаров <br>    
    Проигнорировано: <?=isset($stat['ignored']) ? $stat['ignored'] : '';?> товаров <br>   
    Добавлено новых: <?=isset($stat['added']) ? $stat['added'] : '100';?> товаров <br>
    
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
