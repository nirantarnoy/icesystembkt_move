<?php

namespace frontend\modules\api\controllers;

use backend\models\Journalissue;
use yii\filters\VerbFilter;
use yii\web\Controller;


class JournalissueController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'list' => ['POST'],
                    'list2' => ['POST'],
                    'checkopen' => ['POST'],
                    'issueconfirm' => ['POST'],
                    'issueqrscan' => ['POST'],
                    'issueqrscan2' => ['POST'],
                    'issueqrscanupdate' => ['POST'],
                    'issueconfirm2' => ['POST'],
                    'issueconfirm2vp18' => ['POST'],
                    'issuetempcreate' => ['POST'],
                    'issuetempconfirm' => ['POST'],
                    'issuetempconfirmtest' => ['POST'],
                    'issueforreprocess' => ['POST'],
                    'repackselect' => ['POST'],
                    'oldstockroute' => ['POST'],
                    'issueqrscanaddtemp' => ['POST'],
                    'issueconfirmcancel' => ['POST'],
                ],
            ],
        ];
    }

    public function actionList()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        // $car_id = $req_data['car_id'];
        $issue_date = $req_data['issue_date'];

        $data = [];
        $status = false;
        if ($route_id) {
            $trans_date = date('Y/m/d');
            $t_date = null;
            $exp_order_date = explode(' ', $issue_date);
            if ($exp_order_date != null) {
                if (count($exp_order_date) > 1) {
                    $x_date = explode('-', $exp_order_date[0]);
                    if (count($x_date) > 1) {
                        $t_date = $x_date[0] . "/" . $x_date[1] . "/" . $x_date[2];
                    }
                }
            }
            if ($t_date != null) {
                $trans_date = $t_date;
            }
            // $model = \common\models\JournalIssue::find()->one();
            $model = \common\models\JournalIssue::find()->where(['delivery_route_id' => $route_id, 'date(trans_date)' => $trans_date])->andFilterWhere(['<=', 'status', 2])->one();
            if ($model) {
                $model_line = \common\models\JournalIssueLine::find()->where(['issue_id' => $model->id])->all();
                if ($model_line) {
                    $status = true;
                    foreach ($model_line as $value) {
                        if ($value->qty == null || $value->qty <= 0) continue;
                        $product_image = \backend\models\Product::findPhoto($value->product_id);
                        array_push($data, [
                            'id' => $value->id,
                            'issue_id' => $value->issue_id,
                            'issue_no' => \backend\models\Journalissue::findNum($value->issue_id),
                            'product_id' => $value->product_id,
                            'product_name' => \backend\models\Product::findName($value->product_id),
                            'image' => 'http://119.59.100.74/icesystem/backend/web/uploads/images/products/' . $product_image,
                            'issue_qty' => $value->qty,
                            'avl_qty' => $value->avl_qty,
                            'price' => 0,
                            'product_image' => '',
                            'status' => $model->status
                        ]);
                    }
                }
            }
        }

        return ['status' => $status, 'data' => $data];
    }

    public function actionList2()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        // $car_id = $req_data['car_id'];
        $issue_date = $req_data['issue_date'];

        $data = [];
        $status = false;
        $model = null;
        if ($route_id) {
            $trans_date = date('Y/m/d');
            $t_date = null;
            $exp_order_date = explode(' ', $issue_date);
            if ($exp_order_date != null) {
                if (count($exp_order_date) > 1) {
                    $x_date = explode('-', $exp_order_date[0]);
                    if (count($x_date) > 1) {
                        $t_date = $x_date[0] . "/" . $x_date[1] . "/" . $x_date[2];
                    }
                }
            }
            if ($t_date != null) {
                $trans_date = $t_date;
            }

            $check_route_type = \backend\models\Deliveryroute::find()->where(['id' => $route_id])->one();

            // $model = \common\models\JournalIssue::find()->one();
            if ($check_route_type->type_id == 2) {
//                $model = \common\models\OrderStock::find()->select(['id', 'issue_id', 'product_id', 'SUM(qty) as qty', 'SUM(avl_qty) as avl_qty'])->where(['route_id' => $route_id, 'date(trans_date)' => $trans_date])->groupBy(['product_id'])->all();
//                if(!$model){
//                    $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
//                    $model = \common\models\OrderStock::find()->select(['id', 'issue_id', 'product_id', 'SUM(qty) as qty', 'SUM(avl_qty) as avl_qty'])->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date])->groupBy(['product_id'])->all();
//                }
                $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -2 day"));
                $model = \common\models\OrderStock::find()->select(['id', 'issue_id', 'product_id', 'SUM(qty) as qty', 'SUM(avl_qty) as avl_qty'])->where(['route_id' => $route_id])->andFilterWhere(['BETWEEN', 'date(trans_date)', $pre_date, $trans_date])->groupBy(['product_id'])->all();

            } else {
                $model = \common\models\OrderStock::find()->select(['id', 'issue_id', 'product_id', 'SUM(qty) as qty', 'SUM(avl_qty) as avl_qty'])->where(['route_id' => $route_id, 'date(trans_date)' => $trans_date])->groupBy(['product_id'])->all();
            }

            if ($model) {
                $status = true;
                foreach ($model as $value) {
                    if ($value->qty == null || $value->qty <= 0) continue;
                    $product_image = \backend\models\Product::findPhoto($value->product_id);
                    array_push($data, [
                        'id' => $value->id,
                        'issue_id' => $value->issue_id,
                        'issue_no' => \backend\models\Journalissue::findNum($value->issue_id),
                        'product_id' => $value->product_id,
                        'product_name' => \backend\models\Product::findName($value->product_id),
                        'image' => 'http://103.253.73.108/icesystem/backend/web/uploads/images/products/' . $product_image,
                        'issue_qty' => $value->qty,
                        'avl_qty' => $value->avl_qty,
                        'price' => 0,
                        'product_image' => '',
                        'status' => 2,
                        //  'issue_count' => $this->getissuecount($route_id,$trans_date),
                    ]);
                }
            }
        }

        return ['status' => $status, 'data' => $data];
    }

    public function getissuecount($route_id, $trans_date)
    {
        $count = 0;
        $model = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => $trans_date])->groupBy(['issue_id'])->count('issue_id');
        if ($model) {
            $count = $model;
        }
        return $count;
    }

    public function actionIssueconfirm()
    {
        $issue_id = null;
        $user_id = null;
        $route_id = null;
        $company_id = 1;
        $branch_id = 1;
        $status = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        //$route_id = $req_data['route_id'];
        $issue_id = $req_data['issue_id'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $default_wh = 0;
//        if ($company_id == 1 && $branch_id == 2) {
//            $default_wh = 5;
//        }

        $default_wh = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id);

        $data = [];
        if ($issue_id != null && $user_id != null) {
            //$data = ['issue_id'=> $issue_id,'user_id'=>$user_id];
            $model_update_issue_status = \common\models\JournalIssue::find()->where(['id' => $issue_id])->one();
            $model_issue_line = \backend\models\Journalissueline::find()->where(['issue_id' => $issue_id])->all();
            foreach ($model_issue_line as $val2) {
                if ($val2->qty <= 0 || $val2->qty == null) continue;

                $model_order_stock = new \common\models\OrderStock();
                $model_order_stock->issue_id = $issue_id;
                $model_order_stock->product_id = $val2->product_id;
                $model_order_stock->qty = $val2->qty;
                $model_order_stock->used_qty = 0;
                $model_order_stock->avl_qty = $val2->qty;
                $model_order_stock->order_id = 0;
                $model_order_stock->route_id = $model_update_issue_status->delivery_route_id;
                $model_order_stock->trans_date = date('Y-m-d');
                $model_order_stock->company_id = $company_id;
                $model_order_stock->branch_id = $branch_id;
                if ($model_order_stock->save(false)) {

                    if ($model_update_issue_status) {
                        if ($model_update_issue_status->status != 2) {
                            $model_update_issue_status->status = 2;
                            if ($model_update_issue_status->save(false)) {

                                $status = 1;
                            }
                        }

                    }
                    $this->updateStock($val2->product_id, $val2->qty, $default_wh, '', $company_id, $branch_id, $user_id, $model_update_issue_status->delivery_route_id);
                }
            }

            if ($status == 1) {
                $model_update_order = \backend\models\Orders::find()->where(['delivery_route_id' => $route_id, 'date(order_date)' => strtotime(date('Y-m-d')), 'status' => 1])->one();
                if ($model_update_order) {
                    $model_update_order->status = 99;
                    $model_update_order->save();
                }
            }
//            $model = \backend\models\Journalissue::find()->where(['id' => $issue_id])->one();
//            if ($model) {
//                $model->status = 2; //close
//                $model->user_confirm = $user_id;
//                if ($model->save()) {
//                    $status = 1;
//                }
//            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function checkhasOrderStock($route_id, $issue_id)
    {
        $res = 0;
        if ($route_id) {
            $res = \common\models\OrderStock::find()->where(['issue_id' => $issue_id, 'route_id' => $route_id])->count();
        }
        return $res;
    }

    public function actionIssueconfirm2Origin()
    {
        $issue_id = null;
        $user_id = null;
        $route_id = null;
        $company_id = 0;
        $branch_id = 0;
        $status = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        $issue_id = $req_data['issue_id'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $default_wh = 0; // 1 6
//        if ($company_id == 1 && $branch_id == 2) {
//            $default_wh = 5;
//        }
        $default_wh = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id);

        $data = [];
        if ($issue_id != null && $user_id != null) {
            //$data = ['issue_id'=> $issue_id,'user_id'=>$user_id];
            $has_order_stock = $this->checkhasOrderStock($route_id, $issue_id);
            if ($has_order_stock == 0) {
                $check_route_type = \backend\models\Deliveryroute::find()->where(['id' => $route_id])->one();
                $model_update_issue_status = \common\models\JournalIssue::find()->where(['id' => $issue_id])->one();
                $model_issue_line = \backend\models\Journalissueline::find()->where(['issue_id' => $issue_id])->all();
                foreach ($model_issue_line as $val2) {
                    if ($val2->qty <= 0 || $val2->qty == null) continue;

                    $old_stock = 0;
                       $old_stock = $this->getProductOldstock($val2->product_id, $model_update_issue_status->delivery_route_id, $company_id, $branch_id);

                    if ($check_route_type->type_id == 2) { // is boots car
                        $check_has_order_stock1 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->count(); //
                        if ($check_has_order_stock1 > 0) {
                            $old_stock = $this->getProductOrderstock($val2->product_id, $model_update_issue_status->delivery_route_id, $company_id, $branch_id);
                        } else {
                            $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -2 day"));
                            $check_has_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date])->count(); //
                            if ($check_has_order_stock2 > 0) {
                                $old_stock = $this->getProductOrderstock2($val2->product_id, $model_update_issue_status->delivery_route_id, $company_id, $branch_id);
                            }
//                            else {
//                                $old_stock = $this->getProductOldstock($val2->product_id, $model_update_issue_status->delivery_route_id, $company_id, $branch_id);
//                            }
                        }
                    }

                    $model_order_stock = new \common\models\OrderStock();
                    $model_order_stock->issue_id = $issue_id;
                    $model_order_stock->product_id = $val2->product_id;
                    //  $model_order_stock->qty = ($val2->qty + $old_stock);
                    $model_order_stock->qty = $val2->qty;
                    $model_order_stock->used_qty = 0;
                    // $model_order_stock->avl_qty = ($val2->qty + $old_stock);
                    $model_order_stock->avl_qty = $val2->qty;
                    $model_order_stock->order_id = 0;
                    $model_order_stock->route_id = $model_update_issue_status->delivery_route_id;
                    $model_order_stock->trans_date = date('Y-m-d H:i:s');
                    $model_order_stock->company_id = $company_id;
                    $model_order_stock->branch_id = $branch_id;
                    if ($model_order_stock->save(false)) {
                        //  $this->updateStock($prod_id[$i], $line_qty[$i], $default_warehouse, $model->journal_no, $company_id, $branch_id);
                        // $pre_date_new = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                        // \common\models\OrderStock::updateAll(['avl_qty'=>0],['route_id'=>$route_id,'date(trans_date)'=>$pre_date_new,'product_id'=>$val2->product_id]);
                        if ($model_update_issue_status) {
                            if ($model_update_issue_status->status != 2) {
                                $model_update_issue_status->status = 2;
                                if ($model_update_issue_status->save(false)) {
                                    $status = 1;
                                }
                            }
                        }
                        //  // $this->updateStock($val2->product_id, $val2->qty, $default_wh, '', $company_id, $branch_id);
                        //$this->updateStock($val2->product_id, $val2->qty, $default_wh, $model_update_issue_status->journal_no, $company_id, $branch_id, $user_id, $route_id); // use this na ja not cut stock because has already reduct stock when picking

                        $this->updateStockCar($company_id, $branch_id, $val2->product_id, $model_update_issue_status->delivery_route_id); // add new deduct stock from car warehouse
                    }
                }

                // check old stock product not in issue line
                if ($check_route_type->type_id == 2) { // if is boots

                    $check_has_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->count(); // check has order stock daily

                    if (!$check_has_order_stock) {
                        $max_shift = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->max('order_shift');
                        $model_qty = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d'), 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift])->orderBy(['order_shift' => SORT_DESC])->all();
                        if ($model_qty) {
                            foreach ($model_qty as $value) {
                                $model_ck = \backend\models\Journalissueline::find()->where(['product_id' => $value->product_id, 'issue_id' => $issue_id])->one();
                                if (!$model_ck) { // not have in issue line
                                    $model_order_stock2 = new \common\models\OrderStock();
                                    $model_order_stock2->issue_id = 0; //$issue_id;
                                    $model_order_stock2->product_id = $value->product_id;
                                    $model_order_stock2->qty = $value->qty;
                                    $model_order_stock2->used_qty = 0;
                                    $model_order_stock2->avl_qty = $value->qty;;
                                    $model_order_stock2->order_id = 0;
                                    $model_order_stock2->route_id = $model_update_issue_status->delivery_route_id;
                                    $model_order_stock2->trans_date = date('Y-m-d H:i:s');
                                    $model_order_stock2->company_id = $company_id;
                                    $model_order_stock2->branch_id = $branch_id;
                                    $model_order_stock2->save(false);
                                }
                            }
                        } else {
                            $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                            $max_shift2 = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date])->max('order_shift');
                            $model_qty2 = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date, 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift2])->orderBy(['order_shift' => SORT_DESC])->all();
                            if ($model_qty2) {
                                foreach ($model_qty2 as $value2) {
                                    $model_ck2 = \backend\models\Journalissueline::find()->where(['product_id' => $value2->product_id, 'issue_id' => $issue_id])->one();
                                    if (!$model_ck2) { // not have in issue line
                                        $model_order_stock3 = new \common\models\OrderStock();
                                        $model_order_stock3->issue_id = 0; //$issue_id;
                                        $model_order_stock3->product_id = $value2->product_id;
                                        $model_order_stock3->qty = $value2->qty;
                                        $model_order_stock3->used_qty = 0;
                                        $model_order_stock3->avl_qty = $value2->qty;;
                                        $model_order_stock3->order_id = 0;
                                        $model_order_stock3->route_id = $model_update_issue_status->delivery_route_id;
                                        $model_order_stock3->trans_date = date('Y-m-d H:i:s');
                                        $model_order_stock3->company_id = $company_id;
                                        $model_order_stock3->branch_id = $branch_id;
                                        $model_order_stock3->save(false);
                                    }
                                }
                            }
                        }
                    } else { // not issue but has old qty
                        $model_order_stock_qty = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->all();
                        foreach ($model_order_stock_qty as $value) {
                            $model_ck = \backend\models\Journalissueline::find()->where(['product_id' => $value->product_id, 'issue_id' => $issue_id])->one();
                            if (!$model_ck) { // not have in issue line
                                $model_order_stock2 = new \common\models\OrderStock();
                                $model_order_stock2->issue_id = 0; //$issue_id;
                                $model_order_stock2->product_id = $value->product_id;
                                $model_order_stock2->qty = $value->qty;
                                $model_order_stock2->used_qty = 0;
                                $model_order_stock2->avl_qty = $value->qty;;
                                $model_order_stock2->order_id = 0;
                                $model_order_stock2->route_id = $model_update_issue_status->delivery_route_id;
                                $model_order_stock2->trans_date = date('Y-m-d H:i:s');
                                $model_order_stock2->company_id = $company_id;
                                $model_order_stock2->branch_id = $branch_id;
                                $model_order_stock2->save(false);
                            }
                        }
                    }

                }

