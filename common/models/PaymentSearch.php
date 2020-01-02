<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Payment;

/**
 * PaymentSearch represents the model behind the search form of `common\models\Payment`.
 */
class PaymentSearch extends Payment
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'user_phone', 'product_uuid', 'gateway_order_id', 'gateway_transaction_id', 'gateway_mode', 'current_status', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5', 'created_at', 'updated_at'], 'safe'],
            [['quantity_purchased', 'received_callback'], 'integer'],
            [['amount_charged', 'net_amount', 'gateway_fee'], 'number'],
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
        $query = Payment::find();

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
            'quantity_purchased' => $this->quantity_purchased,
            'amount_charged' => $this->amount_charged,
            'net_amount' => $this->net_amount,
            'gateway_fee' => $this->gateway_fee,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'received_callback' => $this->received_callback,
        ]);

        $query->andFilterWhere(['like', 'uuid', $this->uuid])
            ->andFilterWhere(['like', 'user_phone', $this->user_phone])
            ->andFilterWhere(['like', 'product_uuid', $this->product_uuid])
            ->andFilterWhere(['like', 'gateway_order_id', $this->gateway_order_id])
            ->andFilterWhere(['like', 'gateway_transaction_id', $this->gateway_transaction_id])
            ->andFilterWhere(['like', 'gateway_mode', $this->gateway_mode])
            ->andFilterWhere(['like', 'current_status', $this->current_status])
            ->andFilterWhere(['like', 'udf1', $this->udf1])
            ->andFilterWhere(['like', 'udf2', $this->udf2])
            ->andFilterWhere(['like', 'udf3', $this->udf3])
            ->andFilterWhere(['like', 'udf4', $this->udf4])
            ->andFilterWhere(['like', 'udf5', $this->udf5]);

        return $dataProvider;
    }
}
