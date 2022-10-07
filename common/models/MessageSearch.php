<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Message;

/**
 * MessageSearch represents the model behind the search form of `common\models\Message`.
 */
class MessageSearch extends Message
{
    public $user;
    public $user_id;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['text', 'user'], 'safe'],
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
        $query = Message::find();

        $query->joinWith('users');
        
        // add conditions that should always apply here

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            '{{%user_message}}.user_id' => $this->user_id
        ]);

        $query->andFilterWhere(['like', 'text', $this->text]);
        $query->andFilterWhere(['like', '{{%user}}.username', $this->user]);

        $countQuery = clone $query;
        $countQuery->distinct();
        
        $query->with('users');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'totalCount' => $countQuery->count()
        ]);

        return $dataProvider;
    }
}
