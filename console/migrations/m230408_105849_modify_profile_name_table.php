<?php

use yii\db\Migration;
use common\models\ProfileName;

/**
 * Class m230408_105849_modify_profile_name_table
 */
class m230408_105849_modify_profile_name_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * @var ProfileName[]
         */
        $profiles = ProfileName::find()->all();
        foreach ($profiles as $p) {
            $p->name = ucfirst($p->name);
            $p->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        /**
         * @var ProfileName[]
         */
        $profiles = ProfileName::find()->all();
        foreach ($profiles as $p) {
            $p->name = lcfirst($p->name);
            $p->save();
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230408_105849_modify_profile_name_table cannot be reverted.\n";

        return false;
    }
    */
}
