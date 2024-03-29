<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name_en')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name_ar')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'marketing_text_en')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'marketing_text_ar')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'price_per_unit')->input('text', [['placeholder' => '2.500']]) ?>

    <?= $form->field($model, 'quantity_available')->input('text', [['placeholder' => '1']]) ?>

    <?= $form->field($model, 'delivery_fee')->input('text', [['placeholder' => '1']]) ?>

    <?= $form->field($model, 'status')->dropDownList([
        \common\models\Product::STATUS_INACTIVE => 'Inactive',
        \common\models\Product::STATUS_ACTIVE => 'Active'
    ],
    ['id'=>'statusInput', 'prompt' => 'Select status']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
