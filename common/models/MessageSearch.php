<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

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
        $query = Message::find()->alias('message');

        $query->joinWith('users user');
        $query->distinct();

        // add conditions that should always apply here

        $this->load($params);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'message.user_id' => $this->user_id
        ]);
        if ($this->text) {
            $query->andWhere([
                'or',
                ['like', 'message.text', $this->text],
                ['like', 'message.description', $this->text],
            ]);
        }
        //        $query->andFilterWhere(['like', 'message.text', $this->text]);
        $query->andFilterWhere(['like', 'user.username', $this->user]);

        return $dataProvider;
    }
}
