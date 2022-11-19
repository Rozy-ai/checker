<?php

namespace common\models;

use backend\models\P_all_compare;
use backend\models\P_updated;
use common\models\Source;
use backend\models\User;
use common\models\HiddenItems;
use Yii;
use yii\behaviors\TimestampBehavior;
use common\behaviors\StatsBehavior;
use yii\db\ActiveRecord as ActiveRecordAlias;

/**
 * Эта таблица для правых  товаров
 *
 * @property int $user_id
 * @property int $product_id
 * @property int $source_id
 * @property int $node
 * @property string $status
 * @property string|null $message
 *
 * @property Product $product
 * @property User $user
 */
class Comparison extends ActiveRecordAlias {

    const STATUS_MISMATCH = 'MISMATCH';
    const STATUS_MATCH = 'MATCH';
    const STATUS_OTHER = 'OTHER';
    const STATUS_PRE_MATCH = 'PRE_MATCH';

    private static $status_data = [
      'PRE_MATCH' => [
          'hex_color' => 'D7C000',
          'name' => 'Prematch',
          'name_2' => 'Да, предварительное',
        ],
      'MATCH' => [
          'hex_color' => '',
          'name' => 'Match',
          'name_2' => 'Да',
        ],
      'OTHER' => [
          'hex_color' => '',
          'name' => 'Other',
          'name_2' => 'Другое',
        ],
      'MISMATCH' => [
          'hex_color' => '',
          'name' => 'Mismatch',
          'name_2' => 'Нет',
        ],
    ];
    
    public static function getFilterStatuses(){
        return [
            'NOCOMPARE' => [
                'hex_color' => '',
                'name' => 'Nocompare',
                'name_2' => 'Не отмеченные',                
            ],
            'PRE_MATCH' => [
                'hex_color' => 'D7C000',
                'name' => 'Prematch',
                'name_2' => 'Да, предварительное',
            ],
            'MATCH' => [
                'hex_color' => '',
                'name' => 'Match',
                'name_2' => 'Да',
            ],
            'OTHER' => [
                'hex_color' => '',
                'name' => 'Other',
                'name_2' => 'Другое',
            ],
            'YES_NO_OTHER' => [
                'hex_color' => '',
                'name' => 'Result',
                'name_2' => 'Все отмеченные',
            ],
            'MISMATCH' => [
                'hex_color' => '',
                'name' => 'Mismatch',
                'name_2' => 'Нет',
            ],
            'ALL' => [
                'hex_color' => '',
                'name' => 'All',
                'name_2' => 'Все',
            ]
        ];
    }
    


    public static function get_filter_statuses(){
      $out = self::$status_data;
      
      $out['NOCOMPARE'] = [
        'hex_color' => '',
        'name' => 'Nocompare',
        'name_2' => 'Не отмеченные',
      ];

      $out = array_merge($out,self::$status_data);

      $out['YES_NO_OTHER'] = [
        'hex_color' => '',
        'name' => 'Result',
        'name_2' => 'Все отмеченные',
      ];

      $out['ALL'] = [
        'hex_color' => '',
        'name' => 'All',
        'name_2' => 'Все',
      ];
      $out['ALL_WITH_NOT_FOUND'] = [
        'hex_color' => '',
        'name' => 'All, with not found',
        'name_2' => 'Все, с ненаденными',
      ];
      return $out;

    }

    public static function get_name_status($status_code) {
    	if ($status_code === 'MISMATCH') return 'Mismatch (No)';
        if ($status_code === 'PRE_MATCH') return 'Pre_match (?)';
        if ($status_code === 'MATCH') return 'Match (Yes)';
    	if ($status_code === 'OTHER') return 'Other';
    }
    
        /**
     * 
     * @return array [
     *      key => status_name
     *      key => status_name
     * ]
     */
    public static function getStatuses() {
        $statuses = [
            self::STATUS_MISMATCH => Yii::t('site', 'MISMATCH'),
            self::STATUS_MATCH => Yii::t('site', 'MATCH'),
            self::STATUS_OTHER => Yii::t('site', 'OTHER'),
            self::STATUS_PRE_MATCH => Yii::t('site', 'PRE_MATCH'),
        ];

        return $statuses;
    }
       
