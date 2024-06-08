<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_route_close".
 *
 * @property int $id
 * @property int|null $route_id
 * @property string|null $trans_date
 * @property int|null $product_id
 * @property float|null $qty
 * @property int|null $company_id
 * @property int|null $branch_id
 */
class OrderRouteClose extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_route_close';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['route_id', 'product_id', 'company_id', 'branch_id'], 'integer'],
            [['trans_date'], 'safe'],
            [['qty'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'route_id' => 'Route ID',
            'trans_date' => 'Trans Date',
            'product_id' => 'Product ID',
            'qty' => 'Qty',
            'company_id' => 'Company ID',
            'branch_id' => 'Branch ID',
        ];
    }
}
