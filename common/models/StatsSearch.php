<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Stats;
use yii\db\Expression;

/**
 * StatsSearch represents the model behind the search form of `common\models\Stats`.
 */
class StatsSearch extends Stats
{
    public $total = false;
    public $username = '';
    public $type = '';
    public $year = '';
    public $month = '';
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'year', 'month', 'type'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Stats::find();
        $query->joinWith('user');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if ($this->total) {
            $query->select([
                new Expression('NULL AS user_id'),
                new Expression('period'),
                new Expression('type'),
                new Expression('SUM(pre_match_count) AS pre_match_count'),
                new Expression('SUM(match_count) AS match_count'),
                new Expression('SUM(mismatch_count) AS mismatch_count'),
                new Expression('SUM(other_count) AS other_count'),
            ]);
            $query->groupBy('period');
        }
        else {
            $query->andFilterWhere(['LIKE', '{{%user}}.username', $this->username]);
        }

        if ($this->type == 'T')
        {
             $this->year = false;
             $this->month = false;
        }
        if (!$this->year || $this->type == 'Y')
        {
            $this->month = false;
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([ 'user_id' => $this->user_id ]);
        if ($this->type)
        {
            $query->andWhere([ '=', 'type', $this->type]);
        }
        if ($this->year)
        {
            $condition = ['OR'];
            // $condition [] = [ 'period' => sprintf('%04d', $this->year) ];
            if ($this->month)
            {
                $condition [] = [ 'period' => sprintf('%04d-%02d', $this->year, $this->month) ];
                $condition [] = [ 'LIKE', 'period', sprintf('%04d-%02d-%%', $this->year, $this->month), false ];
            }
            else {
                $condition [] = [ 'period' => sprintf('%04d', $this->year) ];
                $condition [] = [ 'LIKE', 'period', sprintf('%04d-%%', $this->year), false ];
            }
            $query->andWhere($condition);
        }

        return $dataProvider;
    }
}
