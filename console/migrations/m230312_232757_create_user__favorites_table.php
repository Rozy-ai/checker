<?php

use yii\db\Migration;
use common\models\User__favorites;

/**
 * Handles the creation of table `{{%user__favorites}}`.
 */
class m230312_232757_create_user__favorites_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user__favorites}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'source_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'user_type' => "ENUM('" . implode("','", User__favorites::TYPE_USER) . "') DEFAULT '". User__favorites::TYPE_USER_DEFAULT . "'",
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user__favorites}}');
    }
}
