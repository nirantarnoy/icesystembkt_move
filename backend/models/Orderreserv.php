<?php

namespace backend\models;

use common\models\OrderReserv;
use Yii;
use yii\db\ActiveRecord;
use yii\web\Session;

date_default_timezone_set('Asia/Bangkok');

class Orderreserv extends \common\models\OrderReserv
{
    public function behaviors()
    {
        return [
            'timestampcdate' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                ],
                'value' => time(),
            ],
//            'timestampudate' => [
//                'class' => \yii\behaviors\AttributeBehavior::className(),
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_INSERT => 'updated_at',
//                ],
//                'value' => time(),
//            ],
            'timestampcby' => [
                'class' => \yii\behaviors\AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_by',
                ],
                'value' => Yii::$app->user->id,
            ],
//            'timestamuby' => [
//                'class' => \yii\behaviors\AttributeBehavior::className(),
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
//                ],
//                'value' => Yii::$app->user->id,
//            ],
//            'timestampcompany'=>[
//                'class'=> \yii\behaviors\AttributeBehavior::className(),
//                'attributes'=>[
//                    ActiveRecord::EVENT_BEFORE_INSERT=>'company_id',
//                ],
//                'value'=> isset($_SESSION['user_company_id'])? $_SESSION['user_company_id']:1,
//            ],
//            'timestampbranch'=>[
//                'class'=> \yii\behaviors\AttributeBehavior::className(),
//                'attributes'=>[
//                    ActiveRecord::EVENT_BEFORE_INSERT=>'branch_id',
//                ],
//                'value'=> isset($_SESSION['user_branch_id'])? $_SESSION['user_branch_id']:1,
//            ],
//            'timestampupdate' => [
//                'class' => \yii\behaviors\AttributeBehavior::className(),
//                'attributes' => [
//                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
//                ],
//                'value' => time(),
//            ],
        ];
    }

    public static function findCode($id)
    {
        $model = OrderReserv::find()->where(['id' => $id])->one();
        return $model != null ? $model->journal_no : '';
    }


    public static function getLastNo($company_id, $branch_id)
    {
        //   $model = Orders::find()->MAX('order_no');
        $model = OrderReserv::find()->where(['company_id' => $company_id, 'branch_id' => $branch_id])->MAX('journal_no');

        $pre = "RS";

            if ($model != null) {
//            $prefix = $pre.substr(date("Y"),2,2);
//            $cnum = substr((string)$model,4,strlen($model));
//            $len = strlen($cnum);
//            $clen = strlen($cnum + 1);
//            $loop = $len - $clen;
                $prefix = $pre ;// substr(date("Y"), 2, 2);
                $cnum = substr((string)$model, 2, strlen($model));
                $len = strlen($cnum);
                $clen = strlen($cnum + 1);
             //   $loop = $len - $clen;
                for ($i = 1; $i <= 4; $i++) {
                    $prefix .= "0";
                }
                $prefix .= $cnum + 1;
                return $prefix;
            } else {
                $prefix = $pre ;// substr(date("Y"), 2, 2);
                return $prefix . '0001';
            }

    }

}
