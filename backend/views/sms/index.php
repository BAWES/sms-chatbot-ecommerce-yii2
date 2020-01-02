<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SmsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sms';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sms-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'uuid',
            'user_phone',
            'sender',
            'body:ntext',
            'status',
            'created_at:datetime',
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}'],
        ],
    ]); ?>


</div>
