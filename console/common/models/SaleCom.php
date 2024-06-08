<?php

namespace common\models;

use Yii;

class SaleCom extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sale_com';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'],'required'],
            [['code'],'unique'],
            [['emp_qty', 'status', 'created_at', 'updated_at', 'created_by','company_id','branch_id'], 'integer'],
            [['com_extra'], 'number'],
            [['code', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'รหัส',
            'name' => 'ชื่อ',
            'emp_qty' => 'จำนวนพนักงาน',
            'com_extra' => 'ค่าพิเศษ',
            'status' => 'สถานะ',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
        ];
    }
}
