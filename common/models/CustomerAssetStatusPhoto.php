<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customer_asset_status_photo".
 *
 * @property int $id
 * @property int|null $customer_asset_id
 * @property string|null $photo
 * @property int|null $company_id
 * @property int|null $branch_id
 */
class CustomerAssetStatusPhoto extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_asset_status_photo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_asset_id', 'company_id', 'branch_id'], 'integer'],
            [['photo'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_asset_id' => 'Customer Asset ID',
            'photo' => 'Photo',
            'company_id' => 'Company ID',
            'branch_id' => 'Branch ID',
        ];
    }
}
