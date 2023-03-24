<?php

use yii\db\Migration;

/**
 * Class m230324_141250_create_index_fulltext
 */
class m230324_141250_create_index_fulltext extends Migration
{
     
    public function createIndex($name, $table, $columns, $type = '')
    {
        $time = $this->beginCommand('create ' . ( $type ) . " index $name on $table (" . implode(',', (array) $columns) . ')');
        $this->db->createCommand("ALTER TABLE $table ADD $type INDEX $name (" . implode(',', (array) $columns) . ')')->execute();
        $this->endCommand($time);
    }
        
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {               
        if ($this->db->schema->getTableSchema('parser_trademarkia_com', true)) 
            $this->createIndex('parser_trademarkia_com_profile_fulltext', 'parser_trademarkia_com', 'profile', 'fulltext');
        if ($this->db->schema->getTableSchema('parser_google', true)) 
            $this->createIndex('parser_google_profile_fulltext', 'parser_google', 'profile', 'fulltext');
        if ($this->db->schema->getTableSchema('parser_china', true)) 
            $this->createIndex('parser_china_fulltext', 'parser_china', 'profile', 'fulltext');
        if ($this->db->schema->getTableSchema('parser_alibaba', true)) 
            $this->createIndex('parser_alibaba_fulltext', 'parser_alibaba', 'profile', 'fulltext');
        if ($this->db->schema->getTableSchema('parser_shopping', true)) 
            $this->createIndex('parser_shopping_fulltext', 'parser_shopping', 'profile', 'fulltext');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {   
        if ($this->db->schema->getTableSchema('parser_trademarkia_com', true)) 
            $this->dropIndex('parser_trademarkia_com_profile_fulltext', 'parser_trademarkia_com');
        if ($this->db->schema->getTableSchema('parser_google', true)) 
            $this->dropIndex('parser_google_profile_fulltext', 'parser_google');
        if ($this->db->schema->getTableSchema('parser_china', true)) 
            $this->dropIndex('parser_china_profile_fulltext', 'parser_china');
        if ($this->db->schema->getTableSchema('parser_alibaba', true)) 
            $this->dropIndex('parser_alibaba_profile_fulltext', 'parser_alibaba');
        if ($this->db->schema->getTableSchema('parser_shopping', true)) 
            $this->dropIndex('parser_shopping_profile_fulltext', 'parser_shopping');        
    }

}
