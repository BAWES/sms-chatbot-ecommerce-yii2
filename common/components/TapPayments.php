<?php

namespace common\components;

use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use Yii;

/**
 * TapPayments class for payment processing
 *
 * @author Khalid Al-Mutawa <khalid@bawes.net>
 * @link http://www.bawes.net
 */
class TapPayments extends Component {

    /**
     * @var string Generated link sends user directly to KNET portal
     */
    const GATEWAY_KNET = "src_kw.knet";
    /**
     * @var string Generated link sends user directly to VISA/MASTER portal
     */
    const GATEWAY_VISA_MASTERCARD = "src_card";

    /**
     * @var float gateway fee charged by portal
     */
    public $knetGatewayFee = 0.005; // How much is charged per KNET transaction

    /**
     * @var float gateway fee charged by portal
     */
    public $creditcardGatewayFeePercentage = 0.03; // How much is charged per Creditcard transaction

    /**
     * @var string secret api key
     */
    public $secretApiKey = "sk_test_cV4aXHefpuWlOG2xkmDIAq9b";

    /**
     * @var string publishable api key
     */
    public $publishableApiKey = "pk_test_EtHFV4BuPQokJT6jiROls87Y";

    private $apiEndpoint = "https://api.tap.company/v2";


    /**
     * Create a charge for redirect
     */
    public function createCharge($desc = "Pay", $statementDesc = "", $ref, $amount, $firstName, $email, $phone, $redirectUrl, $gateway) {
        $chargeEndpoint = $this->apiEndpoint."/charges";

        $chargeParams = [
          "amount" => $amount,
          "currency" => "KWD",
          "threeDSecure" => true,
          "save_card" => false,
          "description" => $desc,
          "statement_descriptor" => $statementDesc,
          "metadata" => [
            // "udf1" => "test 1",
            // "udf2" => "test 2"
          ],
          "reference" => [
            "transaction" => $ref,
            "order" => $ref
          ],
          "receipt" => [
            "email" => false,
            "sms" => false
          ],
          "customer" => [
            "first_name" => $firstName,
            "email" => $email,
            "phone" => [
              "country_code" => "965",
              "number" => $phone
            ]
          ],
          "source" => [
            "id" => $gateway
          ],
          "redirect" => [
            "url" => $redirectUrl
          ]
        ];

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($chargeEndpoint)
            ->setData($chargeParams)
            ->addHeaders([
                'authorization' => 'Bearer '.$this->secretApiKey,
                'content-type' => 'application/json',
            ])
            ->send();

        return $response;

    }


    /**
     * Check charge object for status updates
     * @param  string $chargeId
     */
    public function retrieveCharge($chargeId)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($this->apiEndpoint."/charges/".$chargeId)
            ->addHeaders([
                'authorization' => 'Bearer '.$this->secretApiKey,
                'content-type' => 'application/json',
            ])
            ->send();

        return $response;
    }


}
