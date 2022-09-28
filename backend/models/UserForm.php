<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use common\models\User as User;
/**
 * User form
 */
class UserForm extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    public $model = null;
    public $username;
    public $email;
    public $status;
    public $password;
    public $detail_view_for_items;

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios [self::SCENARIO_CREATE] = ['username', 'email', 'password', 'status','detail_view_for_items'];
        $scenarios [self::SCENARIO_UPDATE] = ['username', 'email', 'password', 'status','detail_view_for_items'];
        
        return $scenarios;
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->model instanceof yii\db\ActiveRecord)
        {
            $this->setAttributes($this->model->getAttributes(['username', 'email', 'status','detail_view_for_items']), false);
        }
        else {
            throw new Exception('$model is not instance of yii\db\ActiveRecord');
        }
    }
    
    public function emailUniqueWhen($form) 
    { 
        return $form->scenario == self::SCENARIO_CREATE or $form->email != $form->model->email;
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          [['detail_view_for_items'],'safe'],

          ['status', 'required'],
            ['status', 'in', 'range' => [User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_DELETED]],
            
            ['username', 'trim', 'on' => self::SCENARIO_CREATE],
            ['username', 'required', 'on' => self::SCENARIO_CREATE],
            ['username', 'unique', 'targetClass' => User::class, 
                         'message' => 'This username has already been taken.', 
                         'on' => self::SCENARIO_CREATE],
            ['username', 'string', 'min' => 2, 'max' => 255, 'on' => self::SCENARIO_CREATE],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 
                      'message' => 'This email address has already been taken.', 
                      'when' => [$this, 'emailUniqueWhen'] ],

            ['password', 'required', 'on' => self::SCENARIO_CREATE],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account or the update existing account was successful
     */
    public function save()
    {
        if (!$this->validate()) {
            return null;
        }
        
        $this->model->setAttributes($this->getAttributes(['username', 'email', 'status','detail_view_for_items']), false);
        
        if ($this->scenario == self::SCENARIO_CREATE or !empty($this->password))
        {
            $this->model->setPassword($this->password);
            $this->model->generateAuthKey();
        }
        
        if ($this->model->save())
        {
            if ($this->scenario == self::SCENARIO_CREATE)
            {
                $auth = \Yii::$app->authManager;
                $userRole = $auth->getRole('user');
                $auth->assign($userRole, $this->model->getId());
            }
            
            return true;
        }
        
        return null;
    }
}
