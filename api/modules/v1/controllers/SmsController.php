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

        // Validate that phone number is really phone number, not adv or automated sms
        if(!preg_match('/^\+?\d+$/', $phone)){
            return [
                "operation" => "Invalid phone number disregarded"
            ];
        }

        // Check if phone exists, otherwise create account for him.
        $user = User::find()->where(['phone' => $phone])->one();
        $isNewUser = false;
        if(!$user){
            $isNewUser = true;
            $user = new User;
            $user->phone = $phone;
            $user->auth_key = "temp";

            // Detect received message language to set lang pref
            $user->language_preferred = Sms::getLanguageUsed($message) == "arabic" ? "ar" : "en";

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

        /**
         * Start Processing Response from Bot
         */
         // Convert Str to lowercase before testing for values
        $message = strtolower($sms->body);

        // If User is NEW (first time) ->
        // Send welcome message in detected lang
        // + Give Instructions to Change Language

        if($isNewUser){
            $msgEnglish = "Welcome to eCommerce bot. Respond with 'arabic' to change language.";
            $msgArabic = "";
            return $user->sendMessageFromBot($msgEnglish);
        }

        // Check if user wants to change language
        if(Yii::$app->botHelper->checkStringForWords($message, ["arabic", "عربي", "arab"])){
            // Change lang to arabic and response (Language has been changed to Arabic)
            if($user->language_preferred != "ar"){
                $user->language_preferred = "ar";
                $user->save(false);
                return $user->sendMessageFromBot("من إليوم و رايح بكلمك باللغة العربية");
            }
            return $user->sendMessageFromBot("أصلاً من زمان قاعد أكلمك باللغة العربية");
        }
        if(Yii::$app->botHelper->checkStringForWords($message, ["english"])){
            // Change lang to English and response (Language has been changed to English)
            if($user->language_preferred != "en"){
                $user->language_preferred = "en";
                $user->save(false);
                return $user->sendMessageFromBot("I'll be messaging you in English from now on.");
            }
            return $user->sendMessageFromBot("I'm already messaging you in English");
        }



        if(Yii::$app->botHelper->assertApproval($message)){
            return $user->sendMessageFromBot("Aww. I think I like you too.");
        }else if(Yii::$app->botHelper->assertRejection($message)){
            return $user->sendMessageFromBot("That's alright. This bot will find love elsewhere.");
        }else{
            return $user->sendMessageFromBot("Hello, this is your friendly bot responding. Do you love me? Respond with the word 'yes' or 'no'.");
        }

        /**
         * End of response processing
         */
        return "Done processing message received. No sms response has been sent.";
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
