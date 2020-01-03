<?php

use yii\db\Migration;

/**
 * Class m191211_131209_add_payment_table
 */
class m191211_131209_add_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        Yii::$app->db->createCommand('SET foreign_key_checks = 0')->execute();

        // Create table that will store payment records
        $this->createTable('payment', [
            "uuid" => $this->char(40)->notNull(), // primaryKey

            "user_phone" => $this->string()->notNull(), // Which user made the payment?
            "product_uuid" => $this->char(40)->notNull(), // Which product is being purchased?

            "quantity_purchased" => $this->integer()->notNull(), // Quantity of product being purchased

            "gateway_order_id" => $this->string(), // myfatoorah order id
            "gateway_transaction_id" => $this->string(), // myfatoorah transaction id
            "gateway_mode" => $this->string(), // which gateway they used
            "current_status" => $this->text(), // Where are we with this payment / result

            // payment amounts
            "amount_charged" => $this->double(10,3)->notNull(), // amount charged to customer
            "net_amount" => $this->double(10,3), // net amount deposited into our account
            "gateway_fee" => $this->double(10,3), // gateway fee charged

            // User defined fields
            "udf1" => $this->string(),
            "udf2" => $this->string(),
            "udf3" => $this->string(),
            "udf4" => $this->string(),
            "udf5" => $this->string(),

            // Generated Payment Link
            "payment_link" => $this->string(),

            //datetime
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),

            'received_callback' => $this->boolean()->notNull()->defaultValue(0) // Callback from payment gateway received?

        ], $tableOptions);
        $this->addPrimaryKey('PK', 'payment', 'uuid');

        // creates index for column `product_id`in table `payment`
        $this->createIndex(
            'idx-payment-product_uuid',
            'payment',
            'product_uuid'
        );

        // creates index for column `gateway_order_id`in table `payment`
        $this->createIndex(
            'idx-payment-gateway_order_id',
            'payment',
            'gateway_order_id'
        );
        // creates index for column `gateway_transaction_id`in table `payment`
        $this->createIndex(
            'idx-payment-gateway_transaction_id',
            'payment',
            'gateway_transaction_id'
        );

        // creates index for column `user_phone`in table `payment`
        $this->createIndex(
            'idx-payment-user_phone',
            'payment',
            'user_phone'
        );

        // add foreign key for `user_phone` in table `payment`
        $this->addForeignKey(
            'fk-payment-user_phone',
            'payment',
            'user_phone',
            'user',
            'phone',
            'CASCADE'
        );

        // add foreign key for `product_uuid` in table `payment`
        $this->addForeignKey(
            'fk-payment-product_uuid',
            'payment',
            'product_uuid',
            'product',
            'uuid',
            'CASCADE'
        );

        Yii::$app->db->createCommand('SET foreign_key_checks = 1')->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191211_131209_add_payment_table cannot be reverted.\n";

        return false;
    }

}
