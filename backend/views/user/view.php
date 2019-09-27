<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->phone], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->phone], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'phone',
            'name',
            'email:email',
            // 'auth_key',
            'statusText',
            'language_preferred',
            // 'status',
            'last_sms_sent_at:datetime',
            'last_sms_received_at:datetime',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <!-- 'uuid',
    'user_phone',
    'sender',
    'body:ntext',
    'status',
    'created_at',
    'updated_at', -->

    <h1>Message History</h1>



    <?php foreach($model->sms as $sms){
        $senderUser = ($sms->sender == \common\models\Sms::SENDER_USER);
        ?>
        <div style='padding: 20px; padding-top:12px; margin: 3px; background: <?= $senderUser? 'lightblue' : 'lightpink' ?>;'>
            <span style='font-size: 1.1em;'><?= $sms->body ?></span>

            <div style='margin-top:10px; margin-bottom: -10px; font-size:0.8em;'><?= Yii::$app->formatter->asDatetime($sms->created_at) ?></div>
        </div>
    <?php } ?>

</div>
