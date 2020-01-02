<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Payment */

$this->title = $model->uuid;
$this->params['breadcrumbs'][] = ['label' => 'Payments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="payment-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'uuid',
            'user_phone',
            'product_uuid',
            'quantity_purchased',
            'gateway_order_id',
            'gateway_transaction_id',
            'gateway_mode',
            'current_status:ntext',
            'amount_charged:currency',
            'net_amount:currency',
            'gateway_fee:currency',
            'udf1',
            'udf2',
            'udf3',
            'udf4',
            'udf5',
            'created_at',
            'updated_at',
            'received_callback',
        ],
    ]) ?>

</div>
