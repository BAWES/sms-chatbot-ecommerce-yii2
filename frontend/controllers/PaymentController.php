<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

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
        $payment->payment_mode = $gateway;
        $payment->investor_id = Yii::$app->user->identity->investor_id;
        $payment->project_id = $project->project_id;
        $payment->payment_amount_charged = 100;
        $payment->payment_current_status = "Redirected to payment gateway";
        $payment->save();

        Yii::info("[Payment Attempt Started] ".Yii::$app->user->identity->investor_name.' start attempting making a payment '.Yii::$app->formatter->asCurrency($amountToInvest, '',[\NumberFormatter::MAX_SIGNIFICANT_DIGITS=>10]) , __METHOD__);

        // Redirect to payment gateway
        $response = Yii::$app->tapPayments->createCharge(
            "Equity in ".$project->project_name_en, // Description
            "TheCapital", //Statement Desc.
            $payment->payment_uuid, // Reference
            $amountToInvest,
            "Buyer Khalid",
            "demo@khalid.com",
            "99811042",
            Url::to(['payment/callback'], true),
            $gateway
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

        $payment->payment_gateway_transaction_id = $chargeId;
        $payment->save(false);

        return $this->redirect($redirectUrl);

    }

}
