<?php

use common\models\ExternalUser;
use common\models\ExternalUserProfile;
use common\models\ExternalUserProfileConfig;
use common\models\ExternalUserProfileFieldVal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var backend\models\ExternalUser $model */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile(Url::base(true) . "/js/ex-user-profile.js", [
    'position' => $this::POS_END,
    'depends' => [
        \yii\web\JqueryAsset::className(),
        \yii\bootstrap4\BootstrapPluginAsset::className()
    ]
]);
?>

<style>
    .ex-user-profile {
        display: none;
    }
    #ex-user-profile-actual {
        padding: 0 100px;
    }
    #ex-user-profile-actual > .ex-user-profile {
        display: block;
    }
</style>
<!--<div id="ex-user-profile-field-vals">-->
<!--    --><?php
//    foreach ($model->externalUserProfileFieldVals as $fieldVal) {
//        echo '<div class="ex-user-profile-field-val" data-field_id="'.$fieldVal->id.'">' . Html::encode($fieldVal->value) . '</div>';
//    }
//    ?>
<!--</div>-->
<div id="ex-user-profile-defs">

</div>

<?php $form = ActiveForm::begin(['id' => 'external-user-form', 'enableAjaxValidation' => true]); ?>

<?php
/** @var ExternalUserProfile $profile */
foreach (ExternalUserProfile::find()->all() as $profile) {
    echo "<div class='ex-user-profile profile-id-{$profile->id}'>";

    foreach ($profile->externalUserProfileConfigs as $i => $fieldConfig) {
        $fieldActualVal = new ExternalUserProfileFieldVal();
        $fieldActualVal->field_id = $fieldConfig->field_id;

        foreach ($model->externalUserProfileFieldVals as $fieldVal) {
            if ($fieldVal->field_id == $fieldConfig->field_id) {
                $fieldActualVal->value = $fieldVal->value;
                break;
            }
        }

        echo '<div class="ex-user-profile-field-val-wrap">';
        echo '<label>' . $fieldConfig->field->name . '</label>';
        echo '<p>' . $fieldConfig->field->comment . '</p>';
        echo $form->field($fieldActualVal, 'value')->textInput([
                'name' => "ExternalUserProfileFieldVal[$i][value]",
            'id' => 'externaluserprofilefieldval-value'.$fieldConfig->field_id . "-" . $i
        ])->label(false);
        echo $form->field($fieldActualVal, 'field_id')->hiddenInput([
            'name' => "ExternalUserProfileFieldVal[$i][field_id]",
            'id' => 'externaluserprofilefieldval-field_id'.$fieldConfig->field_id . "-" . $i
        ])->label(false);
        echo '</div>';

        //todo: сгенерировать ExternalUserProfileConfig заного?
        // просто вывести ? ExternalUserProfileFieldVal $form->field($fieldActualVal, '');
        // сохранять сначала ExternalUserProfile новый или с айди, и перезаписывать все ExternalUserProfileFieldVal
        // для этого пользователя(field_id id)?
    }

    echo '</div>';
}
?>

<div class="external-user-form">

    <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>
    <?= $form->field($model, 'status')->dropDownList(ExternalUser::STATUSES, ['prompt' => '']) ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ex_profile_id')->dropDownList(
            ArrayHelper::map(
                    array_merge([null=>'Не выбран'], ExternalUserProfile::find()->all()), 'id', 'name'
            )
    ) ?>
    <div id="ex-user-profile-actual">

    </div>


    <div class="form-group">
        <button type="submit" class="btn btn-success">Сохранить</button>
    </div>



    <?php ActiveForm::end(); ?>
</div>

<script>
    window.addEventListener("load", function () {
        jQuery(function ($) {
            $('.ex-user-profile').appendTo('#ex-user-profile-defs');
            $('#externaluser-ex_profile_id').on('change', function (e) {
                let id = $(this).children(':selected').val();
                $('#ex-user-profile-actual').children().appendTo('#ex-user-profile-defs');
                if (!id)
                    return;
                $('.profile-id-' + id).appendTo('#ex-user-profile-actual');
            });
        });
    });
</script>
