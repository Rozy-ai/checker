<?php

namespace common\behaviors;

use yii\db\ActiveRecord;

/**
 * Description of StatsBehavior
 *
 * @author demiurg
 */
class StatsBehavior extends \yii\base\Behavior {
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

  /*
ALTER TABLE `stats`	ADD COLUMN `pre_match_count` INT(10) NOT NULL DEFAULT '0' AFTER `other_count`;

   * */

    protected function upsert($user_id, $period, $type, $status, $count) {
        $status_field = strtolower($status) . "_count";
        $sql = "INSERT INTO {{%stats}} (user_id, period, type, {$status_field}) "
             . "VALUES (:user_id, :period, :type, :count) "
             . "ON DUPLICATE KEY UPDATE {$status_field}={$status_field}+(:count)";
        \Yii::$app->db->createCommand($sql)
                      ->bindValues([
                          'user_id' => $user_id, 'period' => $period,
                          'status' => $status, 'type' => $type, 'count' => $count
                              ])
                      ->execute();
    }

    protected function store_stats($user_id, $timestamp, $status, $count) {
        $transaction = \Yii::$app->db->beginTransaction();
        // TOTAL
        $this->upsert($user_id, 'TOTAL', 'T', $status, $count);
        // YEAR
        $this->upsert($user_id, strftime('%Y', $timestamp), 'Y', $status, $count);
        // MONTH
        $this->upsert($user_id, strftime('%Y-%m', $timestamp), 'M', $status, $count);
        // DAY
        $this->upsert($user_id, strftime('%Y-%m-%d', $timestamp), 'D', $status, $count);

        $transaction->commit();
    }

    public function afterInsert($event) {
        $values = $this->owner->attributes;
        $this->store_stats($values['user_id'], $values['created_at'], $values['status'], 1);
    }

    public function beforeUpdate($event) {
        $values = $this->owner->oldAttributes;
        $this->store_stats($values['user_id'], $values['updated_at'], $values['status'], -1);
    }

    public function afterUpdate($event) {
        $values = $this->owner->attributes;
        $this->store_stats($values['user_id'], $values['updated_at'], $values['status'], 1);
    }
}
