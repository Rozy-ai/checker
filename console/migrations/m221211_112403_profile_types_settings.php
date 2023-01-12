<?php

use common\models\ProfileTypeSetting;
use common\models\Source;
use yii\db\Migration;

/**
 * Class m221211_112403_profile_types_settings
 */
class m221211_112403_profile_types_settings extends Migration
{
    private const TABLE_NAME = '{{%profile_types_settings}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'profile_type' => $this->integer(11)->notNull(),
            'source_id' => $this->integer(11)->notNull(),
            'price1' => $this->money()->notNull(),
            'price2' => $this->money()->notNull(),
            'max_views_count' => $this->integer(11)->notNull(),
            'cancel_show_count' => $this->integer(11)->notNull(),
            'inner_page' => $this->boolean()->defaultValue(false),
        ]);

        $this->addPrimaryKey('pk_profile_types_settings', self::TABLE_NAME, ['profile_type', 'source_id']);
        $this->addForeignKey(
            'fk_profile_type_source',
            self::TABLE_NAME,
            'source_id',
            Source::tableName(),
            'id',
            'CASCADE',
            'CASCADE',
        );

        /** @var Source $source */
        foreach (Source::find()->all() as $source) {
            foreach (ProfileTypeSetting::TYPES as $type) {
                ProfileTypeSetting::initWithDefaultValues($type,  $source->id);
            }
        }

        $this->addColumn('source', 'max_free_show_count', $this->integer()->defaultValue(10));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('source', 'max_free_show_count');
        $this->dropTable(self::TABLE_NAME);
    }
}
