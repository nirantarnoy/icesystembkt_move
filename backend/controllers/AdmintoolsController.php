<?php

namespace backend\controllers;

use backend\models\Uploadfile;
use common\models\LoginForm;
use Yii;
use backend\models\Member;
use backend\models\MemberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\UploadedFile;

class AdmintoolsController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $model_file = new Uploadfile();
        return $this->render('_index', [
            'model_file' => $model_file
        ]);
    }

    public function actionUpdatelogin()
    {
        $user_id = \Yii::$app->request->post('user_update_log');
        if($user_id){
         //   $max_id = \common\models\LoginLogCal::find()->where(['user_id'=>$user_id])->andFilterWhere(['is', 'logout_date', new \yii\db\Expression('null')])->max('id');
            $max_id = \common\models\LoginLogCal::find()->where(['user_id'=>$user_id])->max('id');
            if($max_id){
                $model = \common\models\LoginLogCal::find()->where(['<','id',$max_id])->orderBy(['id'=>SORT_DESC])->one();
                if($model){
                    $model->logout_date = null;
                    if($model->save(false)){
                        \common\models\LoginLogCal::deleteAll(['id'=>$max_id]);
                    }
                }
            }
        }
        return $this->redirect(['admintools/index']);

    }

    public function actionUpdateissuecancel(){
        $issue_id = \Yii::$app->request->post('issue_id');
        if($issue_id){
           $model = \backend\models\Journalissue::find()->where(['id'=>$issue_id,'status'=>200])->one();
           if($model){
               $model->status = 150; // 150 issue confirm
               $model->save(false);
           }
        }
        return $this->redirect(['admintools/index']);
    }

    public function actionFindlogintime(){
        $login_date = 'ccc';
        $user_id = \Yii::$app->request->post('user_id');
        if($user_id){
           // $max_id = \common\models\LoginLogCal::find()->where(['user_id'=>$user_id])->andFilterWhere(['is', 'logout_date', new \yii\db\Expression('null')])->max('id');
            $max_id = \common\models\LoginLogCal::find()->where(['user_id'=>$user_id])->max('id');
            if($max_id > 0 || $max_id !=null){
                //$login_date = $max_id;
                $model = \common\models\LoginLogCal::find()->where(['<','id',$max_id])->orderBy(['id'=>SORT_DESC])->one();
                if($model){
                  //  $login_date = 'niran';
                    $login_date = date('d-m-Y H:i:s', strtotime($model->login_date));
                }else{

                   // $login_date = date('d-m-Y H:i:s');
                    //$login_date = 'xxx';
                }
            }

        }
        echo $login_date;
    }

    public function actionRollbacksale(){
        $route_id = \Yii::$app->request->post('route_id');
        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }

        $res = 0;
     //   $route_id = 894;
        if($route_id > 0){

            $reprocess_wh = $this->findReprocesswh($company_id, $branch_id);
           // echo $reprocess_wh;return;
            if (\backend\models\Orders::updateAll(['status' => 1], ['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'sale_from_mobile' => 1])) {
                $model = \backend\models\Stocktrans::find()->select(['qty','product_id'])->where(['trans_ref_id' => $route_id, 'date(trans_date)' => date('Y-m-d'), 'activity_type_id' => 7,'company_id'=>$company_id,'branch_id'=>$branch_id])->all();
                if ($model) {
                    foreach ($model as $value) {
                        $model_update = \backend\models\Stocksum::find()->where(['product_id' => $value->product_id, 'warehouse_id' => $reprocess_wh,'company_id'=>$company_id,'branch_id'=>$branch_id])->one();
                        if ($model_update) {
                            $model_update->qty = ($model_update->qty - $value->qty);
                            if ($model_update->save(false)) {
                                $res += 1;
                                 // return qty to order stock

                               // $model_return = \common\models\OrderStock::find()->where(['product_id'=>$value->product_id,'route_id'=>$route_id,'date(trans_date)'=>date('Y-m-d')])->max('id');
                                $model_return = \common\models\OrderStock::find()->select('id')->where(['product_id'=>$value->product_id,'route_id'=>$route_id,'date(trans_date)'=>date('Y-m-d')])->one();
                                if($model_return){
                                    \common\models\OrderStock::updateAll(['avl_qty'=>$value->qty],['id'=>$model_return->id]);
                                }
                            }
                        }
                    }
                    if($res > 0){
                        \backend\models\Stocktrans::deleteAll(['trans_ref_id' => $route_id, 'date(trans_date)' => date('Y-m-d'), 'activity_type_id' => 7,'company_id'=>$company_id,'branch_id'=>$branch_id]);
                    }
                }
            }
            if($res > 0){
                echo "success";
            }else{
                echo "fail";
            }
        }
        return $this->redirect(['admintools/index']);
    }
    public function findReprocesswh($company_id, $branch_id)
    {
        $id = 0;
        if ($company_id && $branch_id) {
            $model = \backend\models\Warehouse::find()->where(['company_id' => $company_id, 'branch_id' => $branch_id, 'is_reprocess' => 1])->one();
            if ($model) {
                $id = $model->id;
            }
        }
        return $id;
    }
}
