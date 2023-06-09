<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use frontend\components\User as FrontUser;
use frontend\models\Identity as FrontIdentity;
use yii\console\Application;

/**
 * This class only exists here for IDE (PHPStorm/Netbeans/...) autocompletion.
 * This file is never included anywhere.
 * Adjust this file to match classes configured in your application config, to enable IDE autocompletion for custom components.
 * Example: A property phpdoc can be added in `__Application` class as `@property vendor\package\Rollbar|__Rollbar $rollbar` and adding a class in this file
 * ```php
 * // @property of \vendor\package\Rollbar goes here
 * class __Rollbar {
 * }
 * ```
 */
class Yii {
    /**
     * @var \yii\web\Application|Application|__Application
     */
    public static $app;
}

/**
 * @property yii\rbac\DbManager $authManager
 * @property User|FrontUser|__WebUser $user
 * @method User|FrontUser|__WebUser getUser()
 */
class __Application {
}

/**
 * @property Identity|FrontIdentity $identity
 */
class __WebUser {
}
