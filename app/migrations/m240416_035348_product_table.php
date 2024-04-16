<?php

use yii\db\Migration;

/**
 * Class m240416_035348_product_table
 */
class m240416_035348_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'price' => $this->decimal(10, 2)->notNull(),
            'client_id' => $this->integer()->notNull(),
            'picture' => $this->text()->notNull(),
        ]);

        $this->createIndex(
            '{{%idx-product-client_id}}',
            '{{%product}}',
            'client_id'
        );

        $this->addForeignKey(
            '{{%fk-product-client_id}}',
            '{{%product}}',
            'client_id',
            '{{%client}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240416_035348_product_table cannot be reverted.\n";

        return false;
    }
    */
}
