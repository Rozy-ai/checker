<?php

use yii\db\Migration;

/**
 * Class m211211_102045_create_view_comparisons_aggregated
 */
class m211211_102045_create_view_comparisons_aggregated extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
            CREATE VIEW {{%comparisons_aggregated}}
            AS SELECT
                    {{%comparisons}}.`product_id` AS `product_id`,
                    COUNT(*) AS `counted`,
                    GROUP_CONCAT(DISTINCT {{%user}}.`username` separator ',') AS `users`,
                    GROUP_CONCAT(DISTINCT {{%user}}.`id` separator ',') AS `uids`,
                    GROUP_CONCAT(DISTINCT {{%comparisons}}.`status` separator ',') AS `statuses`,
                    GROUP_CONCAT({{%comparisons}}.`node` separator ',') AS `nodes`
                FROM ({{%comparisons}} JOIN {{%user}} ON ({{%user}}.`id` = {{%comparisons}}.`user_id`))
                GROUP BY {{%comparisons}}.`product_id`
        SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW {{%comparisons_aggregated}}");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211211_102045_create_view_comparisons_aggregated cannot be reverted.\n";

        return false;
    }
    */
}