//
            }

//            $model = \backend\models\Journalissue::find()->where(['id' => $issue_id])->one();
//            if ($model) {
//                $model->status = 2; //close
//                $model->user_confirm = $user_id;
//                if ($model->save()) {
//                    $status = 1;
//                }
//            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function actionIssueconfirm2vp18()
    {
        $issue_id = null;
        $user_id = null;
        $route_id = null;
        $company_id = 0;
        $branch_id = 0;
        $status = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        $issue_id = $req_data['issue_id'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $default_wh = 0; // 1 6
//        if ($company_id == 1 && $branch_id == 2) {
//            $default_wh = 5;
//        }
        $default_wh = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id);

        $data = [];
        if ($issue_id != null && $user_id != null) {
            //$data = ['issue_id'=> $issue_id,'user_id'=>$user_id];
            $has_order_stock = $this->checkhasOrderStock($route_id, $issue_id);
            if ($has_order_stock == 0) {
                $check_route_type = \backend\models\Deliveryroute::find()->where(['id' => $route_id])->one();
                $model_update_issue_status = \common\models\JournalIssue::find()->where(['id' => $issue_id])->one();
                $model_issue_line = \backend\models\Journalissueline::find()->where(['issue_id' => $issue_id])->all();
                foreach ($model_issue_line as $val2) {
                    if ($val2->qty <= 0 || $val2->qty == null) continue;

                    $old_stock = 0;

                    $model_check_has_old_product = \common\models\OrderStock::find()->where(['product_id' => $val2->product_id, 'route_id' => $model_update_issue_status->delivery_route_id])->orderBy(['id'=>SORT_DESC])->one();
                    if ($model_check_has_old_product) {
                        $model_check_has_old_product->qty =  $val2->qty;
                        $model_check_has_old_product->avl_qty = ($model_check_has_old_product->avl_qty) + $val2->qty;
                        $model_check_has_old_product->trans_date = date('Y-m-d H:i:s');
                        if($model_check_has_old_product->save(false)){
                            if ($model_update_issue_status) {
                                if ($model_update_issue_status->status != 2) {
                                    $model_update_issue_status->status = 2;
                                    if ($model_update_issue_status->save(false)) {
                                        $status = 1;
                                    }
                                }
                            }
                            $this->updateStockCar($company_id, $branch_id, $val2->product_id, $model_update_issue_status->delivery_route_id); // add new deduct stock from car warehouse
                        }
                    } else {
                        $model_order_stock = new \common\models\OrderStock();
                        $model_order_stock->issue_id = $issue_id;
                        $model_order_stock->product_id = $val2->product_id;
                        //  $model_order_stock->qty = ($val2->qty + $old_stock);
                        $model_order_stock->qty = $val2->qty;
                        $model_order_stock->used_qty = 0;
                        // $model_order_stock->avl_qty = ($val2->qty + $old_stock);
                        $model_order_stock->avl_qty = $val2->qty;
                        $model_order_stock->order_id = 0;
                        $model_order_stock->route_id = $model_update_issue_status->delivery_route_id;
                        $model_order_stock->trans_date = date('Y-m-d H:i:s');
                        $model_order_stock->company_id = $company_id;
                        $model_order_stock->branch_id = $branch_id;
                        if ($model_order_stock->save(false)) {
                            //  $this->updateStock($prod_id[$i], $line_qty[$i], $default_warehouse, $model->journal_no, $company_id, $branch_id);
                            // $pre_date_new = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                            // \common\models\OrderStock::updateAll(['avl_qty'=>0],['route_id'=>$route_id,'date(trans_date)'=>$pre_date_new,'product_id'=>$val2->product_id]);
                            if ($model_update_issue_status) {
                                if ($model_update_issue_status->status != 2) {
                                    $model_update_issue_status->status = 2;
                                    if ($model_update_issue_status->save(false)) {
                                        $status = 1;
                                    }
                                }
                            }
                            //  // $this->updateStock($val2->product_id, $val2->qty, $default_wh, '', $company_id, $branch_id);
                            //$this->updateStock($val2->product_id, $val2->qty, $default_wh, $model_update_issue_status->journal_no, $company_id, $branch_id, $user_id, $route_id); // use this na ja not cut stock because has already reduct stock when picking

                            $this->updateStockCar($company_id, $branch_id, $val2->product_id, $model_update_issue_status->delivery_route_id); // add new deduct stock from car warehouse
                        }
                    }
                }

                // check old stock product not in issue line
                if ($check_route_type->type_id == 2) { // if is boots

                    $check_has_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->count(); // check has order stock daily

                    if (!$check_has_order_stock) {
                        $max_shift = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->max('order_shift');
                        $model_qty = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d'), 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift])->orderBy(['order_shift' => SORT_DESC])->all();
                        if ($model_qty) {
                            foreach ($model_qty as $value) {
                                $model_ck = \backend\models\Journalissueline::find()->where(['product_id' => $value->product_id, 'issue_id' => $issue_id])->one();
                                if (!$model_ck) { // not have in issue line
                                    $model_order_stock2 = new \common\models\OrderStock();
                                    $model_order_stock2->issue_id = 0; //$issue_id;
                                    $model_order_stock2->product_id = $value->product_id;
                                    $model_order_stock2->qty = $value->qty;
                                    $model_order_stock2->used_qty = 0;
                                    $model_order_stock2->avl_qty = $value->qty;;
                                    $model_order_stock2->order_id = 0;
                                    $model_order_stock2->route_id = $model_update_issue_status->delivery_route_id;
                                    $model_order_stock2->trans_date = date('Y-m-d H:i:s');
                                    $model_order_stock2->company_id = $company_id;
                                    $model_order_stock2->branch_id = $branch_id;
                                    $model_order_stock2->save(false);
                                }
                            }
                        } else {
                            $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                            $max_shift2 = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date])->max('order_shift');
                            $model_qty2 = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date, 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift2])->orderBy(['order_shift' => SORT_DESC])->all();
                            if ($model_qty2) {
                                foreach ($model_qty2 as $value2) {
                                    $model_ck2 = \backend\models\Journalissueline::find()->where(['product_id' => $value2->product_id, 'issue_id' => $issue_id])->one();
                                    if (!$model_ck2) { // not have in issue line
                                        $model_order_stock3 = new \common\models\OrderStock();
                                        $model_order_stock3->issue_id = 0; //$issue_id;
                                        $model_order_stock3->product_id = $value2->product_id;
                                        $model_order_stock3->qty = $value2->qty;
                                        $model_order_stock3->used_qty = 0;
                                        $model_order_stock3->avl_qty = $value2->qty;;
                                        $model_order_stock3->order_id = 0;
                                        $model_order_stock3->route_id = $model_update_issue_status->delivery_route_id;
                                        $model_order_stock3->trans_date = date('Y-m-d H:i:s');
                                        $model_order_stock3->company_id = $company_id;
                                        $model_order_stock3->branch_id = $branch_id;
                                        $model_order_stock3->save(false);
                                    }
                                }
                            }
                        }
                    } else { // not issue but has old qty
                        $model_order_stock_qty = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->all();
                        foreach ($model_order_stock_qty as $value) {
                            $model_ck = \backend\models\Journalissueline::find()->where(['product_id' => $value->product_id, 'issue_id' => $issue_id])->one();
                            if (!$model_ck) { // not have in issue line
                                $model_order_stock2 = new \common\models\OrderStock();
                                $model_order_stock2->issue_id = 0; //$issue_id;
                                $model_order_stock2->product_id = $value->product_id;
                                $model_order_stock2->qty = $value->qty;
                                $model_order_stock2->used_qty = 0;
                                $model_order_stock2->avl_qty = $value->qty;;
                                $model_order_stock2->order_id = 0;
                                $model_order_stock2->route_id = $model_update_issue_status->delivery_route_id;
                                $model_order_stock2->trans_date = date('Y-m-d H:i:s');
                                $model_order_stock2->company_id = $company_id;
                                $model_order_stock2->branch_id = $branch_id;
                                $model_order_stock2->save(false);
                            }
                        }
                    }

                }

            }
        }
        return ['status' => $status, 'data' => $data];
    }
    public function actionIssueconfirm2()
    {
        $issue_id = null;
        $user_id = null;
        $route_id = null;
        $company_id = 0;
        $branch_id = 0;
        $status = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        $issue_id = $req_data['issue_id'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $default_wh = 0; // 1 6
//        if ($company_id == 1 && $branch_id == 2) {
//            $default_wh = 5;
//        }
        $default_wh = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id);

        $data = [];
        if ($issue_id != null && $user_id != null) {
            //$data = ['issue_id'=> $issue_id,'user_id'=>$user_id];
            $has_order_stock = $this->checkhasOrderStock($route_id, $issue_id);
            if ($has_order_stock == 0) {
                $check_route_type = \backend\models\Deliveryroute::find()->where(['id' => $route_id])->one();
                $model_update_issue_status = \common\models\JournalIssue::find()->where(['id' => $issue_id])->one();
                if($model_update_issue_status == null){ // not have journal issue no
                  return ['status' => $status, 'data' => $data];
                }else if($model_update_issue_status->status == 2){ // journal already approved
                  return ['status' => $status, 'data' => $data];
                }
                $model_issue_line = \backend\models\Journalissueline::find()->where(['issue_id' => $issue_id])->all();
                foreach ($model_issue_line as $val2) {
                    if ($val2->qty <= 0 || $val2->qty == null) continue;

                    $old_stock = 0;

                    $model_check_has_old_product = \common\models\OrderStock::find()->where(['product_id' => $val2->product_id, 'route_id' => $model_update_issue_status->delivery_route_id])->orderBy(['id'=>SORT_DESC])->one();
                    if ($model_check_has_old_product) {
                        $model_check_has_old_product->qty = $val2->qty;
                        $model_check_has_old_product->avl_qty = (($model_check_has_old_product->avl_qty) + $val2->qty);
                        $model_check_has_old_product->trans_date = date('Y-m-d H:i:s');
                        if($model_check_has_old_product->save(false)){
                            if ($model_update_issue_status) {
                                if ($model_update_issue_status->status != 2) {
                                    $model_update_issue_status->status = 2;
                                    if ($model_update_issue_status->save(false)) {
                                        $status = 1;
                                    }
                                }
                            }
                            $this->updateStockCar($company_id, $branch_id, $val2->product_id, $model_update_issue_status->delivery_route_id); // add new deduct stock from car warehouse
                        }
                    } else {
                        $model_order_stock = new \common\models\OrderStock();
                        $model_order_stock->issue_id = $issue_id;
                        $model_order_stock->product_id = $val2->product_id;
                        //  $model_order_stock->qty = ($val2->qty + $old_stock);
                        $model_order_stock->qty = $val2->qty;
                        $model_order_stock->used_qty = 0;
                        // $model_order_stock->avl_qty = ($val2->qty + $old_stock);
                        $model_order_stock->avl_qty = $val2->qty;
                        $model_order_stock->order_id = 0;
                        $model_order_stock->route_id = $model_update_issue_status->delivery_route_id;
                        $model_order_stock->trans_date = date('Y-m-d H:i:s');
                        $model_order_stock->company_id = $company_id;
                        $model_order_stock->branch_id = $branch_id;
                        if ($model_order_stock->save(false)) {
                            //  $this->updateStock($prod_id[$i], $line_qty[$i], $default_warehouse, $model->journal_no, $company_id, $branch_id);
                            // $pre_date_new = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                            // \common\models\OrderStock::updateAll(['avl_qty'=>0],['route_id'=>$route_id,'date(trans_date)'=>$pre_date_new,'product_id'=>$val2->product_id]);
                            if ($model_update_issue_status) {
                                if ($model_update_issue_status->status != 2) {
                                    $model_update_issue_status->status = 2;
                                    if ($model_update_issue_status->save(false)) {
                                        $status = 1;
                                    }
                                }
                            }
                            //  // $this->updateStock($val2->product_id, $val2->qty, $default_wh, '', $company_id, $branch_id);
                            //$this->updateStock($val2->product_id, $val2->qty, $default_wh, $model_update_issue_status->journal_no, $company_id, $branch_id, $user_id, $route_id); // use this na ja not cut stock because has already reduct stock when picking

                            $this->updateStockCar($company_id, $branch_id, $val2->product_id, $model_update_issue_status->delivery_route_id); // add new deduct stock from car warehouse
                        }
                    }
                }

                // check old stock product not in issue line
                if ($check_route_type->type_id == 2) { // if is boots

                    $check_has_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->count(); // check has order stock daily

                    if (!$check_has_order_stock) {
                        $max_shift = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->max('order_shift');
                        $model_qty = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d'), 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift])->orderBy(['order_shift' => SORT_DESC])->all();
                        if ($model_qty) {
                            foreach ($model_qty as $value) {
                                $model_ck = \backend\models\Journalissueline::find()->where(['product_id' => $value->product_id, 'issue_id' => $issue_id])->one();
                                if (!$model_ck) { // not have in issue line
                                    $model_order_stock2 = new \common\models\OrderStock();
                                    $model_order_stock2->issue_id = 0; //$issue_id;
                                    $model_order_stock2->product_id = $value->product_id;
                                    $model_order_stock2->qty = $value->qty;
                                    $model_order_stock2->used_qty = 0;
                                    $model_order_stock2->avl_qty = $value->qty;;
                                    $model_order_stock2->order_id = 0;
                                    $model_order_stock2->route_id = $model_update_issue_status->delivery_route_id;
                                    $model_order_stock2->trans_date = date('Y-m-d H:i:s');
                                    $model_order_stock2->company_id = $company_id;
                                    $model_order_stock2->branch_id = $branch_id;
                                    $model_order_stock2->save(false);
                                }
                            }
                        } else {
                            $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                            $max_shift2 = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date])->max('order_shift');
                            $model_qty2 = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date, 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift2])->orderBy(['order_shift' => SORT_DESC])->all();
                            if ($model_qty2) {
                                foreach ($model_qty2 as $value2) {
                                    $model_ck2 = \backend\models\Journalissueline::find()->where(['product_id' => $value2->product_id, 'issue_id' => $issue_id])->one();
                                    if (!$model_ck2) { // not have in issue line
                                        $model_order_stock3 = new \common\models\OrderStock();
                                        $model_order_stock3->issue_id = 0; //$issue_id;
                                        $model_order_stock3->product_id = $value2->product_id;
                                        $model_order_stock3->qty = $value2->qty;
                                        $model_order_stock3->used_qty = 0;
                                        $model_order_stock3->avl_qty = $value2->qty;;
                                        $model_order_stock3->order_id = 0;
                                        $model_order_stock3->route_id = $model_update_issue_status->delivery_route_id;
                                        $model_order_stock3->trans_date = date('Y-m-d H:i:s');
                                        $model_order_stock3->company_id = $company_id;
                                        $model_order_stock3->branch_id = $branch_id;
                                        $model_order_stock3->save(false);
                                    }
                                }
                            }
                        }
                    } else { // not issue but has old qty
                        $model_order_stock_qty = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->all();
                        foreach ($model_order_stock_qty as $value) {
                            $model_ck = \backend\models\Journalissueline::find()->where(['product_id' => $value->product_id, 'issue_id' => $issue_id])->one();
                            if (!$model_ck) { // not have in issue line
                                $model_order_stock2 = new \common\models\OrderStock();
                                $model_order_stock2->issue_id = 0; //$issue_id;
                                $model_order_stock2->product_id = $value->product_id;
                                $model_order_stock2->qty = $value->qty;
                                $model_order_stock2->used_qty = 0;
                                $model_order_stock2->avl_qty = $value->qty;;
                                $model_order_stock2->order_id = 0;
                                $model_order_stock2->route_id = $model_update_issue_status->delivery_route_id;
                                $model_order_stock2->trans_date = date('Y-m-d H:i:s');
                                $model_order_stock2->company_id = $company_id;
                                $model_order_stock2->branch_id = $branch_id;
                                $model_order_stock2->save(false);
                            }
                        }
                    }

                }

            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function actionIssueconfirmcancel()
    {
        $issue_id = null;
        $user_id = null;
        $route_id = null;
        $company_id = 1;
        $branch_id = 1;
        $status = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        $issue_id = $req_data['issue_id'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $default_wh = 6; //1 6
        if ($company_id == 1 && $branch_id == 2) {
            $default_wh = 5;
        }

        $data = [];
        if ($issue_id != null && $company_id != null && $branch_id != null) {
            //$data = ['issue_id'=> $issue_id,'user_id'=>$user_id];
            $model_update_issue_status = \common\models\JournalIssue::find()->where(['id' => $issue_id, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();
            if ($model_update_issue_status) {
                $model_update_issue_status->status = 200; // 200 cancel
                if ($model_update_issue_status->save(false)) {
                    $status = 1;
                    array_push($data, ['message' => 'cancel success']);
                }
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function updateStock($product_id, $qty, $wh_id, $journal_no, $company_id, $branch_id, $user_id, $route_id)
    {
        if ($product_id != null && $qty > 0) {
            $model_trans = new \backend\models\Stocktrans();
            $model_trans->journal_no = $journal_no;
            $model_trans->trans_date = date('Y-m-d H:i:s');
            $model_trans->product_id = $product_id;
            $model_trans->qty = $qty;
            $model_trans->warehouse_id = $wh_id;
            $model_trans->stock_type = 2; // 1 in 2 out
            $model_trans->activity_type_id = 6; // 6 issue car
            $model_trans->company_id = $company_id;
            $model_trans->branch_id = $branch_id;
            $model_trans->created_by = $user_id;
            if ($model_trans->save(false)) {
                // new
                $car_warehouse = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id);
                $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $car_warehouse, 'product_id' => $product_id, 'route_id' => $route_id])->one(); // find warehouse car stock for deduct
                if ($model) {
                    if ((int)$model->qty < (int)$qty) {
                        return false;
                    } else {
                        $model->qty = (int)$model->qty - (int)$qty;
                        $model->save(false);
                    }

                }
// original
//                $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $wh_id, 'product_id' => $product_id])->one();
//                if ($model) {
//                    if ((int)$model->qty < (int)$qty) {
//                        return false;
//                    } else {
//                        $model->qty = (int)$model->qty - (int)$qty;
//                        $model->save(false);
//                    }
//
//                }
            }
        }
    }

    public function ismasterproduct($product_id)
    {
        $res = 0;
        if ($product_id) {
            $model = \backend\models\Product::find()->where(['id' => $product_id, 'master_product' => 1])->one();
            if ($model) {
                $res = $model->master_product == null ? 0 : $model->master_product;
            }
        }
        return $res;
    }

    public function actionCheckopen()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        // $car_id = $req_data['car_id'];
        $issue_date = $req_data['issue_date'];

        $data = [];
        $status = false;
        if ($route_id) {
            $trans_date = date('Y/m/d');
            $t_date = null;
            $exp_order_date = explode(' ', $issue_date);
            if ($exp_order_date != null) {
                if (count($exp_order_date) > 1) {
                    $x_date = explode('-', $exp_order_date[0]);
                    if (count($x_date) > 1) {
                        $t_date = $x_date[0] . "/" . $x_date[1] . "/" . $x_date[2];
                    }
                }
            }
            if ($t_date != null) {
                $trans_date = $t_date;
            }
            // $model = \common\models\JournalIssue::find()->one();
            $model = \common\models\JournalIssue::find()->where(['delivery_route_id' => $route_id, 'date(trans_date)' => $trans_date, 'status' => 150])->one();
            if ($model) {
                $model_line = \common\models\JournalIssueLine::find()->where(['issue_id' => $model->id])->all();
                if ($model_line) {
                    foreach ($model_line as $value) {
                        if ($value->qty <= 0) continue;
                        array_push($data, [
                            'has_record' => 1,
                            'issue_id' => $model->id,
                            'product_id' => $value->product_id,
                            'code' => \backend\models\Product::findCode($value->product_id),
                            'name' => \backend\models\Product::findName($value->product_id),
                            'qty' => $value->qty,
                            'status' => $model->status,
                        ]);
                    }
                }
//                array_push($data, [
//                    'has_record' => 1,
//                    'issue_id' => $model->id,
//                    'status' => $model->status,
//                ]);
            } else {
                array_push($data, [
                    'has_record' => 0,
                    'issue_id' => 0,
                    'status' => 0,
                ]);
            }
        }
        return ['status' => $status, 'data' => $data];
    }


    public function actionIssueqrscan()
    {
        $issue_no = null;
        $company_id = 1;
        $branch_id = 1;
        $status = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        //$route_id = $req_data['route_id'];
        $issue_no = $req_data['issue_no'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $model = null;

        $data = [];
        if ($issue_no != null) {
            //$data = ['issue_id'=> $issue_id,'user_id'=>$user_id];
            $model_ch_order = \common\models\Orders::find()->where(['order_no' => $issue_no, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();
            if ($model_ch_order) {
                $model = \common\models\JournalIssue::find()->where(['order_ref_id' => $model_ch_order->id])->andFilterWhere(['<>', 'status', 200])->one();
            } else {
                $model = \common\models\JournalIssue::find()->where(['journal_no' => $issue_no, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();
            }

            if ($model) {
                $model_issue_line = \backend\models\Journalissueline::find()->where(['issue_id' => $model->id])->andFilterWhere(['>', 'qty', 0])->andFilterWhere(['<>', 'status', 200])->all();
                foreach ($model_issue_line as $val2) {
                    $status = 1;
                    array_push($data, [
                        'issue_id' => $model->id,
                        'issue_no' => $model->journal_no,
                        'issue_date' => date('d/m/Y', strtotime($model->trans_date)),
                        'route_name' => \backend\models\Deliveryroute::findName($model->delivery_route_id),
                        'issue_line_id' => $val2->id,
                        'product_id' => $val2->product_id,
                        'product_code' => \backend\models\Product::findCode($val2->product_id),
                        'product_name' => \backend\models\Product::findName($val2->product_id),
                        'issue_qty' => $val2->qty == 0 || $val2->qty == null ? 0 : $val2->qty,
                        'reserve_qty' => $this->findIssuereserve($model->id, $val2->product_id),
                    ]);
                }
            } else {
                $status = 0;
            }

        }
        return ['status' => $status, 'data' => $data];
    }

    public function actionIssueqrscan2()
    {
        $issue_no = null;
        $company_id = 1;
        $branch_id = 1;
        $status = 0;
        $issue_line_id = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        //$route_id = $req_data['route_id'];
        $issue_no = $req_data['issue_no'];
        $issue_line_id = $req_data['issue_line_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $model = null;
        $data = [];
        if ($issue_no != null) {
            //$data = ['issue_id'=> $issue_id,'user_id'=>$user_id];
            //$model = \common\models\JournalIssue::find()->where(['journal_no' => $issue_no, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();

            $model_ch_order = \common\models\Orders::find()->where(['order_no' => $issue_no, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();
            if ($model_ch_order) {
                $model = \common\models\JournalIssue::find()->where(['order_ref_id' => $model_ch_order->id])->andFilterWhere(['<>', 'status', 200])->one();
            } else {
                $model = \common\models\JournalIssue::find()->where(['journal_no' => $issue_no, 'company_id' => $company_id, 'branch_id' => $branch_id])->andFilterWhere(['<>', 'status', 200])->one();
            }

            if ($model) {
                $model_issue_line = \backend\models\Journalissueline::find()->where(['id' => $issue_line_id])->andFilterWhere(['>', 'qty', 0])->all();
                foreach ($model_issue_line as $val2) {
                    $status = 1;
                    array_push($data, [
                        'issue_id' => $model->id,
                        'issue_no' => $model->journal_no,
                        'issue_date' => date('d/m/Y', strtotime($model->trans_date)),
                        'route_name' => \backend\models\Deliveryroute::findName($model->delivery_route_id),
                        'issue_line_id' => $val2->id,
                        'product_id' => $val2->product_id,
                        'product_code' => \backend\models\Product::findCode($val2->product_id),
                        'product_name' => \backend\models\Product::findName($val2->product_id),
                        'issue_qty' => $val2->qty == 0 || $val2->qty == null ? 0 : $val2->qty,
                        'reserve_qty' => $this->findIssuereserve($val2->issue_id, $val2->product_id),
                    ]);
                }
            } else {
                $status = 0;
            }

        }
        return ['status' => $status, 'data' => $data];
    }

    public function findIssuereserve($issue_id, $product_id)
    {
        $qty = 0;
        if ($issue_id != null && $product_id != null) {
            $model = \common\models\IssueStockTemp::find()->where(['issue_id' => $issue_id, 'product_id' => $product_id])->sum('qty');
            if ($model) {
                $qty = $model;
            }
        }
        return $qty;
    }

    public function actionIssueqrscanaddtemp()
    {
        $issue_id = null;
        $status = 0;
        $prodrec_id = null;
        $issue_line_id = null;
        $product_id = null;
        $qty = 0;
        $user_id = null;
        $company_id = 1;
        $branch_id = 1;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $prodrec_id = $req_data['prodrec_id'];
        $issue_id = $req_data['issue_id'];
        $product_id = $req_data['product_id'];
        $qty = $req_data['qty'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];


        $data = [];
        if ($issue_id != null && $prodrec_id != null && $product_id != null && $qty != null) {
            $model = new \common\models\IssueStockTemp();
            $model->issue_id = $issue_id;
            $model->prodrec_id = $prodrec_id;
            $model->product_id = $product_id;
            $model->qty = $qty;
            $model->status = 1;
            $model->created_by = $user_id;
            $model->company_id = $company_id;
            $model->branch_id = $branch_id;
            $model->crated_at = time();
            if ($model->save(false)) {
                $status = 1;
                array_push($data, ['message' => 'complated']);
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function actionIssuetempconfirm()
    {
        $company_id = 0;
        $branch_id = 0;
        $issue_id = null;
        $defaultwarehouse = 0;
        $status = 0;
        $confirm_status = null;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $issue_id = $req_data['issue_id'];
        $confirm_status = $req_data['confirm_status'];

        $data = [];
        $res = 0;
        $mode = '';
        if ($issue_id != null) {
            if (\common\models\IssueStockTemp::updateAll(['status' => $confirm_status], ['issue_id' => $issue_id])) {
                \common\models\JournalIssueLine::updateAll(['qty' => 0], ['issue_id' => $issue_id]);
                $model = \common\models\IssueStockTemp::find()->where(['issue_id' => $issue_id])->all();
                if ($model) {
                    foreach ($model as $value) {
//                        if ($value->branch_id == 2) {
//                            $defaultwarehouse = 5;
//                        }
                        $warehouse_primary = \backend\models\Warehouse::findPrimary($value->company_id, $value->branch_id);
                        $defaultwarehouse = $warehouse_primary;

                        $model_update = \backend\models\Journalissueline::find()->where(['issue_id' => $value->issue_id, 'product_id' => $value->product_id])->one();
                        if ($model_update) {
                            $model_update->qty = ($model_update->qty + $value->qty);
                            $model_update->avl_qty = $model_update->qty;
                            $model_update->updated_by = $value->created_by;
                            $model_update->temp_update = date('Y-m-d H:i:s');
                            if ($model_update->save(false)) {
                                $issue_m = \backend\models\Journalissue::find()->where(['id' => $value->issue_id])->one();
                                if ($issue_m) {
                                    $issue_m->updated_by = $value->created_by;
                                    $issue_m->status = 150; // confirm issue
                                    if ($issue_m->save(false)) {
                                        $res += 1;

                                        $check_is_order_car = \backend\models\Orders::find()->where(['id' => $issue_m->order_ref_id])->andFilterWhere(['company_id' => $issue_m->company_id, 'branch_id' => $issue_m->branch_id])->one();

                                        if ($check_is_order_car->customer_id > 0) {  // add new 01102021  reduce stock for pos sale not include mobile sale
                                            // $this->updateSummary($value->product_id,$defaultwarehouse,$value->qty);
                                            $mode = 'pos';
                                        } else {  // add new 01102021  transfer main warehouse to car warehouse for mobile sale
                                            $mode = 'car';
                                            $this->transfertocarwarehouse($issue_m->company_id, $issue_m->branch_id, $value->product_id, $defaultwarehouse, $value->qty, $issue_m->delivery_route_id);
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
                if ($res > 0) {
                    $status = 1;
                    array_push($data, ['message' => 'completed', 'stock_mode' => $mode]);
                }

            }
        }
        return ['status' => $status, 'data' => $data];
    }

//    public function actionIssuetempconfirmtest()
//    {
//        $issue_id = null;
//        $status = 0;
//        $confirm_status = null;
//
//        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//        $req_data = \Yii::$app->request->getBodyParams();
//        $issue_id = $req_data['issue_id'];
//        $confirm_status = $req_data['confirm_status'];
//
//        $data = [];
//        $res = 0;
//        if ($issue_id != null) {
//            if (1 > 0) {
//                $model = \common\models\IssueStockTemp::find()->where(['issue_id' => $issue_id])->all();
//                if ($model) {
//                    foreach ($model as $value) {
//                        $model_update = \backend\models\Journalissueline::find()->where(['issue_id' => $value->issue_id, 'product_id' => $value->product_id])->one();
//                        if ($model_update) {
//                            array_push($data, ['issue_id' => $value->issue_id, 'product_id' => $model_update->product_id, 'qty' => $model_update->qty]);
//                        }
//                    }
//                }
//            }
//        }
//        return ['status' => $status, 'data' => $data];
//    }

    public function updateSummary($product_id, $wh_id, $qty)
    {
        if ($wh_id != null && $product_id != null && $qty > 0) {
            $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $wh_id, 'product_id' => $product_id])->one();
            if ($model) {
                if ((int)$model->qty < (int)$qty) {
                    return false;
                }
                $model->qty = (int)$model->qty - (int)$qty;
                if ($model->save(false)) {
                    return true;
                }
            } else {
//                $model_new = new \backend\models\Stocksum();
//                $model_new->warehouse_id = $wh_id;
//                $model_new->product_id = $product_id;
//                $model_new->qty = $qty;
//                $model_new->save(false);
            }
        }
        return false;
    }

    public function transfertocarwarehouse($company_id, $branch_id, $product_id, $wh_id, $qty, $route_id)
    {
        if ($wh_id != null && $product_id != null && $qty > 0 && $route_id != null) {
            $car_warehouse = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id);
            if ($car_warehouse > 0) {
                $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $wh_id, 'product_id' => $product_id])->one();
                if ($model) {
                    if ((int)$model->qty < (int)$qty) {
                        return false;
                    }
                    $model->qty = (int)$model->qty - (int)$qty; // cut stock from main warehouse
                    if ($model->save(false)) {
                        $check_car_wh = \backend\models\Stocksum::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'warehouse_id' => $car_warehouse])->one();
                        if ($check_car_wh) {
                            $check_car_wh->qty = (int)$check_car_wh->qty + (int)$qty; // add stock
                            $check_car_wh->save(false);
                        } else {
                            $new_car_wh = new \backend\models\Stocksum(); // add new line stock
                            $new_car_wh->product_id = $product_id;
                            $new_car_wh->warehouse_id = $car_warehouse;
                            $new_car_wh->qty = $qty;
                            $new_car_wh->route_id = $route_id;
                            $new_car_wh->company_id = $company_id;
                            $new_car_wh->branch_id = $branch_id;
                            $new_car_wh->save(false);
                        }
                        return true;
                    }
                } else {

                }
            }

        }
        // return false;
    }

    public function updateStockCar($company_id, $branch_id, $product_id, $route_id)
    {
        if ($product_id != null && $route_id != null && $company_id && $branch_id) {
            $car_warehouse = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id);
            if ($car_warehouse) {
                $check_car_wh = \backend\models\Stocksum::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'warehouse_id' => $car_warehouse])->one();
                if ($check_car_wh) {
                    $check_car_wh->qty = 0; // reset stock
                    $check_car_wh->save(false);
                }
                return true;
            }
        }
        //   return false;
    }

    public function actionIssueforreprocess()
    {
        $company_id = 0;
        $branch_id = 0;
        $product_id = null;
        $defaultwarehouse = 0;
        $status = 0;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $product_id = $req_data['product_id'];
        $qty = $req_data['qty'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $data = [];
        $res = 0;
        $mode = '';

        if ($company_id && $branch_id && $product_id && $qty) {
            $model = new Journalissue();
            $journal_no = $model->getLastReprocessNo(date('Y-m-d'), $company_id, $branch_id);
            $model->journal_no = $journal_no;
            $model->trans_date = date('Y-m-d H:i:s');
            $model->status = 1;
            $model->company_id = $company_id;
            $model->branch_id = $branch_id;
            $model->delivery_route_id = 0;
            $model->reason_id = 4; // 3 refill 4 reprocess
            if ($model->save()) {
                $status = 1;
                if ($product_id != null) {
                    $model_line = new \backend\models\Journalissueline();
                    $model_line->issue_id = $model->id;
                    $model_line->product_id = $product_id;
                    $model_line->qty = $qty;
                    $model_line->avl_qty = $qty;
                    $model_line->sale_price = 0;
                    $model_line->status = 1;
                    if ($model_line->save()) {
                        $this->updateStockForReprocess($product_id, $qty, $model->journal_no, $company_id, $branch_id, $user_id);
                    }
                }
                array_push($data, ['journal_no' => $journal_no]);
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function updateStockForReprocess($product_id, $qty, $journal_no, $company_id, $branch_id, $user_id)
    {
        $wh_id = \backend\models\Warehouse::findPrimary($company_id, $branch_id);
        if ($product_id != null && $qty > 0) {
            $model_trans = new \backend\models\Stocktrans();
            $model_trans->journal_no = $journal_no;
            $model_trans->trans_date = date('Y-m-d H:i:s');
            $model_trans->product_id = $product_id;
            $model_trans->qty = $qty;
            $model_trans->warehouse_id = $wh_id;
            $model_trans->stock_type = 2; // 1 in 2 out
            $model_trans->activity_type_id = 20; // 20 เบิก reprocess
            $model_trans->company_id = $company_id;
            $model_trans->branch_id = $branch_id;
            $model_trans->created_by = $user_id;
            if ($model_trans->save(false)) {
                $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $wh_id, 'product_id' => $product_id])->andFilterWhere(['>', 'qty', 0])->one(); // find warehouse car stock for deduct
                if ($model) {
                    if ((int)$model->qty < (int)$qty) {
                        return false;
                    } else {
                        $model->qty = (int)$model->qty - (int)$qty;
                        $model->save(false);
                    }

                }
            }
        }
    }

    public function actionRepackselect()
    {
        $company_id = 0;
        $branch_id = 0;
        $trans_date = null;
        $journal_no = null;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];
        $journal_no = $req_data['journal_no'];

        $data = [];
        $status = false;

        if ($company_id && $branch_id) {
            $find_date = date('Y-m-d');

            $model = \common\models\StockTrans::find()->where(['company_id' => $company_id, 'branch_id' => $branch_id, 'date(trans_date)' => $find_date])->andFilterWhere(['=', 'journal_no', $journal_no])->one();

            if ($model) {
                $status = true;

                array_push($data, [
                    'id' => $model->id,
                    'journal_no' => $journal_no,
                    'company_id' => $model->company_id,
                    'branch_id' => $model->branch_id,
                    'product_code' => \backend\models\Product::findCode($model->product_id),
                    'product_name' => \backend\models\Product::findName($model->product_id),
                    'qty' => $model->qty,
                ]);

            }
        }

        return ['status' => $status, 'data' => $data];
    }

    public function actionOldstockroute()
    {
        $route_id = null;
        $company_id = null;
        $branch_id = null;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $data = [];
        $status = false;
        if ($route_id) {
            $max_shift = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->max('order_shift');
            $model = \common\models\SaleRouteDailyClose::find()->select(['id', 'route_id', 'product_id', 'SUM(qty) as qty'])->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d'), 'order_shift' => $max_shift])->groupBy(['product_id'])->all();
            //  $model = \common\models\SaleRouteDailyClose::find()->select(['id', 'route_id', 'product_id', 'SUM(qty) as qty'])->where(['route_id' => $route_id,'date(trans_date)'=>date('Y-m-d')])->groupBy(['product_id'])->orderBy(['order_shift'=>SORT_DESC])->all();
            if ($model) {
                $status = true;
                foreach ($model as $value) {
                    if ($value->qty == null || $value->qty <= 0) continue;
                    array_push($data, [
                        'id' => $value->id,
                        'product_code' => \backend\models\Product::findCode($value->product_id),
                        'product_id' => $value->product_id,
                        'product_name' => \backend\models\Product::findName($value->product_id),
                        'qty' => $value->qty
                    ]);
                }
            } else {
                $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                $max_shift = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date])->max('order_shift');
                $model2 = \common\models\SaleRouteDailyClose::find()->select(['id', 'route_id', 'product_id', 'SUM(qty) as qty'])->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date, 'order_shift' => $max_shift])->groupBy(['product_id'])->all();
                if ($model2) {
                    $status = true;
                    foreach ($model2 as $value2) {
                        if ($value2->qty == null || $value2->qty <= 0) continue;
                        array_push($data, [
                            'id' => $value2->id,
                            'product_code' => \backend\models\Product::findCode($value2->product_id),
                            'product_id' => $value2->product_id,
                            'product_name' => \backend\models\Product::findName($value2->product_id),
                            'qty' => $value2->qty
                        ]);
                    }
                }
            }
        }

        return ['status' => $status, 'data' => $data];
    }

    public function getProductOldstock($product_id, $route_id, $company_id, $branch_id)
    {
        $old_qty = 0;

        if ($route_id && $product_id && $company_id && $branch_id) {
            $max_shift = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->max('order_shift');
            // $model_qty = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'product_id' => $product_id,'date(trans_date)'=>date('Y-m-d'), 'company_id' => $company_id, 'branch_id' => $branch_id])->sum('qty');
            $model_qty = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => date('Y-m-d'), 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift])->orderBy(['order_shift' => SORT_DESC])->one();
            if ($model_qty) {
                $old_qty = $model_qty->qty;
            } else {
                $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -2 day"));
                $max_shift = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'date(trans_date)' => $pre_date])->max('order_shift');
                $model_qty2 = \common\models\SaleRouteDailyClose::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => $pre_date, 'company_id' => $company_id, 'branch_id' => $branch_id, 'order_shift' => $max_shift])->orderBy(['order_shift' => SORT_DESC])->one();
                if ($model_qty2) {
                    $old_qty = $model_qty2->qty;
                }
            }
        }
        return $old_qty;
    }

    public function getProductOrderstock($product_id, $route_id, $company_id, $branch_id)
    {
        $old_qty = 0;

        if ($route_id && $product_id && $company_id && $branch_id) {
//            $model_qty = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $product_id,'date(trans_date)'=>date('Y-m-d'), 'company_id' => $company_id, 'branch_id' => $branch_id])->orderBy(['id'=>SORT_DESC])->one();
//            if ($model_qty) {
//                $old_qty = $model_qty->qty;
//            }
            $model_qty = \common\models\OrderStock::find()->select(['avl_qty'])->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => date('Y-m-d'), 'company_id' => $company_id, 'branch_id' => $branch_id])->orderBy(['id' => SORT_DESC])->all();
            if ($model_qty) {
                foreach ($model_qty as $value) {
                    $old_qty = ($old_qty + $value->avl_qty);
                }

            }
        }
        return $old_qty;
    }

    public function getProductOrderstock2($product_id, $route_id, $company_id, $branch_id)
    {
        $old_qty = 0;
        $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -2 day"));
        if ($route_id && $product_id && $company_id && $branch_id) {
//            $model_qty = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $product_id,'date(trans_date)'=>$pre_date, 'company_id' => $company_id, 'branch_id' => $branch_id])->orderBy(['id'=>SORT_DESC])->one();
//            if ($model_qty) {
//                $old_qty = $model_qty->qty;
//            }
            $model_qty = \common\models\OrderStock::find()->select(['avl_qty'])->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => $pre_date, 'company_id' => $company_id, 'branch_id' => $branch_id])->orderBy(['id' => SORT_DESC])->all();
            if ($model_qty) {
                foreach ($model_qty as $value) {
                    $old_qty = ($old_qty + $value->avl_qty);
                }

            }
        }

        return $old_qty;
    }
}
