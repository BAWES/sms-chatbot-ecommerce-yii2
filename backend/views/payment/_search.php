<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="payment-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'uuid') ?>

    <?= $form->field($model, 'user_phone') ?>

    <?= $form->field($model, 'product_uuid') ?>

    <?= $form->field($model, 'quantity_purchased') ?>

    <?= $form->field($model, 'gateway_order_id') ?>

    <?php // echo $form->field($model, 'gateway_transaction_id') ?>

    <?php // echo $form->field($model, 'gateway_mode') ?>

    <?php // echo $form->field($model, 'current_status') ?>

    <?php // echo $form->field($model, 'amount_charged') ?>

    <?php // echo $form->field($model, 'net_amount') ?>

    <?php // echo $form->field($model, 'gateway_fee') ?>

    <?php // echo $form->field($model, 'udf1') ?>

    <?php // echo $form->field($model, 'udf2') ?>

    <?php // echo $form->field($model, 'udf3') ?>

    <?php // echo $form->field($model, 'udf4') ?>

    <?php // echo $form->field($model, 'udf5') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'received_callback') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
