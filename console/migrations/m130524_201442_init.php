<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // Admin table
        $this->createTable('{{%admin}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),

            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);


        // End User table
        $this->createTable('{{%user}}', [
            'phone' => $this->string()->unique()->notNull(), // Primary key

            'name' => $this->string(),
            'email' => $this->string()->unique(),
            'auth_key' => $this->string(32)->notNull(),

            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('PK', 'user', 'phone');

        // SMS Messages
        $this->createTable('{{%sms}}', [
            'uuid' => $this->char(36),
            'user_phone' => $this->string()->unique(), // relation

            'sender' => $this->smallInteger(), // Who sent the text? Us or them?
            'body' => $this->text(),

            'status' => $this->smallInteger(), // Has this been sent already? or in queue?

            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull()
        ], $tableOptions);
        $this->createIndex(
            'idx-sms-user_phone',
            'sms',
            'user_phone'
        );
        $this->addForeignKey(
            'fk-sms-user_phone',
            'sms',
            'user_phone',
            'user',
            'phone'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk-sms-investor_id',
            'sms'
        );
        $this->dropIndex(
            'idx-sms-investor_id',
            'sms'
        );
        $this->dropTable('{{%sms}}');
        $this->dropTable('{{%user}}');
    }
}
