<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\PaymentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Payments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'uuid',
            'user_phone',
            'product_uuid',
            'quantity_purchased',
            // 'gateway_order_id',
            //'gateway_transaction_id',
            //'gateway_mode',
            'current_status:ntext',
            //'amount_charged',
            //'net_amount',
            //'gateway_fee',
            //'udf1',
            //'udf2',
            //'udf3',
            //'udf4',
            //'udf5',
            //'created_at',
            //'updated_at',
            //'received_callback',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
