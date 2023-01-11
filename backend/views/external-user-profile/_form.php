<?php

use common\models\ExternalUserProfileField;
use common\models\Source;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\ExternalUserProfile $model */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile(Url::base(true) . "/js/external-profile-form.js", [
    'position' => $this::POS_END,
    'depends' => [
        \yii\web\JqueryAsset::className(),
        \yii\bootstrap4\BootstrapPluginAsset::className()
    ]
]);
?>

<div id="profile-config-blank" class="profile-config" style="display: none">
    <hr>
    <div class="profile-config-inner" style="background: #dddddd; padding: 10px">
        <span class="field-name" style="font-weight: bold"></span>
<!--        <span class="field-comment" style="font-variant: sub"></span>-->

        <span class="btn btn-danger remove-profile-config" style="display: inline-block"><i class="fa fa-trash"></i></span>
        <div class="form-group field-externaluserprofile-externaluserprofileconfigs">

            <input type="hidden" id="externaluserprofile-externaluserprofileconfigs" class="form-control" name="ExternalUserProfile[externalUserProfileConfigs][]" value="">

            <div class="help-block"></div>
        </div>
    </div>
    <hr>
</div>

<div class="external-user-profile-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'need_confirmation')->checkbox() ?>

    <div id="profile-configs">
        <label class="form-control-lg">Profile fields</label>
        <div class="dropdown">
            <button id="add-profile-field" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Add new field
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <?php
                foreach (ExternalUserProfileField::find()->all() as $field) {
                    echo Html::a($field->name, '#', [
                        'class' => 'dropdown-item profile-field-item',
                        'data-toggle' => 'tooltip',
                        'data-placement' => 'top',
                        'title' => $field->comment,
                        'data-id' => $field->id,
                        'data-name' => $field->name,
                        'data-comment' => $field->comment,
                    ]);
                }
                ?>
            </div>
        </div>
        <?php
        foreach ($model->externalUserProfileConfigs as $i => $profileConfig) {
            ?>
            <div class="profile-config">
                <hr>
                <div class="profile-config-inner" style="background: #dddddd; padding: 10px">
                    <strong><?=$profileConfig->field->name?></strong>
                    <sub><?=$profileConfig->field->comment?></sub>

                    <span class='btn btn-danger remove-profile-config' style="display: inline-block"><i class='fa fa-trash'></i></span>
                    <?php
                    echo $form->field($model, "externalUserProfileConfigs[]")->hiddenInput([
                        'value' => $profileConfig->field->id
                    ])->label(false);
                    ?>
                </div>
                <hr>
            </div>
            <?php
        }
        ?>
    </div>
    <div id="profile-sources">
        <?php
        /** @var Source $source */
        $sourcesCheckboxes = [];

        $model->allowedSources = [];
        foreach ($model->externalUserProfileSources as $profileSource) {
            $model->allowedSources[] = $profileSource->source_id;
        }

        echo $form->field($model, 'allowedSources')->checkboxList(
            ArrayHelper::map(Source::find()->all(), 'id', 'name'),
            [
                'item' => function ($index, $label, $name, $checked, $value) use($model) {
                    // $index = 0  $label = EBAY  $name = User__source_access[source_id][]  $checked =    $value=  1
                    $checked = in_array($value, $model->allowedSources);

                    return Html::checkbox(
                        $name,
                        $checked,
                        [
                            'label' => $label,
                            'value' => $value,
                            'style' => 'margin-left: 20px'
                        ]
                    );
                },
            ]
        );
        ?>
        <hr>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<style>
    .profile-config {
        width: 300px;
        margin-left: 20px;
    }
    .remove-profile-config {
        display: inline-block;
        float: right;
    }
</style>
