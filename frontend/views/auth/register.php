<?php

/* @var $this yii\web\View */
/* @var $form ActiveForm */
/* @var $model Register */

use common\models\ExternalUserProfile;
use common\models\ExternalUserProfileFieldVal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use frontend\models\forms\auth\Register;
use yii\helpers\Url;

$this->title = 'Регистрация';
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@app/views/auth/_base.php');

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
    <div id="ex-user-profile-defs"></div>

<div class="site-login">
    <h2 class="app-h2 mb-05-rem"><?= Html::encode($this->title) ?></h2>
    <div class="row">
        <div class="col-lg-9">
            <?php $form = ActiveForm::begin([
                'id' => 'register-form',
                'enableAjaxValidation' => true,
            ]); ?>

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
                }

                echo '</div>';
            }
            ?>

            <?= $form->errorSummary($model)?>
                <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'email')->input('email', ['maxlength' => true]) ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'password_confirm')->passwordInput() ?>

            <?= $form->field($model, 'ex_profile_id')->dropDownList(
                ArrayHelper::map(
                    array_merge([null=>'Не выбран'], ExternalUserProfile::find()->all()), 'id', 'name'
                )
            ) ?>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="login-button">Зарегистрироваться</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php
$this->endContent();
