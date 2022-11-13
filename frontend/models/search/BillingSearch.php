<?php

declare(strict_types=1);

namespace frontend\models\search;

use yii\base\Model;
use frontend\models\Billing;
use yii\data\ActiveDataProvider;

class BillingSearch extends Model
{
    public ?string $date = null;

    public ?string $sum = null;

    public ?string $id = null;

    public ?string $description = null;

    public function rules(): array
    {
        return [
            ['date', 'date', 'format' => 'php:d.m.Y'],
            [['sum', 'id'], 'number'],
            ['description', 'safe'],
        ];
    }

    public function search(int $userId, array $params = []): ActiveDataProvider
    {
        $query = Billing::find()->where(['user_id' => $userId, 'status' => Billing::STATUS_PAID]);

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
            'FROM_UNIXTIME(date, "%d.%m.%Y")' => $this->date,
            'ABS([[sum]])' => $this->sum,
        ]);
        $query->andFilterWhere(['like', 'description', $this->description]);
        return $dataProvider;
    }
}
