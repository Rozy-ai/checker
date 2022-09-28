<?php

use yii\db\Migration;

/**
 * Class m211126_202838_create_permissions
 */
class m211126_202838_create_permissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $comparisonProducts = $auth->createPermission('compare-products');
        $comparisonProducts->description = 'Can compare products';
        $auth->add($comparisonProducts);
        
        $ruleComparator = new \common\rbac\ComparatorRule;
        $auth->add($ruleComparator);
        
        $comparisonPreviousAndUntestedProducts = $auth->createPermission('compare-previous-and-untested-products');
        $comparisonPreviousAndUntestedProducts->description = 'Can compare only untested and already tested products';
        $comparisonPreviousAndUntestedProducts->ruleName = $ruleComparator->name;
        $auth->add($comparisonPreviousAndUntestedProducts);
        
        $roleAdmin = $auth->getRole('admin');
        $roleUser  = $auth->getRole('user');
        $auth->addChild($roleAdmin, $comparisonProducts);
        $auth->addChild($roleUser, $comparisonPreviousAndUntestedProducts);
        $auth->addChild($comparisonPreviousAndUntestedProducts, $comparisonProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211126_202838_create_permissions cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211126_202838_create_permissions cannot be reverted.\n";

        return false;
    }
    */
}
