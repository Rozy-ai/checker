<?php

use \yii\db\Migration;

class m190124_110200_add_columns_to_source_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%source}}', 'percent_pro', $this->string()->defaultValue(100));
        $this->addColumn('{{%source}}', 'percent_general', $this->string()->defaultValue(100));
        $this->addColumn('{{%source}}', 'cost_pro', $this->string()->defaultValue(100));
    }

    public function down()
    {
        $this->dropColumn('{{%source}}', 'percent_pro');
        $this->dropColumn('{{%source}}', 'percent_general');
        $this->dropColumn('{{%source}}', 'cost_pro');
    }
}
