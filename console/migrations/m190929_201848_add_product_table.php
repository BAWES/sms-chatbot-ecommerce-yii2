<?php

use yii\db\Migration;

/**
 * Class m190929_201848_add_product_table
 */
class m190929_201848_add_product_table extends Migration
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
        
        $this->createTable('{{%product}}', [
            'uuid' => $this->char(40),
            'name_en' => $this->string()->notNull(),
            'name_ar' => $this->string()->notNull(),

            'marketing_text' => $this->text(), // Text sent by Bot showcasing this products offer/price


            'status' => $this->smallInteger()->defaultValue(0)->notNull(), // For later use. Enable/disable product sale

            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull()
        ], $tableOptions);
        $this->addPrimaryKey('PK', 'product', 'uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product}}');

        return false;
    }
}
