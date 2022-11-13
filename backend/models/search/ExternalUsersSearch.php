<?php

namespace backend\models\search;

use backend\models\Billing;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExternalUser;
use yii\db\Expression;

/**
 * ExternalUsersSearch represents the model behind the search form of `backend\models\ExternalUser`.
 */
class ExternalUsersSearch extends ExternalUser
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['created_at', 'updated_at'], 'date', 'format' => 'php:d.m.Y'],
            [['login', 'email', 'balance'], 'safe'],
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
        $balanceQuery = Billing::find()
            ->alias('billing')
            ->select(new Expression('COALESCE(SUM([[billing]].[[sum]]), 0)'))
            ->where(['billing.status' => Billing::STATUS_PAID, 'billing.user_id' => new Expression('[[user]].[[id]]')]);
        $query = ExternalUser::find()->alias('user')
            ->select(['user.*', 'balance' => $balanceQuery]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id',
                    'login',
                    'email',
                    'balance',
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user.id' => $this->id,
            'FROM_UNIXTIME(user.created_at, "%d.%m.%Y")' => $this->created_at,
            'FROM_UNIXTIME(user.updated_at, "%d.%m.%Y")' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'user.login', $this->login])
            ->andFilterWhere(['like', 'user.email', $this->email]);

        return $dataProvider;
    }
}
