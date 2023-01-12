<?php

use yii\db\Migration;

class m230111_160443_create_foreign_keys extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey(
            'external_user_profile_source_ibfk_2',
            '{{%external_user_profile_source}}',
            ['source_id'],
            '{{%source}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('external_user_profile_source_ibfk_2', '{{%external_user_profile_source}}');
    }
}
