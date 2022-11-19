<?
/* @var $source */
/* @var $source_id */
/* @var $stat */
use common\models\Source;
?>
<style>

</style>

<div>
  <h2>Import: Result (<?=$source['source_name']?>)</h2>
  <div>

    <?
    echo '<pre>'.PHP_EOL;
    print_r($stat);
    echo PHP_EOL;
    ?>
    <!--
    Результат: <br>
    Добавляемых было: 180 товаров <br>

    Дубликатов по Asin: 80 товаров <br>

    Проигнорировано: 0 товаров <br>
    Заменено: 80 товаров <br>
    Добавлено новых: 100 товаров <br>
    -->
  </div>

  <div class="row">
    <div class="col">
        <span onclick="window.location = '/product/index';" class="btn btn-secondary btn-block">Назад</span>
      <!--<span onclick="window.location = '/product/index?filter-items__source=<?=$source_id?>&filter-items__profile=%D0%92%D1%81%D0%B5&filter-items__show_n_on_page=10&filter-items__id=&filter-items__target-image=&filter-items__comparing-images=&filter-items__user=&filter-items__comparisons=NOCOMPARE&filter-items__sort=&filter-items__right-item-show=0&page=1';" class="btn btn-secondary btn-block">Назад</span>-->
    </div>
  </div>



</div>
