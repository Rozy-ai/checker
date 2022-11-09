<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExternalUser;

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
            [['login', 'email'], 'safe'],
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
        $query = ExternalUser::find();

        // add conditions that should always apply here

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
            'id' => $this->id,
            'FROM_UNIXTIME(admin.createdAt, "%d.%m.%Y")' => $this->created_at,
            'FROM_UNIXTIME(admin.updatedAt, "%d.%m.%Y")' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'login', $this->login])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
