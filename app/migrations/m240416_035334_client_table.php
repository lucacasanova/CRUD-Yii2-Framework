<?php

use yii\db\Migration;

/**
 * Class m240416_035334_client_table
 */
class m240416_035334_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%client}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'cpf' => $this->string(11)->notNull()->unique(),
            'postal_code' => $this->string(8)->notNull(),
            'street' => $this->string()->notNull(),
            'number' => $this->string()->notNull(),
            'city' => $this->string()->notNull(),
            'state' => $this->string()->notNull(),
            'additional_information' => $this->string(),
            'picture' => $this->text()->notNull(),
            'gender' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%client}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240416_035334_client_table cannot be reverted.\n";

        return false;
    }
    */
}
