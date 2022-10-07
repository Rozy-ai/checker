<?php

/* @var $this yii\web\View */
/* @var $a backend\controllers\ArticlesController */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent" style="display: none">
        <h1 class="display-4">Congratulations!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <p><a class="btn btn-lg btn-success" href="http://www.yiiframework.com">Get started with Yii</a></p>
    </div>

  <div style="margin-top: 70px">

  </div>

    <div class="body-content">

      <div class="row">

        <? foreach ($items as $item):?>
          <div class="col-lg-4" style="margin-top: 10px">
            <h2><?=$item->title?></h2>
            <p>
              <?=$item->html;?>
            </p>
          </div>
        <? endforeach;?>



      </div>

    </div>

</div>
