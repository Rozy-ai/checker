<?php

use yii\db\Migration;

class m230123_130742_addcolumn_table_external_users extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%external_users}}', 'ex_profile_id', $this->integer());
        $this->createIndex('ex_profile_id', '{{%external_users}}', ['ex_profile_id']);
        $this->addForeignKey(
            'external_users_ibfk_1',
            '{{%external_users}}',
            ['ex_profile_id'],
            '{{%external_user_profile}}',
            ['id'],
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {

    }
}
