<?php

namespace common\models;

use Yii;
use common\models\User;

/**
 * This is the model class for table "{{%messages}}".
 *
 * @property int $id
 * @property string $text
 */
class Message extends \yii\db\ActiveRecord{
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%messages}}';
    }

  public static function get_all_for_compare_in_table(){
    $res = self::find()
      ->where("settings__table_rows_id <> '-1'")->asArray()->all();

    return $res;
  }

  private static function template($data){
    $s = <<<TAG
            <div class="[ MESSAGE ] right-item__message " style="margin-bottom: 10px">
              {$data['text']}
              <div class="message__info_btn"> !
                <div class="message__img-message">{$data['description']}
                  <div class="message__img-message-arrow"></div>
                </div>
              </div>
            </div>
TAG;
    return $s;
  }

  public static function compare_in_table(int $id,$compare_val,$all){


    foreach ($all as $item){

      if ((int)$item['settings__table_rows_id'] === (int)$id){
        if ($compare_val){

          //$id = $item['settings__table_rows_id'];
          $s = $item['settings__compare_symbol'];
          $field_value = $item['settings__compare_field'];

          if ($s === '==')
            if ( $compare_val  ==  $field_value){
              return self::template($item);
            }

          if ((string)$s === '-1')
            return self::template($item);

          if ($s === '!=')
            if ( $compare_val  !=  $field_value){
              return self::template($item);
            }

          if ($s === '>')
            if ( (float)$compare_val  >  (float)$field_value){
              return self::template($item);
            }

          if ($s === '<')
            if ( (float)$compare_val  <  (float)$field_value){
              return self::template($item);
            }
        }
      }
    }
    return '';

  }

  /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['text','description'], 'required'],
            [['text'], 'string'],
            [['settings__table_rows_id','settings__compare_symbol','settings__compare_field','settings__visible_all','settings__show_additional_fields'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(){
        return [
            'id' => Yii::t('site', 'ID'),
            'text' => Yii::t('site', 'Text'),
        ];
    }
    
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
                    ->viaTable('user_message', ['message_id' => 'id']);
    }
}

