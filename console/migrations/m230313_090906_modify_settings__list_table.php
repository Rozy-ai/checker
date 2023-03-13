<?php

use yii\db\Migration;

/**
 * Class m230313_090906_modify_settings__list_table
 */
class m230313_090906_modify_settings__list_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            '{{%settings__list}}',
            [
                'route' => 'settings/fields_extend_price?section=price'
            ],
            [
                'route' => 'settings/fields_extend_price',
            ],
        );
        $this->insert(
            '{{%settings__list}}',
            [
                'title' => 'Поля в окне подсказки (BSR)',
                'route' => 'settings/fields_extend_price?section=bsr',
                'position' => 25,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(
            '{{%settings__list}}',
            [
                'route' => 'settings/fields_extend_price'
            ],
            [
                'route' => 'settings/fields_extend_price?section=price',
            ],
        );
        $this->delete(
            '{{%settings__list}}',
            [
                'route' => 'settings/fields_extend_price?section=bsr',
            ]
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230313_090906_modify_settings__list_table cannot be reverted.\n";

        return false;
    }
    */
}
