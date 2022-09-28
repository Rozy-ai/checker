<?php
use yii\db\Migration;

class m211124_163257_initial_rbac_data extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;

        // add "admin" role
        $admin = $auth->createRole('admin');
        $auth->add($admin);

        // Assign roles to user.
        $auth->assign($admin, 1);

        // add "user" role
        $user = $auth->createRole('user');
        $auth->add($user);
        $auth->addChild($admin, $user);
    }
    
    public function down()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();
    }
}
