<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Sms */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sms-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sender')->textInput() ?>

    <?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
