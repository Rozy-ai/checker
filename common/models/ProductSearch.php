<?php

namespace common\models;

use backend\models\Source;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Product;
use common\models\Comparison\Aggregated;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * ProductSearch represents the model behind the search form of `common\models\Product`.
 */
class ProductSearch extends Product
{
    public $status;
    public $user;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['asin', 'status', 'user'], 'safe'],
            // [['title', 'categories', 'asin', 'info', 'comparsion_info', 'results_all_all', 'results_1_1', 'images', 'images_url', 'item_url', 'date_add'], 'safe'],
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
    public function search($params){
      $class = Source::get_source()['source_class'];

        $params = array_merge(['user' => '', 'unprocessed' => false], $params);
        $query = $class::find();
        $query->with('comparisons');
        $query->joinWith(['aggregated']);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'date_add' => $this->date_add,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
              ->andFilterWhere(['like', 'categories', $this->categories])
              ->andFilterWhere(['like', 'asin', $this->asin]);
        if ($this->status) {
            $condition = 'FIND_IN_SET(:value, ' . Aggregated::tableName() . '.statuses) > 0';
            $query->andWhere(new Expression($condition, [':value' => $this->status]));
        }

        if ($params ['unprocessed'])
        {
            if ($params ['user']) {
                $condition = 'FIND_IN_SET(:value, ' . Aggregated::tableName() . '.users) > 0';
                $query->andWhere(['OR',
                        new Expression($condition, [':value' => $params ['user']]),
                        new Expression(Aggregated::tableName() . '.product_id IS NULL')
                    ]);
            }
        }
        else
        {
            if ($this->user) {
                $condition = 'FIND_IN_SET(:value, ' . Aggregated::tableName() . '.users) > 0';
                $query->andWhere(new Expression($condition, [':value' => $this->user]));
            }
        }
        return $dataProvider;
    }
}
