<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\UploadFields;

/**
 * UploadFieldsSearch represents the model behind the search form of `backend\models\UploadFields`.
 */
class UploadFieldsSearch extends UploadFields
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'position', 'default_visible', 'is_select_field'], 'integer'],
            [['name', 'comment', 'price_field', 'product_field', 'row_id'], 'safe'],
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
        $query = UploadFields::find();

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
            'position' => $this->position,
            'default_visible' => $this->default_visible,
            'is_select_field' => $this->is_select_field,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'price_field', $this->price_field])
            ->andFilterWhere(['like', 'product_field', $this->product_field])
            ->andFilterWhere(['like', 'row_id', $this->row_id]);

        return $dataProvider;
    }
}
