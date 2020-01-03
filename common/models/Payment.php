<?php

namespace common\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "payment".
 *
 * @property string $uuid
 * @property string $user_phone
 * @property string $product_uuid
 * @property int $quantity_purchased
 * @property string $gateway_order_id
 * @property string $gateway_transaction_id
 * @property string $gateway_mode
 * @property string $current_status
 * @property double $amount_charged
 * @property double $net_amount
 * @property double $gateway_fee
 * @property string $udf1
 * @property string $udf2
 * @property string $udf3
 * @property string $udf4
 * @property string $udf5
 * @property string $payment_link
 * @property string $created_at
 * @property string $updated_at
 * @property int $received_callback
 *
 * @property Product $productUu
 * @property User $userPhone
 */
class Payment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'user_phone', 'product_uuid', 'quantity_purchased', 'amount_charged'], 'required'],
            [['quantity_purchased', 'received_callback'], 'integer'],
            [['current_status', 'payment_link'], 'string'],
            [['amount_charged', 'net_amount', 'gateway_fee'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['uuid'], 'string', 'max' => 36],
            [['user_phone', 'gateway_order_id', 'gateway_transaction_id', 'gateway_mode', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5'], 'string', 'max' => 255],
            [['product_uuid'], 'string', 'max' => 40],
            [['uuid'], 'unique'],
            [['product_uuid'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_uuid' => 'uuid']],
            [['user_phone'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_phone' => 'phone']],
        ];
    }

    /**
     * @return array
     */
    public function behaviors() {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'uuid',
                ],
                'value' => function() {
                    if(!$this->uuid){
                        // Get a unique uuid from payment table
                        $this->uuid = Payment::getUniquePaymentUuid();
                    }

                    return $this->uuid;
                }
            ],
	        [
		        'class' => TimestampBehavior::className(),
		        'createdAtAttribute' => 'created_at',
		        'updatedAtAttribute' => 'updated_at',
		        'value' => new Expression('NOW()'),
	        ]
        ];
    }

    /**
     * Get a unique alphanumeric uuid to be used for a payment
     * This uuid will be used for url shortener to redirect to payment
     * @return string uuid
     */
    private static function getUniquePaymentUuid($length = 6){
        $uuid = \ShortCode\Random::get($length);

        $isNotUnique = static::find()->where(['uuid' => $uuid])->exists();

        // If not unique, try again recursively
        if($isNotUnique){
            return static::getUniquePaymentUuid($length);
        }

        return $uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'Uuid',
            'user_phone' => 'User Phone',
            'product_uuid' => 'Product Uuid',
            'quantity_purchased' => 'Quantity Purchased',
            'gateway_order_id' => 'Gateway Order ID',
            'gateway_transaction_id' => 'Gateway Transaction ID',
            'gateway_mode' => 'Gateway Mode',
            'current_status' => 'Current Status',
            'amount_charged' => 'Amount Charged',
            'net_amount' => 'Net Amount',
            'gateway_fee' => 'Gateway Fee',
            'udf1' => 'Udf1',
            'udf2' => 'Udf2',
            'udf3' => 'Udf3',
            'udf4' => 'Udf4',
            'udf5' => 'Udf5',
            'payment_link' => 'Payment Link',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'received_callback' => 'Received Callback',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['uuid' => 'product_uuid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['phone' => 'user_phone']);
    }
}
