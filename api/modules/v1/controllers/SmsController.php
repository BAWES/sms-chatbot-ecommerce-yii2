<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\filters\Cors;
use common\models\User;
use common\models\Sms;

/**
 * Base SMS Controller
 */
class SmsController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Allow XHR Requests from our different subdomains and dev machines
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => Yii::$app->params['allowedOrigins'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [
                    'X-Pagination-Current-Page',
                    'X-Pagination-Page-Count',
                    'X-Pagination-Per-Page',
                    'X-Pagination-Total-Count'
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction',
            // optional:
            'collectionOptions' => ['GET', 'POST', 'HEAD', 'OPTIONS'],
            'resourceOptions' => ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        ];
        return $actions;
    }


    /**
     * Called when sms is received
     */
    public function actionReceive()
    {
        $phone = Yii::$app->request->getBodyParam("phone");
        $message = Yii::$app->request->getBodyParam("message");

        // Check if phone exists, otherwise create account for him.
        $user = User::find()->where(['phone' => $phone])->one();
        if(!$user){
            $user = new User;
            $user->phone = $phone;
            $user->auth_key = "temp";
            $user->save();
        }

        // Once that is done, we need to add sms to user record
        if($user){
            $sms = new Sms;
            $sms->user_phone = $phone;
            $sms->sender = Sms::SENDER_USER;
            $sms->body = $message;
            $sms->save();
        }

        // Send a message back from Bot
        if(strtolower($sms->body) == 'yes'){
            $user->sendMessageFromBot("Aww. I think I like you too.");
        }else if(strtolower($sms->body) == 'no'){
            $user->sendMessageFromBot("That's alright. This bot will find love elsewhere.");
        }else{
            $user->sendMessageFromBot("Hello, this is your friendly bot responding. Do you love me? Respond with the word 'yes' or 'no'.");
        }

        return [
            "operation" => "Message Stored"
        ];
    }


    /**
     * Called to poll for a single message that is required to be
     * sent by chatbot. That message will be marked as sent as soon as polled
     */
    public function actionPollForMessageToSend()
    {
        $messageToSend = Sms::find()
                            ->where([
                                'status' => Sms::STATUS_UNSENT,
                                'sender' => Sms::SENDER_BOT
                            ])
                            ->orderBy('created_at DESC')
                            ->limit(1)
                            ->one();

        // If no messages to send
        if(!$messageToSend){
            return false;
        }

        // Mark message as sent
        $messageToSend->status = Sms::STATUS_SENT;
        $messageToSend->save(false);

        return [
            "phone" => $messageToSend->user_phone,
            "message" => $messageToSend->body
        ];
    }


}
