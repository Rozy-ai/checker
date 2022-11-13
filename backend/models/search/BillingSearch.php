<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Billing;

/**
 * BillingSearch represents the model behind the search form of `backend\models\Billing`.
 */
class BillingSearch extends Billing
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id', 'status', 'source', 'admin_id'], 'integer'],
            ['date', 'date', 'format' => 'php:d.m.Y'],
            [['sum'], 'number'],
            [['description'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Billing::find()->alias('billing')
            ->joinWith('user user');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'billing.id' => $this->id,
            'billing.user_id' => $this->user_id,
            'billing.status' => $this->status,
            'billing.sum' => $this->sum,
            'billing.source' => $this->source,
            'FROM_UNIXTIME([[billing]].[[date]], "%d.%m.%Y")' => $this->date,
            'billing.admin_id' => $this->admin_id,
        ]);

        $query->andFilterWhere(['like', 'user.login', $this->description]);
        $query->andFilterWhere(['like', 'billing.description', $this->description]);

        return $dataProvider;
    }
}
