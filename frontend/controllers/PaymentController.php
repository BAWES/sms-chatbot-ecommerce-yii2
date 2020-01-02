<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\helpers\Url;
use common\components\TapPayments;

/**
 * Payment controller
 * Controller for processing payments and receiving callbacks from gateways
 */
class PaymentController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
    }

    /**
     * Process callback from TAP payment gateway
     * @param string $tap_id
     * @return mixed
     */
    public function actionCallback($tap_id)
    {
        // try{
        //     $paymentRecord = Payment::updatePaymentStatusFromTap($tap_id);
        //
        //     $paymentRecord->received_callback = true;
        //     $paymentRecord->save();
        //
        //     if($paymentRecord->payment_current_status != 'CAPTURED'){  //Failed Payment
        //
        //         Yii::$app->session->setFlash('error', "There seems to be an issue with your payment, please try again.");
        //         // Redirect back to project page with message
        //         return $this->redirect(['project/view',
        //             'id' => $paymentRecord->project_id
        //         ]);
        //     }
        //
        //     return $this->redirect(['investment/view', 'payid' => $paymentRecord->payment_uuid]);
        //
        //
        // }catch(\Exception $e){
        //     Yii::info($e->getMessage(), __METHOD__);
        //     throw new NotFoundHttpException($e->getMessage());
        // }

        return "Received tap id $tap_id";
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionProcess()
    {
        // Create new payment record
        $payment = new \common\models\Payment;
        $payment->gateway_mode = TapPayments::GATEWAY_KNET;

        $payment->user_phone = "+96599811042"; //TODO use actual user phone
        $payment->product_uuid = "857435c2-2d63-11ea-9e34-dcf8456f5f8d"; // TODO use actual product uuid
        $payment->quantity_purchased = 5;//TODO
        $payment->amount_charged = 100;//TODO

        $payment->current_status = "Redirected to payment gateway";
        $payment->save();


        // Redirect to payment gateway
        $response = Yii::$app->tapPayments->createCharge(
            "Purchase of product name", // Description
            "SmsBot", //Statement Desc.
            $payment->uuid, // Reference
            $payment->amount_charged,
            "Buyer Khalid",
            "demo@khalid.com",
            "99811042",
            Url::to(['payment/callback'], true),
            $payment->gateway_mode
        );

        $responseContent = json_decode($response->content);

        // Validate that theres no error from TAP gateway
        if(isset($responseContent->errors)) {
            $errorMessage = "Error: ".$responseContent->errors[0]->code. " - ". $responseContent->errors[0]->description;
            \Yii::error($errorMessage, __METHOD__); // Log error faced by user
            \Yii::$app->getSession()->setFlash('error', $errorMessage);

            return $this->redirect(['view',
                'id' => $project->project_id,
            ]);
        }

        $chargeId = $responseContent->id;
        $redirectUrl = $responseContent->transaction->url;

        $payment->gateway_transaction_id = $chargeId;
        $payment->save(false);

        return $this->redirect($redirectUrl);

    }

}
