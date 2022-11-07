<?php

namespace backend\models;

use common\models\User__source_access;
use Yii;
use common\models\Comparison;
use common\models\Message;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property-read Message[] $messages
 * @property-read Comparison[] $comparisons
 * @property-read User__source_access[] $user__source_access
 * @property int $status
 */
class User extends \yii\db\ActiveRecord{
  /**
   * {@inheritdoc}
   */
  public static function tableName(): string
  {
    return '{{%user}}';
  }

  /**
   * {@inheritdoc}
   */
  public function rules(): array
  {
    return [
        [['username', 'email'], 'required'],
        [['status'], 'integer'],
        [['username', 'email'], 'string', 'max' => 255],
        [['username'], 'unique'],
        [['email'], 'unique'],
        [['detail_view_for_items'], 'safe'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels(): array
  {
    return [
        'username' => Yii::t('site', 'Username'),
        'email' => Yii::t('site', 'Email'),
        'status' => Yii::t('site', 'Status'),
        ];
  }


  /**
   * Gets query for [[Comparison]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getComparisons(): \yii\db\ActiveQuery
  {
    return $this->hasMany(Comparison::className(), ['id' => 'user_id']);
  }

  /**
   * Gets query for [[Message]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getMessages(): \yii\db\ActiveQuery
  {
    return $this->hasMany(Message::className(), ['id' => 'message_id'])->viaTable('user_message', ['user_id' => 'id']);
  }

  /**
   * Gets query for [[User__source_access]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getUser__source_access(): \yii\db\ActiveQuery
  {
    return $this->hasMany(User__source_access::class, ['user_id' => 'id']);
  }


  public static function isAdmin($uid = false): bool
  {
      $user = Yii::$app->getUser();
    if (!$uid) {
        if ($user->getIsGuest()) {
            return false;
        }
        $uid = $user->id;
    }
    return (bool)\Yii::$app->getAuthManager()->getAssignment('admin', $uid);
  }


  public static function is_detail_view_for_items(){
    $user_id = \Yii::$app->getUser()->id;
    $res = self::findOne(['id' => $user_id]);
    if ($res) {
      return $res->detail_view_for_items ? true : false;
    }
    return false;
  }

  /*
  public function is_source_access($user_id = false,$source_id = false){
    $user_id = $this->id;
    if (!$source_id){
      $source_id = Source::get_source()['source_id'];
    }

    $q = User::find()->where(['id' => $user_id, 'source_id' => $source_id]);
    $q->innerJoin('user__source_access','user__source_access.user_id = user.id');
    $res = $q->limit(1)->one();

    //echo '<pre>'.PHP_EOL;
    print_r($res);
    //echo PHP_EOL;
    exit;

  }
  */

}
