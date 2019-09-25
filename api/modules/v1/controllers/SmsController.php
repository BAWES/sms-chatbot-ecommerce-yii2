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
            $user->status = User::STATUS_ACTIVE;
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

        // If User is NEW (first time)
        // Send welcome message + Give Instructions to Change Language / Unsubscribe
        if($isNewUser){
            $msgEnglish = "This is Khalid's sales bot. Respond with 'unsubscribe' to stop receiving messages. ارسل 'عربي' لتغير اللغة ";
            $msgArabic = "أهلاً بك. أنا روبوت مبيعات صناعة خالد المطوع. يرجى الرد ب-'unsubscribe' لايقاف الخدمة. Send 'english' to change language";

            // Not returning here, so it will continue processing a secondary message after welcome message.
            $user->sendMessageFromBot($user->language_preferred == "ar" ? $msgArabic : $msgEnglish);
        }

        // Check if user wants to "unsubscribe"
        if(Yii::$app->botHelper->checkStringForWords($message, ["unsubscribe"])){
            // If Active, make inactive
            if($user->status == User::STATUS_ACTIVE){
                $msgEnglish = "You have been unsubscribed. Send 'enable' to enable the service.";
                $msgArabic = "تم الغاء الخدمة. أرسل". "'enable'" . "لتفعيل الخدمة";
                $user->status = User::STATUS_INACTIVE;
                $user->save(false);
                return $user->sendMessageFromBot($user->language_preferred == "ar" ? $msgArabic : $msgEnglish);
            }

            // Else say that they're already inactive
            $msgEnglish = "You are already unsubscribed. Send 'enable' to enable the service.";
            $msgArabic = "تم الغاء الخدمة. أرسل". "'enable'" . "لتفعيل الخدمة";
            return $user->sendMessageFromBot($user->language_preferred == "ar" ? $msgArabic : $msgEnglish);
        }

        // Check if user wants to re subscribe by sending "enable"
        if(Yii::$app->botHelper->checkStringForWords($message, ["enable"])){
            // If STATUS_INACTIVE, make active
            if($user->status == User::STATUS_INACTIVE){
                $msgEnglish = "Your account has been enabled.";
                $msgArabic = "تم تفعيل حسابك";
                $user->status = User::STATUS_ACTIVE;
                $user->save(false);
                return $user->sendMessageFromBot($user->language_preferred == "ar" ? $msgArabic : $msgEnglish);
            }

            // Else say that they're already active
            $msgEnglish = "Your account is already active.";
            $msgArabic = "حسابك فعال";
            return $user->sendMessageFromBot($user->language_preferred == "ar" ? $msgArabic : $msgEnglish);
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


        // TODO #1: Send an offer to buy something, asking user to respond with quantity (number)
        // TODO #2: Check do calculation of number * price. Send total quote along with payment link [MyFatoorah?]
        // TODO #3: Store payment status / send receipts / etc. [After showing demo maybe?]
        
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
