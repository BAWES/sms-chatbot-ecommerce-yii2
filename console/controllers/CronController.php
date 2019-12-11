<?php

namespace console\controllers;
use \DateTime;

use yii\helpers\Console;
use common\models\Payment;
use yii\db\Expression;


/**
 * All Cron actions related to this project
 */
class CronController extends \yii\console\Controller {

    /**
     * Used for testing only
     */
    public function actionIndex(){
        $this->stdout("Test Email Function \n", Console::FG_RED, Console::BOLD);
    }

    /**
     * Method called to find old transactions that haven't received callback and force a callback
     */
    public function actionUpdateTransactions(){

        $now = new DateTime('now');
        $payments = Payment::find()
                    ->where("received_callback = 0")
                    ->andWhere(['<', 'payment_created_at', new Expression('DATE_SUB(NOW(), INTERVAL 5 MINUTE)')])
                    ->all();

        if ($payments) {
            foreach ($payments as $payment) {
                try{
                    $payment = Payment::updatePaymentStatusFromTap($payment->payment_gateway_transaction_id);
                    $payment->received_callback = true;
                    $payment->save();
                } catch (\Exception $e){
                    \Yii::error("[Issue checking status] ".$e->getMessage() ,__METHOD__);
                }
            }
        }else{
            $this->stdout("All Payments received callback \n", Console::FG_RED, Console::BOLD);
            return self::EXIT_CODE_NORMAL;
        }

        $this->stdout("Payments status updated successfully \n", Console::FG_RED, Console::BOLD);
        return self::EXIT_CODE_NORMAL;
    }
}