    /**
     * @param type $status_code
     */
    public static function get_status_by_code($status_code) {

        foreach (self::get_filter_statuses() as $k => $item) {
            if ($status_code === $k)
                return $item;
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%comparisons}}';
    }

    public static function get_no_compare_ids_for_item(ActiveRecordAlias $item) {
        $compared = $item->comparisons;
        $addInfo = $item->getAddInfo();
        $list_1 = [];
        $list_2 = [];
        $out = [];

        if ($compared)
            foreach ($compared as $c) {
                $list_1[] = $c->node;
            }

        $list_2 = array_keys($addInfo);

        return array_diff($list_2, $list_1);
    }

    public static function can_compare($p_id, $source_id) {

        return $res = Comparison::find()
                ->select('*')
                ->leftJoin('messages', 'messages.id = comparisons.messages_id')
                ->where(['comparisons.product_id' => $p_id])
                ->andWhere(['comparisons.source_id' => $source_id])
                ->andWhere(['messages.settings__visible_all' => '1'])
                ->asArray()
                //->createCommand()->getRawSql();
                ->limit(1)
                ->one();
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
//        $class = Source::get_source()['source_class'];

        return [
            [['user_id', 'product_id', 'status'], 'required'],
            [['user_id', 'product_id', 'source_id'], 'integer'],
            [['messages_id'], 'safe'],
            [['status', 'message'], 'string'],
//            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => $class::className(), 'targetAttribute' => ['product_id' => 'id']],
//            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
            StatsBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'user_id' => Yii::t('site', 'User ID'),
            'product_id' => Yii::t('site', 'Product ID'),
            'status' => Yii::t('site', 'Status'),
            'message' => Yii::t('site', 'Message'),
        ];
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct() {
        $get_source = Source::get_source()['source_class'];
        $class = $get_source['source_class'];
        $source_id = $get_source['source_id'];

        return $this->hasOne($class::className(), ['id' => 'product_id'])->where(['source_id' => $source_id]);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    /**
     * Делает в таблице Comparisons папись о сравнении со статусом $status
     * @param string $status 
     * @param int $id_source
     * @param int $id_product
     * @param int $id_item
     * @param type $message
     */
    public static function setStatus(string $status, int $id_source, int $id_product, int $id_item, $message = '' ){
        $comparison = self::findOne([
            'product_id'    => $id_product, 
            'node'          => $id_item, 
            'source_id'     => $id_source]);
        if ($comparison){
            if ($comparison->status != $status){
                $comparison->status = $status;
                $comparison->save();
            }
        } else {
            $comparison = new self([
                'source_id' => $id_source,
                'product_id' => $id_product,
                'node' => $id_item,
                'user_id' => \Yii::$app->user->id,
                'status' => $status,
                'message' => $message,
            ]);
            $comparison->save();
        }
    }
/*
    public function setStatus($status, $msgid = null, $url = null, $pid = false) {
        $this->status = $status;
        $this->message = null;
        $this->url = '';
        if (isset($url)) {
            $this->url = $url;
        }

        $this->user_id = Yii::$app->user->id;

        if ($status == self::STATUS_MATCH) {
            
        }
        if ($status == Comparison::STATUS_OTHER) {
            $message = Message::findOne($msgid);
            if ($message !== null) {
                $this->message = $message->text;
                $this->messages_id = $message->id;

                if ($pid && User::isAdmin()) {
                    $puv = P_user_visible::findOne(['p_id' => $pid]);
                    if (!$puv) {
                        $puv = new P_user_visible();
                        $puv->p_id = $pid;
                        $puv->save();
                    }
                }
            }
        } else {
            $this->messages_id = -1;
        }

        return $this;
    }
*/   
    /**
    public function afterSave($insert, $changedAttributes) {

        $class = Source::get_source()['source_class'];
        $res = 'SHOW';
        $p_id = $this->product_id;
//    //echo '<pre>'.PHP_EOL;
//    print_r($insert);
//    print_r($changedAttributes);

        $p = $class::find()->where('id = ' . $p_id)->limit(1)->one();
        $count = count($p->getAddInfo());
        $addInfo = $p->getAddInfo();

        $source_id = Source::get_source()['source_id'];
        $res_comparison = Comparison::find()->where('product_id = ' . $p_id . ' AND status = "MATCH" AND source_id = ' . $source_id)->all();

        $res_all_comparison = Comparison::find()
                ->where('product_id = ' . $p_id . ' AND source_id = ' . $source_id . ' AND status = "MISMATCH"')
                ->count();

        $res_all_comparison_2 = Comparison::find()
                ->where('product_id = ' . $p_id . ' AND source_id = ' . $source_id . ' ')
                ->count();

        if ($res_comparison)
            $res = 'SHOW';
        else {
            if ((int) $count === (int) $res_all_comparison)
                $res = 'HIDE';
        }

        $find = HiddenItems::find()->where(['p_id' => $p_id, 'source_id' => $source_id])->one();

        if ($res === 'SHOW') {
            if ($find) {
                HiddenItems::findOne(['p_id' => $p_id, 'source_id' => $source_id])->delete();
            }
        } else {
            if (!$find) {
                $h = new HiddenItems();
                $h->p_id = $p_id;
                $h->source_id = $source_id;
                $h->status = HiddenItems::STATUS_NOT_FOUND;
                $h->user_id = Yii::$app->user->id;
                $h->insert();
            } else
                HiddenItems::findOne(['p_id' => $p_id, 'source_id' => $source_id])->save();
        }
        P_updated::date_updated($p_id, $source_id);

        $all_compared = false;
        if ((int) $count === (int) $res_all_comparison_2)
            $all_compared = true;

        $find_2 = P_all_compare::find()->where(['p_id' => $p_id, 'source_id' => $source_id])->one();

        // все товары сопоставлены
        if ($all_compared) {
            // запись есть?
            if (!$find_2) { // нет - добавляем
                $c = new P_all_compare();
                $c->p_id = $p_id;
                $c->source_id = $source_id;
                $c->insert();
            }
        } else { // все товары НЕ сопоставлены
            // запись есть? → удаляем
            if ($find_2)
                P_all_compare::findOne(['p_id' => $p_id, 'source_id' => $source_id])->delete();
        }

        parent::afterSave($insert, $changedAttributes);
    }
    
 
    */
        
    public function getMessages() {
        return $this->hasOne(Message::class, ['id' => 'messages_id']);
    }

    public function compare_table_fields($leftValue) {
        $this->messages->settings__table_rows_id;
        $s = $this->messages->settings__compare_symbol;
        $field_value = $this->messages->settings__compare_field;

        if ($s === '==')
            if ($leftValue == $field_value) {
                return true;
            }

        if ($s === '-1')
            return true;

        if ($s === '!=')
            if ($leftValue != $field_value) {
                return true;
            }

        if ($s === '>')
            if ((float) $leftValue > (float) $field_value) {
                return true;
            }

        if ($s === '<')
            if ((float) $leftValue < (float) $field_value) {
                return true;
            }

        return false;
    }

    public static function addOrUpdate(){
        
    }
}
