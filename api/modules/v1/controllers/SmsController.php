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
            $msgEnglish = "You have subscribed to receive seafood offers. Respond with 'unsubscribe' to stop receiving messages. ارسل 'عربي' لتغير اللغة ";
            $msgArabic = "لقد سجلت في خدمة عروض الأكلات البحرية. الرد ب-'unsubscribe' لايقاف الخدمة. Send 'english' to change language";

            // Not returning here, so it will continue processing a secondary message after welcome message.
            $user->sendMessageFromBot($user->language_preferred == "ar" ? $msgArabic : $msgEnglish);
            return "Sending welcome message"; //exit after sending this message. User is required to send another message for further processing
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


        $productPrice = 0.5;

        if(is_numeric($message)){
            // Check if sent message is or has a number, then parse and send message based on quantity sent.
            // Send total quote along with TODO payment link [MyFatoorah?]

            $quantityRequested = (int) $message;
            if($quantityRequested == 0){
                return $user->sendMessageFromBot("Guess you don't want any hugs.");
            }

            $totalPrice = $productPrice * $quantityRequested;


            /**
             * TAP Payment Link
             */
             // Redirect to payment gateway
             // $response = Yii::$app->tapPayments->createCharge(
             //     "$quantityRequested Hugs from Khalid Bot", // Description
             //     "Hugs", //Statement Desc.
             //     rand(1, 9999999), // Reference
             //     $totalPrice,
             //     "You",
             //     "test@test.com",
             //     "99999999",
             //     \yii\helpers\Url::to(['payment/callback'], true),
             //     \api\components\TapPayments::GATEWAY_KNET
             // );
             //
             // $responseContent = json_decode($response->content);
             //
             // // Validate that theres no error from TAP gateway
             // if(isset($responseContent->errors)) {
             //     $errorMessage = "Error: ".$responseContent->errors[0]->code. " - ". $responseContent->errors[0]->description;
             //     \Yii::error($errorMessage, __METHOD__); // Log error faced by user
             //     return $errorMessage;
             // }
             //
             // $chargeId = $responseContent->id;
             // $redirectUrl = $responseContent->transaction->url;


             /**
              * MyFatoorah Payment Link
              */
             // $merchantCode = "[Your merchant code here]";
             // $username = "[Your merchant username here]";
             // $password = "[Your merchant password here]";
             $my = \bawes\myfatoorah\MyFatoorah::test();

             $resp = $my->setPaymentMode(\bawes\myfatoorah\MyFatoorah::GATEWAY_ALL)
             ->setReturnUrl("https://google.com")
             ->setErrorReturnUrl("https://google.com")
             ->setCustomer("Khalid", "customer@email.com", "97738271")
             ->setReferenceId() //Pass unique order number or leave empty to use time()
             ->addProduct("Hug", $productPrice, $quantityRequested)
             ->getPaymentLinkAndReference();

             $redirectUrl = $resp['paymentUrl'];
             $myfatoorahRefId = $resp['paymentRef']; //good idea to store this for later status checks


             // Bitly khalid@pogi.io acct Url Shorten: Password Kk5397359!
             // Authorization: Bearer {token}
             $apiKey = "836836ac9cef2b83027accead53db574085e3a40";

            $apiv4 = 'https://api-ssl.bitly.com/v4/bitlinks';

            $data = array(
                'long_url' => $redirectUrl
            );
            $payload = json_encode($data);

            $header = array(
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            );

            $ch = curl_init($apiv4);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $result = json_decode(curl_exec($ch));

            if(isset($result->link)){
                $redirectUrl = $result->link;
            }

             // $payment->payment_gateway_transaction_id = $chargeId;

            return $user->sendMessageFromBot("That will be ". number_format($totalPrice, 3) . " KD for $quantityRequested hugs. Pay via $redirectUrl");
        }else{
            // #1: Send an offer to buy something, asking user to respond with quantity (number)
            return $user->sendMessageFromBot("I'm giving out virtual hugs for ". number_format($productPrice, 3) ." KD each. Respond with the number of hugs you'd like.");
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
