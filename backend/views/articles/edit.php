<?
/* @var $item backend\controllers\ArticlesController */

use mihaildev\ckeditor\CKEditor;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

//$this->title = Yii::t('site', 'Редактирование поля таблицы');
?>

<div class="[ ARTICLE-EDIT ] article-edit">
<? $form = ActiveForm::begin(['id' => 'articles__edit-item','class' => 'form-control']); ?>

<?= Html::activeHiddenInput($item,'id'); ?>
<?= $form->field($item, 'title')->input('text') ?>
<?= $form->field($item, 'html')->textarea()->widget(CKEditor::className(),[
  'editorOptions' => [
    'preset' => 'full', //разработанны стандартные настройки basic, standard, full данную возможность не обязательно использовать
    'inline' => false, //по умолчанию false
  ],
]);
?>
<?//= $form->field($item, 'date')->input('date') ?>

<?= Html::submitButton("Сохранить", ['class' => 'btn btn-primary btn-block'])?>

<? ActiveForm::end() ?>
</div>
<?= Html::a("Удалить",'/articles/del?id='.$item->id ,['class' => 'btn btn-secondary btn-block','style' => 'margin-top: 5px'])?>


