<?php

namespace frontend\modules\api\controllers;

use backend\models\Employee;
use backend\models\Orders;
use backend\models\Position;
use backend\models\Stocktrans;
use backend\models\User;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\Controller;

date_default_timezone_set('Asia/Bangkok');

class OrderController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'addorder' => ['POST'],
                    'addordernew' => ['POST'],
                    'addordernewvp19' => ['POST'],
                    'addordernewlast' => ['POST'],
                    'addordertransfer' => ['POST'],
                    'list' => ['POST'],
                    'listnew' => ['POST'],
                    'listbycustomer' => ['POST'],
                    'deleteorder' => ['POST'],
                    'deleteorderline' => ['POST'],
                    'deleteordercustomer' => ['POST'],
                    'customercredit' => ['POST'],
                    'closeorder' => ['POST'],
                    'cancelorer' => ['POST'],
                    'cancelordervp19' => ['POST'],
                    'testline' => ['POST'],
                    'orderdiscount' => ['POST'],
                    'getlastorderno' => ['POST'],
                    'createnotifyclose' => ['POST'],
                ],
            ],
        ];
    }

    public function actionTestline()
    {
        return $this->notifymessageorderclose(884, 1, 1, 1);
    }

    public function actionAddordernew2()
    {


        $customer_id = null;
        $status = false;
        $user_id = 0;
        $issue_id = 0;
        $route_id = 0;
        $car_id = 0;
        $company_id = 0;
        $branch_id = 0;
        $datalist = null;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $customer_id = $req_data['customer_id'];
            $user_id = $req_data['user_id'] == null ? 0 : $req_data['user_id'];
            //  $issue_id = $req_data['issue_id'];
            $route_id = $req_data['route_id'];
            $car_id = $req_data['car_id'];
            $payment_type_id = $req_data['payment_type_id'];
            $company_id = $req_data['company_id'];
            $branch_id = $req_data['branch_id'];
            $datalist = $req_data['data'];
        }


        $data = [];
        $is_free = 0;
        if ($payment_type_id == 3) {
            $is_free = 1;
        }
        if ($customer_id && $route_id && $car_id) {
            //  $sale_date = date('Y/m/d');
            $sale_date = date('Y/m/d');

            // $sale_time = date('H:i:s');
            $order_total_all = 0;
            //   $has_order = $this->hasOrder($sale_date, $route_id, $car_id);
            $has_order = null;
            $has_order = \common\models\Orders::find()->select(['id'])->where(['date(order_date)' => date('Y-m-d'), 'order_channel_id' => $route_id, 'car_ref_id' => $car_id, 'status' => 1])->one();
            if ($has_order != null) {
                $has_order_id = $has_order->id;
                if ($has_order_id) {
                    if (count($datalist) > 0) {
                        for ($i = 0; $i <= count($datalist) - 1; $i++) {
                            if ($datalist[$i]['qty'] <= 0) continue;

                            // $price_group_id = $this->findCustomerpricgroup($customer_id, $datalist[$i]['product_id'], $route_id);

                            $line_price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
                            $line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);

                            $model_line_trans = new \backend\models\Orderlinetrans();
                            $model_line_trans->order_id = $has_order_id;
                            $model_line_trans->customer_id = $customer_id;
                            $model_line_trans->product_id = $datalist[$i]['product_id'];
                            $model_line_trans->qty = $datalist[$i]['qty'];
                            $model_line_trans->price = $line_price;
                            $model_line_trans->line_total = $line_total;
                            $model_line_trans->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                            $model_line_trans->sale_payment_method_id = $payment_type_id;
                            $model_line_trans->issue_ref_id = $issue_id;
                            $model_line_trans->status = 1;
                            $model_line_trans->is_free = $is_free;
                            if ($model_line_trans->save(false)) {

                                $modelx = \common\models\OrderLine::find()->select(['qty'])->where(['product_id' => $datalist[$i]['product_id'], 'order_id' => $has_order_id, 'customer_id' => $customer_id])->one();
                                if ($modelx) {
                                    $modelx->qty = ($modelx->qty + $datalist[$i]['qty']);
                                    $modelx->line_total = $payment_type_id == 3 ? 0 : ($modelx->qty * $datalist[$i]['price']);
                                    $modelx->status = 1;
                                    $modelx->is_free = $is_free;
                                    if ($modelx->save(false)) {
                                        $status = true;
                                    }
                                } else {

                                    $model_line = new \backend\models\Orderline();
                                    $model_line->order_id = $has_order_id;
                                    $model_line->customer_id = $customer_id;
                                    $model_line->product_id = $datalist[$i]['product_id'];
                                    $model_line->qty = $datalist[$i]['qty'];
                                    $model_line->price = $line_price;
                                    $model_line->line_total = $line_total;
                                    $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                                    $model_line->sale_payment_method_id = $payment_type_id;
                                    $model_line->issue_ref_id = $issue_id;
                                    $model_line->status = 1;
                                    $model_line->is_free = $is_free;
                                    if ($model_line->save(false)) {

                                    }
                                }

                                if ($payment_type_id != 3) {
                                    $this->addpayment($has_order_id, $customer_id, $line_total, $company_id, $branch_id, $payment_type_id, $user_id);
                                }

                                //  $order_total_all += $model_line_trans->line_total;

                                // issue order stock
                                $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                if ($model_update_order_stock) {
                                    if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) {
                                        $model_update_order_stock->order_id = $has_order_id;
                                        $model_update_order_stock->avl_qty = ($model_update_order_stock->avl_qty - $datalist[$i]['qty']);
                                        $model_update_order_stock->save(false);
                                    } else {
                                        $remain_qty = ($datalist[$i]['qty'] - $model_update_order_stock->avl_qty);

                                        $model_update_order_stock->order_id = $has_order_id;
                                        $model_update_order_stock->avl_qty = 0;
                                        if ($model_update_order_stock->save(false)) {

                                            $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                            if ($model_update_order_stock2) {
                                                $model_update_order_stock2->order_id = $has_order_id;
                                                $model_update_order_stock2->avl_qty = ($model_update_order_stock2->avl_qty - $remain_qty);
                                                $model_update_order_stock2->save(false);
                                            }
                                        }
                                    }
                                }
                                // end issue order stock
                                $status = true;
                            }
                        }
                    }
                }
            } else {

                $emp_1 = 0;
                $emp_2 = 0;
                $empdaily = $this->findCarempdaily($car_id);
                if ($empdaily != null) {
                    $xx = 0;
                    foreach ($empdaily as $value_emp) {
                        if ($xx == 0) {
                            $emp_1 = $value_emp->employee_id;
                        } else {
                            $emp_2 = $value_emp->employee_id;
                        }
                        $xx += 1;
                    }
                }


//                $date = date('Y-m-d');
//                $prefix = '';
//                //   $model = Orders::find()->MAX('order_no');
//                //      $model = Orders::find()->where(['date(order_date)' => date('Y-m-d', strtotime($date))])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id,'sale_from_mobile'=>1])->andFilterWhere(['like','order_no','CO'])->MAX('order_no');
//                $modelx = Orders::find()->where(['date(order_date)' => date('Y-m-d')])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id,'sale_from_mobile'=>1])->andFilterWhere(['like','order_no','CO'])->MAX('order_no');
//
//                $pre = "CO";
//                if ($modelx != null) {
//                    $prefix = $pre . '-' . substr(date("Y"), 2, 2) . date('m', strtotime($date)) . date('d', strtotime($date)) . '-';
//                    $cnum = substr((string)$modelx, 10, strlen($modelx));
//                    $len = strlen($cnum);
//                    $clen = strlen($cnum + 1);
//                    $loop = $len - $clen;
//                    for ($i = 1; $i <= $loop; $i++) {
//                        $prefix .= "0";
//                    }
//                    $prefix .= $cnum + 1;
//                    return $prefix;
//                } else {
//                    $prefix = $pre . '-' . substr(date("Y"), 2, 2) . date('m', strtotime($date)) . date('d', strtotime($date)) . '-';
//                     $prefix . '0001';
//                }


                $model = new \backend\models\Ordermobile();
                // $model->order_no = $model->getLastNoMobile(date('Y-m-d'), 1, 2);
                $model->order_no = $model->getLastNoMobile($company_id, $branch_id);
                //  $model->order_no = $prefix;
                // $model->order_date = date('Y-m-d H:i:s', strtotime($sale_date . ' ' . $sale_time));
                $model->order_date = date('Y-m-d H:i:s');
                $model->customer_id = 0;
                $model->order_channel_id = $route_id; // สายส่ง
                $model->sale_channel_id = 1; //ช่องทาง
                $model->car_ref_id = $car_id;
                $model->issue_id = $issue_id;
                $model->status = 1;
                $model->created_by = $user_id;
                $model->company_id = $company_id;
                $model->branch_id = $branch_id;
                $model->sale_from_mobile = 1;
                $model->emp_1 = $emp_1;
                $model->emp_2 = $emp_2;
                $model->order_date2 = date('Y-m-d');

                if ($model->save(false)) {
                    // array_push($data, ['order_id' => $model->id]);
                    if (count($datalist) > 0) {
                        for ($i = 0; $i <= count($datalist) - 1; $i++) {
                            if ($datalist[$i]['qty'] <= 0) continue;
                            // $price_group_id = $this->findCustomerpricgroup($customer_id, $datalist[$i]['product_id'], $route_id);
                            // $line_total = ($datalist[$i]['qty'] * $datalist[$i]['price']);

                            $line_price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
                            $line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);


                            $model_line_trans = new \backend\models\Orderlinetrans();
                            $model_line_trans->order_id = $model->id;
                            $model_line_trans->customer_id = $customer_id;
                            $model_line_trans->product_id = $datalist[$i]['product_id'];
                            $model_line_trans->qty = $datalist[$i]['qty'];
                            $model_line_trans->price = $line_price;
                            $model_line_trans->line_total = $line_total;
                            $model_line_trans->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                            $model_line_trans->sale_payment_method_id = $payment_type_id;
                            $model_line_trans->issue_ref_id = $issue_id;
                            $model_line_trans->status = 1;
                            $model_line_trans->is_free = $is_free;

                            if ($model_line_trans->save(false)) {
                                $model_line = new \backend\models\Orderline();
                                $model_line->order_id = $model->id;
                                $model_line->customer_id = $customer_id;
                                $model_line->product_id = $datalist[$i]['product_id'];
                                $model_line->qty = $datalist[$i]['qty'];
                                $model_line->price = $line_price;
                                $model_line->line_total = $line_total;
                                $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                                $model_line->status = 1;
                                $model_line->sale_payment_method_id = $payment_type_id;
                                $model_line->issue_ref_id = $issue_id;
                                $model_line->is_free = $is_free;
                                if ($model_line->save(false)) {
                                }

                                if ($payment_type_id != 3) {
                                    $this->addpayment($model->id, $customer_id, $line_total, $company_id, $branch_id, $payment_type_id, $user_id);
                                }

                                $order_total_all += $model_line_trans->line_total;
                                $status = true;

                                // issue order stock
                                $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                //$model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id,  'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                if ($model_update_order_stock) {
                                    if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) {
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = ($model_update_order_stock->avl_qty - $datalist[$i]['qty']);
                                        $model_update_order_stock->save(false);
                                    } else {
                                        $remain_qty = ($datalist[$i]['qty'] - $model_update_order_stock->avl_qty);
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = 0;
                                        if ($model_update_order_stock->save(false)) {
                                            $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                            if ($model_update_order_stock2) {
                                                $model_update_order_stock2->order_id = $model->id;
                                                $model_update_order_stock2->avl_qty = ($model_update_order_stock2->avl_qty - $remain_qty);
                                                $model_update_order_stock2->save(false);
                                            }
                                        }
                                    }
                                }
                                // end issue order stock
                            }
                        }
                    }

                    $model->order_total_amt = $order_total_all;
                    $model->save(false);
//                    if ($model->issue_id > 0) {
//                        $model_issue = \backend\models\Journalissue::find()->where(['id' => $model->issue_id])->one();
//                        if ($model_issue) {
//                            $model_issue->status = 2;
//                            $model_issue->order_ref_id = $model->id;
//                            $model_issue->company_id = $company_id;
//                            $model_issue->branch_id = $branch_id;
//                            $model_issue->save(false);
//                        }
//                    }
                }
            }
        }
        //  array_push($data,['data'=>$req_data]);

        return ['status' => $status, 'data' => $data];
    }


    public function findLastNoMobile($company_id, $branch_id)
    {
        $date = date('Y-m-d');
        //   $model = Orders::find()->MAX('order_no');
        //      $model = Orders::find()->where(['date(order_date)' => date('Y-m-d', strtotime($date))])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id,'sale_from_mobile'=>1])->andFilterWhere(['like','order_no','CO'])->MAX('order_no');
        $model = Orders::find()->where(['date(order_date)' => date('Y-m-d')])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id, 'sale_from_mobile' => 1])->andFilterWhere(['like', 'order_no', 'CO'])->MAX('order_no');

        $pre = "CO";
        if ($model != null) {
//            $prefix = $pre.substr(date("Y"),2,2);
//            $cnum = substr((string)$model,4,strlen($model));
//            $len = strlen($cnum);
//            $clen = strlen($cnum + 1);
//            $loop = $len - $clen;
            $prefix = $pre . '-' . substr(date("Y"), 2, 2) . date('m', strtotime($date)) . date('d', strtotime($date)) . '-';
            $cnum = substr((string)$model, 10, strlen($model));
            $len = strlen($cnum);
            $clen = strlen($cnum + 1);
            $loop = $len - $clen;
            for ($i = 1; $i <= $loop; $i++) {
                $prefix .= "0";
            }
            $prefix .= $cnum + 1;
            return $prefix;
        } else {
            $prefix = $pre . '-' . substr(date("Y"), 2, 2) . date('m', strtotime($date)) . date('d', strtotime($date)) . '-';
            return $prefix . '0001';
        }
    }


//    public function addPayment($customer_id, $trans_date,$order_id,$pay_amount,$company_id,$branch_id)
//    {
//        $status = false;
//        $model = \common\models\PaymentReceive::find()->where(['date(trans_date)' => $trans_date, 'customer_id' => $customer_id])->one();
//       // return $model;
//        if ($model) {
//            //if(count($check_record) > 0){
//            $model_line = new \common\models\PaymentReceiveLine();
//            $model_line->payment_receive_id = $model->id;
//            $model_line->order_id = $order_id;
//            $model_line->payment_amount =$pay_amount;
//            $model_line->payment_channel_id = 1; // 1 เงินสด 2 โอน
//            $model_line->payment_method_id = 1; // 1 สด
//            $model_line->status = 1;
//            if ($model_line->save(false)) {
//                $status = true;
//            }
//            // }
//        } else {
//            $model = new \backend\models\Paymentreceive();
//            $model->trans_date = date('Y-m-d');//date('Y-m-d H:i:s');
//            $model->customer_id = $customer_id;
//            $model->journal_no = $model->getLastNo2(date('Y-m-d'), $company_id, $branch_id);
//            $model->status = 1;
//            $model->company_id = $company_id;
//            $model->branch_id = $branch_id;
//            if ($model->save()) {
//                $model_line = new \common\models\PaymentReceiveLine();
//                $model_line->payment_receive_id = $model->id;
//                $model_line->order_id = $order_id;
//                $model_line->payment_amount = $pay_amount;
//                $model_line->payment_channel_id = 1; // 1 เงินสด 2 โอน
//                $model_line->payment_method_id = 1; // 1 สด
//                $model_line->status = 1;
//                if ($model_line->save(false)) {
//                    $status = true;
//                }
//            }
//        }
//    }

//    public function actionAddorder()
//    {
//        $customer_id = null;
//        $product_id = null;
//        $qty = 0;
////        $price = 0;
////        $price_group_id = null;
//        $status = false;
//        $user_id = 0;
//        $issue_id = 0;
//        $route_id = 0;
//        $api_date = null;
//        $car_id = 0;
//        $company_id = 0;
//        $branch_id = 0;
//
//        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//        $req_data = \Yii::$app->request->getBodyParams();
//        if ($req_data != null) {
//            $api_date = $req_data['order_date'];
//            $customer_id = $req_data['customer_id'];
//            $product_id = $req_data['product_id'];
//            $qty = $req_data['qty'];
//            $price = $req_data['price']; // find by route_id
//            //  $price_group_id = $req_data['price_group_id'];
//            $user_id = $req_data['user_id'] == null ? 0 : $req_data['user_id'];
//            $issue_id = $req_data['issue_id'];
//            $route_id = $req_data['route_id'];
//            $car_id = $req_data['car_id'];
//            $payment_type_id = $req_data['payment_type_id'];
//            $company_id = $req_data['company_id'];
//            $branch_id = $req_data['branch_id'];
//
//        }
//
//        $data = [];
//        $is_free = 0;
//        if ($payment_type_id == 3) {
//            $is_free = 1;
//        }
//        if ($customer_id && $route_id && $car_id) {
//            //  $sale_date = date('Y/m/d');
//            $sale_date = date('Y/m/d');
//
//            $order_total_all = 0;
//            $has_order = $this->hasOrder($sale_date, $route_id, $car_id);
//            if ($has_order != null) {
//                $has_order_id = $has_order->id;
//                if ($has_order_id) {
//                    $this->registerissue($has_order_id, $issue_id, $company_id, $branch_id);
//                    //$price = $this->findCustomerprice($customer_id, $product_id, $route_id);
//
//                    $price_group_id = $this->findCustomerpricgroup($customer_id, $product_id, $route_id);
//
//                    $modelx = \common\models\OrderLine::find()->where(['product_id' => $product_id, 'order_id' => $has_order_id, 'customer_id' => $customer_id])->one();
//                    if ($modelx) {
//                        $modelx->qty = ($modelx->qty + $qty);
//                        $modelx->line_total = $payment_type_id == 3 ? 0 : ($modelx->qty * $price);
//                        $modelx->status = 1;
//                        $modelx->is_free = $is_free;
//                        if ($modelx->save(false)) {
//                            $status = true;
//                            $model_update_issue_line = \common\models\JournalIssueLine::find()->where(['issue_id' => $issue_id, 'product_id' => $product_id])->one();
//                            if ($model_update_issue_line) {
//                                $model_update_issue_line->avl_qty = ($model_update_issue_line->avl_qty - (int)$qty);
//                                $model_update_issue_line->save(false);
//                            }
//                        }
//                    } else {
//                        $model_line = new \backend\models\Orderline();
//                        $model_line->order_id = $has_order_id;
//                        $model_line->customer_id = $customer_id;
//                        $model_line->product_id = $product_id;
//                        $model_line->qty = $qty;
//                        $model_line->price = $payment_type_id == 3 ? 0 : $price;
//                        $model_line->line_total = $payment_type_id == 3 ? 0 : ($qty * $price);
//                        $model_line->price_group_id = $price_group_id;
//                        $model_line->sale_payment_method_id = $payment_type_id;
//                        $model_line->issue_ref_id = $issue_id;
//                        $model_line->status = 1;
//                        $model_line->is_free = $is_free;
//                        if ($model_line->save(false)) {
//
//                            //  if ($payment_type_id == 2) {
//                            if ($payment_type_id == 1) {
//                                $this->addpayment($has_order_id, $customer_id, ($qty * $price), $company_id, $branch_id, $payment_type_id);
//                            }
//
//                            //  }
//
//                            // $order_total_all += $model_line->line_total;
//                            $status = true;
//
//                            // issue order stock
//                            $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                            if ($model_update_order_stock) {
//                                if ($model_update_order_stock->avl_qty >= $qty) {
//                                    $model_update_order_stock->order_id = $has_order_id;
//                                    $model_update_order_stock->avl_qty = $model_update_order_stock->avl_qty - $qty;
//                                    $model_update_order_stock->save(false);
//                                } else {
//                                    $remain_qty = $qty - $model_update_order_stock->avl_qty;
//
//                                    $model_update_order_stock->order_id = $has_order_id;
//                                    $model_update_order_stock->avl_qty = 0;
//                                    if ($model_update_order_stock->save(false)) {
//
//                                        $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                                        if ($model_update_order_stock2) {
//                                            $model_update_order_stock2->order_id = $has_order_id;
//                                            $model_update_order_stock2->avl_qty = $model_update_order_stock2->avl_qty - $remain_qty;
//                                            $model_update_order_stock2->save(false);
//                                        }
//                                    }
//                                }
//                            }
//                            // end issue order stock
//
//                        }
//                    }
//
////                    $model->order_total_amt = $order_total_all;
////                    $model->save(false);
//                }
//            } else {
//                $model = new \backend\models\Ordermobile();
//                $model->order_no = $model->getLastNo($sale_date, $company_id, $branch_id);
//                // $model->order_date = date('Y-m-d H:i:s', strtotime($sale_date . ' ' . $sale_time));
//                $model->order_date = date('Y-m-d H:i:s');
//                $model->customer_id = 0;
//                $model->order_channel_id = $route_id; // สายส่ง
//                $model->sale_channel_id = 1; //ช่องทาง
//                $model->car_ref_id = $car_id;
//                $model->issue_id = $issue_id;
//                $model->status = 1;
//                $model->created_by = $user_id;
//                $model->company_id = $company_id;
//                $model->branch_id = $branch_id;
//                $model->sale_from_mobile = 1;
//                if ($model->save(false)) {
//                    array_push($data, ['order_id' => $model->id]);
//                    $this->registerissue($model->id, $issue_id, $company_id, $branch_id);
//                    //   $price = $this->findCustomerprice($customer_id, $product_id, $route_id);
//                    $price_group_id = $this->findCustomerpricgroup($customer_id, $product_id, $route_id);
//
//                    $model_line = new \backend\models\Orderline();
//                    $model_line->order_id = $model->id;
//                    $model_line->customer_id = $customer_id;
//                    $model_line->product_id = $product_id;
//                    $model_line->qty = $qty;
//                    $model_line->price = $payment_type_id == 3 ? 0 : $price;
//                    $model_line->line_total = $payment_type_id == 3 ? 0 : ($qty * $price);
//                    $model_line->price_group_id = $price_group_id;
//                    $model_line->status = 1;
//                    $model_line->sale_payment_method_id = $payment_type_id;
//                    $model_line->issue_ref_id = $issue_id;
//                    $model_line->is_free = $is_free;
//                    if ($model_line->save(false)) {
//
//                        //   if ($payment_type_id == 2) {
//
//                        if ($payment_type_id != 3) {
//                            $this->addpayment($model->id, $customer_id, ($qty * $price), $company_id, $branch_id, $payment_type_id);
//                        }
//                        //  }
//
//                        $order_total_all += $model_line->line_total;
//                        $status = true;
//
//                        // issue order stock
//                        $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                        if ($model_update_order_stock) {
//                            if ($model_update_order_stock->avl_qty >= $qty) {
//                                $model_update_order_stock->order_id = $model->id;
//                                $model_update_order_stock->avl_qty = $model_update_order_stock->avl_qty - $qty;
//                                $model_update_order_stock->save(false);
//                            } else {
//                                $remain_qty = $qty - $model_update_order_stock->avl_qty;
//                                $model_update_order_stock->order_id = $model->id;
//                                $model_update_order_stock->avl_qty = 0;
//                                if ($model_update_order_stock->save(false)) {
//
//                                    $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                                    if ($model_update_order_stock2) {
//                                        $model_update_order_stock2->order_id = $model->id;
//                                        $model_update_order_stock2->avl_qty = $model_update_order_stock2->avl_qty - $remain_qty;
//                                        $model_update_order_stock2->save(false);
//                                    }
//                                }
//                            }
//                        }
//                        // end issue order stock
//
//                    }
//                    $model->order_total_amt = $order_total_all;
//                    $model->save(false);
//                    if ($model->issue_id > 0) {
//                        $model_issue = \backend\models\Journalissue::find()->where(['id' => $model->issue_id])->one();
//                        if ($model_issue) {
//                            $model_issue->status = 2;
//                            $model_issue->order_ref_id = $model->id;
//                            $model_issue->company_id = $company_id;
//                            $model_issue->branch_id = $branch_id;
//                            $model_issue->save(false);
//                        }
//
//                    }
//                }
//            }
//        }
//        //  array_push($data,['data'=>$req_data]);
//
//        return ['status' => $status, 'data' => $data];
//    }

    public function hasOrder($order_date, $route_id, $car_id)
    {
        $order_date = date('Y-m-d');
        $res = null;
        if ($route_id && $car_id) {
            $model = \common\models\Orders::find()->select(['id'])->where(['date(order_date)' => $order_date, 'order_channel_id' => $route_id, 'car_ref_id' => $car_id, 'status' => 1])->one();
            $res = $model;
        }
        return $res;
    }

    public function hasOrderCustomer($order_date, $route_id, $customer_id)
    {
        //$order_date = date('Y-m-d');
        $res = null;
        if ($route_id && $customer_id) {
            $model = \common\models\QuerySaleByCustomer::find()->select('id')->where(['date(order_date)' => date('Y-m-d', strtotime($order_date)), 'customer_id' => $customer_id])->one();
            $res = $model;
        }
        return $res;
    }

//    public function checkorderopen($route_id,$order_date){
//        if($route_id){
//            $model = \common\models\Orders::find()->where(['delivery_route_id'=>$route_id,'date(order_date)'=>$order_date,'status'=>1])->count();
//        }
//    }
//    public function checkissueorder($route_id,$order_date){
//        if($route_id){
//            $model = \common\models\OrderStock::find()->where(['route_id'=>$route_id,'date(trans_date)'=>$order_date])->count();
//        }
//    }

    public function findCustomerprice($customer_id, $product_id, $route_id)
    {
        $price = 0;
        if ($product_id && $route_id) {
            $model = \common\models\QueryCustomerPrice::find()->where(['cus_id' => $customer_id, 'product_id' => $product_id, 'delivery_route_id' => $route_id])->one();
            if ($model) {
                $price = $model->sale_price == null ? 0 : $model->sale_price;
            }
        }
        return $price;
    }

    public function findCustomerpricgroup($customer_id, $product_id, $route_id)
    {
        $group_id = 0;
        if ($product_id && $route_id) {
            $model = \common\models\QueryCustomerPrice::find()->where(['cus_id' => $customer_id, 'product_id' => $product_id, 'delivery_route_id' => $route_id])->one();
            if ($model) {
                $group_id = $model->id == null ? 0 : $model->id;
            }
        }
        return $group_id;
    }

    public function addpayment($order_id, $customer_id, $amount, $company_id, $branch_id, $payment_type_id, $user_id)
    {
        $status = false;
        $pay_amt = 0;
        if ($payment_type_id == 1) {
            $pay_amt = $amount;
        }
        $model = \common\models\PaymentReceive::find()->select(['id'])->where(['date(trans_date)' => date('Y-m-d'), 'customer_id' => $customer_id])->one();
        // return $model;
        if ($model) {
            //if(count($check_record) > 0){
            $model_line = new \common\models\PaymentReceiveLine();
            $model_line->payment_receive_id = $model->id;
            $model_line->order_id = $order_id;
            $model_line->payment_amount = $pay_amt;
            $model_line->payment_channel_id = 1; // 1 เงินสด 2 โอน
            $model_line->payment_method_id = $payment_type_id; // 1 สด 2 เชื่อ
            $model_line->status = 1;

            if ($model_line->save(false)) {
                $status = true;
            }
            // }
        } else {
            $model = new \backend\models\Paymentreceive();
            $model->trans_date = date('Y-m-d');//date('Y-m-d H:i:s');
            $model->customer_id = $customer_id;
            $model->journal_no = $model->getLastNo2(date('Y-m-d'), $company_id, $branch_id);
            $model->status = 1;
            $model->company_id = $company_id;
            $model->branch_id = $branch_id;
            $model->crated_by = $user_id;
            if ($model->save()) {
                $model_line = new \common\models\PaymentReceiveLine();
                $model_line->payment_receive_id = $model->id;
                $model_line->order_id = $order_id;
                $model_line->payment_amount = $pay_amt;
                $model_line->payment_channel_id = 1; // 1 เงินสด 2 โอน
                $model_line->payment_method_id = $payment_type_id; // 1 สด
                $model_line->status = 1;
                if ($model_line->save(false)) {
                    $status = true;
                }
            }
        }

//        $model = new \backend\models\Paymenttrans();
//        $model->trans_no = $model->getLastNo($company_id, $branch_id);
//        $model->trans_date = date('Y-m-d H:i:s');
//        $model->order_id = $order_id;
//        $model->status = 0;
//        $model->company_id = $company_id;
//        $model->branch_id = $branch_id;
//        if ($model->save(false)) {
//            if ($customer_id != null) {
//
//                $model_line = new \backend\models\Paymenttransline();
//                $model_line->trans_id = $model->id;
//                $model_line->customer_id = $customer_id;
//                $model_line->payment_method_id = 8;
//                $model_line->payment_term_id = 0;
//                $model_line->payment_date = date('Y-m-d H:i:s');
//                $model_line->payment_amount = $payment_type_id == 1 ? $amount : 0;
//                $model_line->total_amount = 0;
//                $model_line->order_ref_id = $order_id;
//                $model_line->payment_type_id = $payment_type_id;
//                $model_line->status = 1;
//                $model_line->doc = '';
//                if ($model_line->save(false)) {
//
//                }
//
//            }
//        }
    }

    public function actionList()
    {
        $status = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $car_id = $req_data['car_id'];
        $api_date = $req_data['order_date'];

        $data = [];
        if ($car_id) {

            $sale_date = date('Y-m-d');
            $t_date = null;
            $exp_order_date = explode(' ', $api_date);
            if ($exp_order_date != null) {
                if (count($exp_order_date) > 1) {
                    $x_date = explode('-', $exp_order_date[0]);
                    if (count($x_date) > 1) {
                        $t_date = $x_date[0] . "/" . $x_date[1] . "/" . $x_date[2];
                    }
                }
            }
            if ($t_date != null) {
                $sale_date = $t_date;
            }

            $model = \common\models\QueryApiOrderDailySummary::find()->where(['car_ref_id' => $car_id, 'date(order_date)' => $sale_date])->all();
            // $model = \common\models\Orders::find()->where(['id'=>131])->all();
            //  $model = \common\models\Orders::find()->where(['car_ref_id' => $car_id])->all();
            if ($model) {
                $status = true;
                foreach ($model as $value) {
                    array_push($data, [
                        'id' => $value->id,
                        'order_no' => $value->order_no,
                        'order_date' => $value->order_date,
                        'order_status' => $value->status,
                        'customer_id' => $value->customer_id,
                        'customer_code' => $value->code,
                        'customer_name' => $value->name,
                        'note' => '',
                        'payment_method' => $value->payment_method_name,
                        'payment_method_id' => $value->pay_type,
                        'sale_payment_method_id' => $value->sale_payment_method_id,
                        'total_amount' => $value->line_total == null ? 0 : $value->line_total,
                        'total_qty' => $value->line_qty == null ? 0 : $value->line_qty,
                    ]);
                }
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function actionListnew()
    {
        $status = false;
        $searchcustomer = '';
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $car_id = $req_data['car_id'];
        $api_date = $req_data['order_date'];
        $searchcustomer = $req_data['searchcustomer'];

        $data = [];
        if ($car_id) {

            $sale_date = date('Y-m-d');
            $t_date = null;
            $exp_order_date = explode(' ', $api_date);
            if ($exp_order_date != null) {
                if (count($exp_order_date) > 1) {
                    $x_date = explode('-', $exp_order_date[0]);
                    if (count($x_date) > 1) {
                        $t_date = $x_date[0] . "/" . $x_date[1] . "/" . $x_date[2];
                    }
                }
            }
            if ($t_date != null) {
                $sale_date = $t_date;
            }
            $model = null;
            if ($searchcustomer != '') {
                $model = \common\models\QueryApiOrderDailySummaryNew::find()->where(['car_ref_id' => $car_id, 'date(order_date)' => $sale_date, 'status' => 1, 'customer_id' => $searchcustomer])->all();
            } else {
                $model = \common\models\QueryApiOrderDailySummaryNew::find()->where(['car_ref_id' => $car_id, 'date(order_date)' => $sale_date, 'status' => 1])->all();
            }

            // $model = \common\models\Orders::find()->where(['id'=>131])->all();
            //  $model = \common\models\Orders::find()->where(['car_ref_id' => $car_id])->all();
            if ($model) {
                $status = true;
                foreach ($model as $value) {
                    array_push($data, [
                        'id' => $value->id,
                        'order_no' => $value->order_no,
                        'order_date' => $value->order_date,
                        'order_status' => $value->status,
                        'customer_id' => $value->customer_id,
                        'customer_code' => $value->code,
                        'customer_name' => $value->name,
                        'note' => '',
                        'sale_payment_method_id' => $value->sale_payment_method_id,
                        'line_total' => $value->line_total == null ? 0 : $value->line_total,
                        'qty' => $value->line_qty == null ? 0 : $value->line_qty,
                        'price' => $value->price == null ? 0 : $value->price,
                        'order_line_id' => $value->order_line_id,
                        'product_id' => $value->product_id,
                        'product_code' => $value->product_code,
                        'product_name' => $value->product_name,
                        'order_line_date' => date('d-m-Y H:i:s', $value->created_at),
                        'order_line_status' => $value->order_line_status,

                    ]);
                }
            }
        }
        return ['status' => $status, 'data' => $data];
    }

//    public function findDiscount($order_id){
//        $dis_amount = 0;
//        if($order_id){
//            $model = \common\models\Orders::find()->select('discount_amt')->where(['id'=>$order_id])->one();
//            if($model){
//                $dis_amount = $model->discount_amt;
//            }
//        }
//
//        return $dis_amount;
//
//    }
    public function actionAddordertransfer()
    {
        $customer_id = null;
        $product_id = null;
        $qty = 0;
//        $price = 0;
//        $price_group_id = null;
        $status = false;
        $user_id = 0;
        $transfer_id = 0;
        $route_id = 0;
        $api_date = null;
        $car_id = 0;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $api_date = $req_data['order_date'];
            $customer_id = $req_data['customer_id'];
            $product_id = $req_data['product_id'];
            $qty = $req_data['qty'];
            $price = $req_data['price']; // find by route_id
            //  $price_group_id = $req_data['price_group_id'];
            $user_id = $req_data['user_id'] == null ? 0 : $req_data['user_id'];
            $issue_id = $req_data['issue_id'];
            $route_id = $req_data['route_id'];
            $car_id = $req_data['car_id'];
            $transfer_id = $req_data['transfer_id'];
        }

        $data = [];
        if ($customer_id && $route_id && $car_id) {
            //  $sale_date = date('Y/m/d');
            $sale_date = date('Y/m/d');
//            $t_date = null;
//            $exp_order_date = explode(' ', $api_date);
//            if ($exp_order_date != null) {
//                if (count($exp_order_date) > 1) {
//                    $x_date = explode('-', $exp_order_date[0]);
//                    if (count($x_date) > 1) {
//                        $t_date = $x_date[0] . "/" . $x_date[1] . "/" . $x_date[2];
//                    }
//                }
//            }
//            if ($t_date != null) {
//                $sale_date = $t_date;
//            }
            // $sale_time = date('H:i:s');

            $order_total_all = 0;

            $has_order = $this->hasOrder($sale_date, $route_id, $car_id);
            if ($has_order != null) {
                $has_order_id = $has_order->id;
                if ($has_order_id) {
                    //$price = $this->findCustomerprice($customer_id, $product_id, $route_id);

                    $price_group_id = $this->findCustomerpricgroup($customer_id, $product_id, $route_id);

                    $modelx = \common\models\OrderLine::find()->select(['qty', 'line_total', 'status'])->where(['product_id' => $product_id, 'order_id' => $has_order_id, 'customer_id' => $customer_id])->one();
                    if ($modelx) {
                        $modelx->qty = ($modelx->qty + $qty);
                        $modelx->line_total = ($modelx->qty * $price);
                        $modelx->status = 1;
                        if ($modelx->save(false)) {
                            $status = true;
                        }
                    } else {
                        $model_line = new \backend\models\Orderline();
                        $model_line->order_id = $has_order_id;
                        $model_line->customer_id = $customer_id;
                        $model_line->product_id = $product_id;
                        $model_line->qty = $qty;
                        $model_line->price = $price;
                        $model_line->line_total = ($qty * $price);
                        $model_line->price_group_id = $price_group_id;
                        $model_line->status = 1;
                        if ($model_line->save(false)) {
                            $order_total_all += $model_line->line_total;
                            $status = true;

                            $model_update_transfer_line = \common\models\TransferLine::find()->where(['transfer_id' => $transfer_id, 'product_id' => $product_id])->one();
                            if ($model_update_transfer_line) {
                                $model_update_transfer_line->avl_qty = $model_update_transfer_line->avl_qty - $qty;
                                $model_update_transfer_line->save(false);
                            }
                        }
                    }


//                    $model->order_total_amt = $order_total_all;
//                    $model->save(false);
                }
            } else {
                $model = new \backend\models\Orders();
                $model->order_no = $model->getLastNo($sale_date);
                // $model->order_date = date('Y-m-d H:i:s', strtotime($sale_date . ' ' . $sale_time));
                $model->order_date = date('Y-m-d H:i:s');
                $model->customer_id = 0;
                $model->order_channel_id = $route_id; // สายส่ง
                $model->sale_channel_id = 1; //ช่องทาง
                $model->car_ref_id = $car_id;
                $model->issue_id = $issue_id;
                $model->status = 1;
                $model->created_by = $user_id;
                if ($model->save(false)) {
                    //   $price = $this->findCustomerprice($customer_id, $product_id, $route_id);
                    $price_group_id = $this->findCustomerpricgroup($customer_id, $product_id, $route_id);
                    $model_line = new \backend\models\Orderline();
                    $model_line->order_id = $model->id;
                    $model_line->customer_id = $customer_id;
                    $model_line->product_id = $product_id;
                    $model_line->qty = $qty;
                    $model_line->price = $price;
                    $model_line->line_total = ($qty * $price);
                    $model_line->price_group_id = $price_group_id;
                    $model_line->status = 1;
                    if ($model_line->save(false)) {
                        $order_total_all += $model_line->line_total;
                        $status = true;

                        $model_update_transfer_line = \common\models\TransferLine::find()->where(['transfer_id' => $transfer_id, 'product_id' => $product_id])->one();
                        if ($model_update_transfer_line) {
                            $model_update_transfer_line->avl_qty = $model_update_transfer_line->avl_qty - $qty;
                            $model_update_transfer_line->save(false);
                        }
                    }
                    $model->order_total_amt = $order_total_all;
                    $model->save(false);
                }
            }
        }
        //  array_push($data,['data'=>$req_data]);

        return ['status' => $status, 'data' => $data];
    }

    public function actionListbycustomer()
    {
        $status = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $customer_id = $req_data['customer_id'];
        $order_id = $req_data['order_id'];

        $data = [];
        if ($customer_id) {
            //$model = \common\models\QueryApiOrderDaily::find()->where(['customer_id' => $customer_id])->andFilterWhere(['id' => $order_id])->andFilterWhere(['>', 'qty', 0])->all();
            $model = \common\models\QueryApiOrderDailySummaryNew::find()->where(['customer_id' => $customer_id])->andFilterWhere(['id' => $order_id])->andFilterWhere(['>', 'line_qty', 0])->all();
            if ($model) {
                $status = true;
                foreach ($model as $value) {
                    array_push($data, [
                        'order_id' => $value->id,
                        'order_no' => $value->order_no,
                        'order_date' => $value->order_date,
                        'order_status' => $value->status,
                        'line_id' => $value->order_line_id,
                        'customer_id' => $value->customer_id,
                        'customer_name' => $value->name,
                        'customer_code' => $value->code,
                        'product_id' => $value->product_id,
                        'product_code' => $value->product_code,
                        'product_name' => $value->product_name,
                        'qty' => $value->line_qty,
                        'price' => $value->price,
                        'price_group_id' => '',
                        'order_line_status' => \backend\models\Orderlinetrans::findStatus($value->order_line_id),
                        'order_discount_amt' => $value->discount_amt == null ? 0 : $value->discount_amt,
                    ]);

                }
            }
        }

        return ['status' => $status, 'data' => $data];
    }

    public function getSumbycust($order_id)
    {
        $data = [];
        if ($order_id) {
            $model = \backend\models\Orderline::find()->select(['customer_id', 'sum(line_total) as line_total'])->where(['order_id' => $order_id])->groupBy('customer_id')->all();
            if ($model) {
                foreach ($model as $value) {
                    array_push($data, ['customer_id' => $value->customer_id, 'line_total' => $value->line_total]);
                }
            }
        }
        return $data;
    }

    public function actionDeleteorderline()
    {
        $status = false;
        $id = null;


        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $id = $req_data['id'];

        $data = [];
        if ($id) {
            $model_data = \backend\models\Orderline::find()->where(['id' => $id])->one();
            if ($model_data) {
                // $model_return_issue = \backend\models\Journalissueline::find()->where(['product_id' => $model_data->product_id, 'issue_id' => $model_data->issue_ref_id])->andFilterWhere(['>', 'qty', 0])->one();
                $model_return_issue = \common\models\OrderStock::find()->where(['product_id' => $model_data->product_id, 'order_id' => $model_data->order_id])->one();

                if ($model_return_issue) {
                    $model_return_issue->avl_qty = (int)$model_return_issue->avl_qty + (int)$model_data->qty;
                    if ($model_return_issue->save(false)) {
                        if (\common\models\OrderLine::deleteAll(['id' => $id])) {
                            $status = true;
                        }
                    }
                }
            }

        }

        return ['status' => $status, 'data' => $data];
    }

    public function actionDeleteordercustomer()
    {
        $status = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $order_id = $req_data['order_id'];
        $customer_id = $req_data['customer_id'];

        $data = [];
        if ($order_id != null && $customer_id != null) {
            if (\common\models\OrderLine::updateAll(['qty' => 0, 'price' => 0, 'line_total' => 0], ['order_id' => $order_id, 'customer_id' => $customer_id])) {
                $status = true;
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function actionDeleteorder()
    {
        $status = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $id = $req_data['order_id'];

        $data = [];
        if (!$id) {
            if (\common\models\Order::deleteAll(['id' => $id])) {
                $status = true;
            }
        }

        return ['status' => $status, 'data' => $data];
    }

    public function actionCustomercredit()
    {
        $status = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $id = $req_data['orderline_id'];

        $data = [];
        if ($cus_id) {
            $model = \common\models\QuerySalePaySummary::find()->where(['customer_id' => $cus_id])->andFilterWhere(['>', 'remain_amount', 0])->all();
            if ($model) {
//                $html = $cus_id;
                $i = 0;
                foreach ($model as $value) {
                    $i += 1;
                    //   $total_amount = $total_amount + ($value->remain_amount == null ? 0 : $value->remain_amount);
                    $html .= '<tr>';
                    $html .= '<td style="text-align: center">' . $i . '</td>';
                    $html .= '<td style="text-align: center">' . \backend\models\Orders::getNumber($value->order_id) . '</td>';
                    $html .= '<td style="text-align: center">' . date('d/m/Y', strtotime($value->order_date)) . '</td>';
                    $html .= '<td>
                            <select name="line_pay_type[]" id=""  class="form-control" onchange="checkpaytype($(this))">
                                <option value="0">เงินสด</option>
                                <option value="1">โอนธนาคาร</option>
                            </select>
                            <input type="file" class="line-doc" name="line_doc[]" style="display: none">
                            <input type="hidden" class="line-order-id" name="line_order_id[]" value="' . $value->order_id . '">
                            <input type="hidden" class="line-number" name="line_number[]" value="' . ($i - 1) . '">
                    </td>';
//                    $html .= '<td style="text-align: center"><input type="file" class="form-control"></td>';
                    $html .= '<td>
                            <input type="text" class="form-control line-remain" style="text-align: right" name="line_remain[]" value="' . number_format($value->remain_amount, 2) . '" readonly>
                            <input type="hidden" class="line-remain-qty" value="' . $value->remain_amount . '">
                            </td>';
                    $html .= '<td><input type="number" class="form-control line-pay" name="line_pay[]" value="" onchange="linepaychange($(this))"></td>';
                    $html .= '</tr>';

                }
                // $html .= '<tr><td colspan="4" style="text-align: right">รวม</td><td style="text-align: right;font-weight: bold">' . number_format($total_amount, 2) . '</td><td style="text-align: right;font-weight: bold"><span class="line-pay-total">0</span></td></tr>';
            }
        }

        echo $html;
    }

    public function registerissue($order_id, $issue_id, $company_id, $branch_id)
    {

//        $order_id = \Yii::$app->request->post('order_id');
//        $issuelist = \Yii::$app->request->post('issue_list');
        $default_wh = 6;
        if ($company_id == 1 && $branch_id == 2) {
            $default_wh = 5;
        }

        if ($order_id != null && $issue_id != null) {
            //  $issue_data = explode(',', $issuelist);
//            print_r($issuelist[0]);

            $model_check_has_issue = \common\models\OrderStock::find()->where(['order_id' => $order_id, 'issue_id' => $issue_id])->count();
            if ($model_check_has_issue > 0) {

            } else {
//                $model_order= \backend\models\Orders::find()->where(['id'=>$order_id])->one();
//                if($model_order){
//                    $model_order->
//                }
                $model_issue_line = \backend\models\Journalissueline::find()->where(['issue_id' => $issue_id])->all();
                foreach ($model_issue_line as $val2) {
                    if ($val2->qty <= 0 || $val2->qty == null) continue;
                    $model_order_stock = new \common\models\OrderStock();
                    $model_order_stock->issue_id = $issue_id;
                    $model_order_stock->product_id = $val2->product_id;
                    $model_order_stock->qty = $val2->qty;
                    $model_order_stock->used_qty = 0;
                    $model_order_stock->avl_qty = $val2->qty;
                    $model_order_stock->order_id = $order_id;
                    if ($model_order_stock->save(false)) {
                        $model_update_issue_status = \common\models\JournalIssue::find()->where(['id' => $issue_id])->one();
                        if ($model_update_issue_status) {
                            $model_update_issue_status->status = 2;
                            $model_update_issue_status->save(false);
                        }
                        $this->updateStock($val2->product_id, $val2->qty, $default_wh, '');
                    }
                }
            }
        }
    }

    public function updateStock($product_id, $qty, $wh_id, $journal_no)
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
            if ($model_trans->save(false)) {
                $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $wh_id, 'product_id' => $product_id])->one();
                if ($model) {
                    $model->qty = $model->qty - (int)$qty;
                    $model->save(false);
                }
            }
        }
    }

    public function actionCloseorder2()
    {
        $status = 0;

        $company_id = 1;
        $branch_id = 1;
        $route_id = null;
        $user_id = null;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();

        $route_id = $req_data['route_id'];
        $user_id = $req_data['user_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];

        $default_wh = 0;
        $reprocess_wh = 0;

        $data = [];
        $res = 0;

        if ($route_id != null && $company_id != null && $branch_id != null) {
            $reprocess_wh = $this->findReprocesswh($company_id, $branch_id);
            $status = 1;
            $default_wh = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id); // for boot sale return stock
            $model_close = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->all();
            if ($model_close) {

                $x = 0;
                foreach ($model_close as $value) {
                    if ($value->avl_qty <= 0 || $value->avl_qty == null) {
                        $x += 1;
                        continue;
                    }
                    $res += 1;
                    $check_is_close = \backend\models\Stocktrans::find()->where(['trans_ref_id' => $route_id, 'activity_type_id' => 7, 'product_id' => $value->product_id])->one(); // check already close order
                    //$check_is_close = \backend\models\Stocktrans::find()->where(['trans_ref_id'=>$value->order_id,'activity_type_id'=>7,'product_id'=>$value->product_id])->one(); // check already close order
                    if ($check_is_close) continue;

                    $model = new \backend\models\Stocktrans();
                    $model->journal_no = $model->getReturnNo($company_id, $branch_id);
                    $model->trans_date = date('Y-m-d H:i:s');
                    $model->product_id = $value->product_id;
                    $model->qty = $value->avl_qty;
                    $model->warehouse_id = $reprocess_wh;
                    $model->stock_type = 1;
                    $model->activity_type_id = 7;//7; // 1 prod rec 2 issue car
                    $model->company_id = $company_id;
                    $model->branch_id = $branch_id;
                    $model->created_by = $user_id;
                    $model->trans_ref_id = $route_id;
                    if ($model->save(false)) {
                        $check_route_type = \backend\models\Deliveryroute::find()->where(['id' => $route_id])->one();
                        if ($check_route_type->type_id == 1) { // check general route not boot
                            //   $this->updateSummary($value->product_id, $reprocess_wh, $value->avl_qty, $company_id, $branch_id);
                        } else if ($check_route_type->type_id == 2) { // is boots route
                            // $this->updatebootSummary2($value->product_id, $default_wh, $route_id, $value->avl_qty, $company_id, $branch_id, $user_id);
                        }
                        $res += 1;
                        $data = ['stock' => 'ok', 'route_id' => $route_id];
                    }


                }
                // if ($res > 0 || $x == count($model_close)) {
                if ($res > 0) {
                    if (\common\models\Orders::updateAll(['status' => 100], ['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d')])) {
                        $status = 1;
                    }
//                    $model_update = \backend\models\Orders::find()->where(['order' => $order_id])->one();
//                    if ($model_update) {
//                        $model_update->status = 100;
//                        if ($model_update->save(false)) {
//
//                        }
//                        $status = 1;
//                    }
                }
            }

        }

        return ['status' => $status, 'data' => $data];
    }

    public function actionCloseorder()
    {
        $status = 0;

        $company_id = 0;
        $branch_id = 0;
        $order_id = null;
        $route_id = 0;
        $user_id = 0;
        $is_return_stock = null;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();

        $route_id = $req_data['route_id'];
        $user_id = $req_data['user_id'];
        $order_id = $req_data['order_id'];
        $company_id = $req_data['company_id'];
        $branch_id = $req_data['branch_id'];
        $is_return_stock = $req_data['return_stock'];

        $default_wh = 0;
        $reprocess_wh = 0;
        $dvl_route_type = 0;

        $data = [];
        $res = 0;

        //  $route_id = 0;
        if ($route_id && $company_id != null && $branch_id != null) {

            $reprocess_wh = $this->findReprocesswh($company_id, $branch_id);

            $default_wh = \backend\models\Warehouse::findWarehousecar($company_id, $branch_id); // for boot sale return stock
            $check_route_type = \backend\models\Deliveryroute::find()->select('type_id')->where(['id' => $route_id])->one();
            $dvl_route_type = $check_route_type->type_id;
            $stock_data = null;


            $sql = "select route_id,product_id,sum(avl_qty) AS avl_qty";
            $sql .= " FROM order_stock";
            $sql .= " WHERE  route_id =" . $route_id;
            $sql .= " AND date(trans_date) =" . "'" . date('Y-m-d') . "'" . " ";
            $sql .= " GROUP BY route_id, product_id ";

            $sql_query = \Yii::$app->db->createCommand($sql);
            $stock_data = $sql_query->queryAll();

            $order_shift = 0;
            // $order_shift = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->groupBy(['route_id'])->count('issue_id');

            if ($stock_data != null) {

                if ($is_return_stock == '1') {
                    $x = 0;
                    for ($i = 0; $i <= count($stock_data) - 1; $i++) {
                        if ($stock_data[$i]['avl_qty'] <= 0 || $stock_data[$i]['avl_qty'] == null) {
                            $x += 1;
                            continue;
                        }


                        //$check_is_close = \backend\models\Stocktrans::find()->where(['trans_ref_id'=>$value->order_id,'activity_type_id'=>7,'product_id'=>$value->product_id])->one(); // check already close order

                        // $check_is_close = \backend\models\Stocktrans::find()->where(['trans_ref_id' => $route_id, 'activity_type_id' => 7, 'product_id' => $stock_data[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->one(); // check already close order
                        // $check_is_close = \backend\models\Stocktrans::find()->where(['trans_ref_id' => $route_id, 'activity_type_id' => 7, 'product_id' => $stock_data[$i]['product_id'], 'date(trans_date)' => date('Y-m-d'), 'created_by' => $user_id])->one(); // check already close order
                        //if ($check_is_close) continue;
                        //  $status = 101;

                        $model = new \backend\models\Stocktrans();
                        $model->journal_no = $model->getReturnNo($company_id, $branch_id);
                        $model->trans_date = date('Y-m-d H:i:s');
                        $model->product_id = $stock_data[$i]['product_id'];
                        $model->qty = $stock_data[$i]['avl_qty'];
                        $model->warehouse_id = $reprocess_wh;
                        $model->stock_type = 1;
                        $model->activity_type_id = 7;//7; // 1 prod rec 2 issue car
                        $model->company_id = $company_id;
                        $model->branch_id = $branch_id;
                        $model->created_by = $user_id;
                        $model->trans_ref_id = $route_id;
                        if ($model->save(false)) {
                            if ($dvl_route_type == 1) { // check general route not boot
                                $this->updateSummary($stock_data[$i]['product_id'], $reprocess_wh, $stock_data[$i]['avl_qty'], $company_id, $branch_id);

                            } else if ($dvl_route_type == 2) { // is boots route
                                $this->updatebootSummary2($stock_data[$i]['product_id'], $default_wh, $route_id, $stock_data[$i]['avl_qty'], $company_id, $branch_id, $user_id);
                            }
                            $res += 1;
                            $data = ['stock' => 'ok', 'order_id' => 1];
                        }
                    }
                    if ($res > 0 || $x == count($stock_data)) {

//                        $emp_1 = 0;
//                        $emp_2 = 0;
//                        $car_id = null;
//
//                        $car_id = \common\models\QueryCarRoute::find()->where(['delivery_route_id' => $route_id])->one();
//                        if ($car_id) {
//                            $empdaily = $this->findCarempdaily($car_id->id);
//                            if ($empdaily != null) {
//                                $xx = 0;
//                                foreach ($empdaily as $value_emp) {
//                                    if ($xx == 0) {
//                                        $emp_1 = $value_emp->employee_id;
//                                    } else {
//                                        $emp_2 = $value_emp->employee_id;
//                                    }
//                                    $xx += 1;
//                                }
//                            }
//
//                        }


                        if (\common\models\Orders::updateAll(
                            ['status' => 100, 'order_shift' => $order_shift],
                            ['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'sale_from_mobile' => 1])) {

                            \common\models\OrderStock::updateAll(['avl_qty' => 0], ['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')]);

                            $status = 1;
                        }
//                        \common\models\OrderStock::updateAll(['avl_qty' => 0], ['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')]);
//
//                        $status = 1;
                    }

                } else { // not return stock

                    $x = 0;
                    if ($stock_data != null) {
                        for ($i = 0; $i <= count($stock_data) - 1; $i++) {
                            if ($stock_data[$i]['avl_qty'] <= 0 || $stock_data[$i]['avl_qty'] == null) {
                                $x += 1;
                                continue;
                            }

                            $this->updatebootSummary2($stock_data[$i]['product_id'], $default_wh, $route_id, $stock_data[$i]['avl_qty'], $company_id, $branch_id, $user_id);
                            $res += 1;
                            $data = ['stock' => 'ok', 'order_id' => 1];

                        }
                    }
                    if ($res > 0 || $x == count($stock_data)) {

                        $emp_1 = 0;
                        $emp_2 = 0;
                        $car_id = null;

//                        $car_id = \common\models\QueryCarRoute::find()->select(['id'])->where(['delivery_route_id' => $route_id])->one();
//                        if ($car_id) {
//                            $empdaily = $this->findCarempdaily($car_id->id);
//                            if ($empdaily != null) {
//                                $xx = 0;
//                                foreach ($empdaily as $value_emp) {
//                                    if ($xx == 0) {
//                                        $emp_1 = $value_emp->employee_id;
//                                    } else {
//                                        $emp_2 = $value_emp->employee_id;
//                                    }
//                                    $xx += 1;
//                                }
//                            }
//
//                        }
//                            ['status' => 100, 'emp_1' => $emp_1, 'emp_2' => $emp_2, 'order_shift' => $order_shift],

                        if (\common\models\Orders::updateAll(
                            ['status' => 100, 'order_shift' => $order_shift],
                            ['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'sale_from_mobile' => 1])) {

                            //  \common\models\OrderStock::updateAll(['avl_qty'=>0],['route_id'=>$route_id,'date(trans_date)'=>date('Y-m-d')]);

                            $status = 1;
                            // $this->notifymessageorderclose($route_id, $user_id, $company_id, $branch_id);
                        }
                    }

                }

            } else {
                $status = $sql;
            }

            // if($this->comdailycal($route_id)){
            $this->notifymessageorderclose($route_id, $user_id, $company_id, $branch_id);
            // }

        } else {
            $status = 1000;
        }

        return ['status' => $status, 'data' => $data];
    }

    public function updateSummary($product_id, $wh_id, $qty, $company_id, $branch_id)
    {
        if ($wh_id != null && $product_id != null && $qty > 0) {
            $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $wh_id, 'product_id' => $product_id, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();
            if ($model) {
                if ($model->qty < 0) {
                    $model->qty = (double)$qty;
                    $model->save(false);
                } else {
                    $model->qty = ($model->qty + (int)$qty);
                    $model->save(false);
                }

            } else {
                $model_new = new \backend\models\Stocksum();
                $model_new->warehouse_id = $wh_id;
                $model_new->product_id = $product_id;
                $model_new->qty = $qty;
                $model_new->company_id = $company_id;
                $model_new->branch_id = $branch_id;
                $model_new->save(false);
            }
        }
    }

    public function updatebootSummary($product_id, $wh_id, $route_id, $qty, $company_id, $branch_id)
    {
        if ($wh_id != null && $product_id != null && $qty > 0) {
            $model = \backend\models\Stocksum::find()->where(['warehouse_id' => $wh_id, 'product_id' => $product_id, 'route_id' => $route_id])->one();
            if ($model) {
                if ($model->qty < 0) {
                    $model->qty = (double)$qty;
                    $model->save(false);
                } else {
                    $model->qty = ($model->qty + (int)$qty);
                    $model->save(false);
                }

            } else {
                $model_new = new \backend\models\Stocksum();
                $model_new->warehouse_id = $wh_id;
                $model_new->product_id = $product_id;
                $model_new->qty = $qty;
                $model_new->company_id = $company_id;
                $model_new->branch_id = $branch_id;
                $model_new->save(false);
            }
        }
    }

    public function updatebootSummary2($product_id, $wh_id, $route_id, $qty, $company_id, $branch_id, $user_id)
    {
        if ($product_id != null && $qty > 0 && $company_id != null && $branch_id != null) {
            $order_shift = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'issue_id', 0])->groupBy(['issue_id'])->count('issue_id');
            $model = \common\models\SaleRouteDailyClose::find()->where(['product_id' => $product_id, 'route_id' => $route_id, 'date(trans_date)' => date('Y-m-d'), 'order_shift' => $order_shift])->one();
            if ($model) {
                $model->qty = $qty;
                $model->trans_date = date('Y-m-d H:i:s');
                $model->save(false);
            } else {
                $model_new = new \common\models\SaleRouteDailyClose();
                $model_new->trans_date = date('Y-m-d H:i:s');
                $model_new->route_id = $route_id;
                $model_new->product_id = $product_id;
                $model_new->qty = $qty;
                $model_new->company_id = $company_id;
                $model_new->branch_id = $branch_id;
                $model_new->order_shift = $order_shift;
                $model_new->crated_by = $user_id;
                $model_new->save(false);
            }
        }
    }

    public function findCarempdaily($car_id)
    {
        $model = null;
        if ($car_id) {
            $model = \backend\models\Cardaily::find()->select(['employee_id'])->where(['car_id' => $car_id, 'date(trans_date)' => date('Y-m-d')])->all();
        }
        return $model;
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

    public function actionCancelorderOriginal() // 29/01/2023
    {
        $status = 0;

        $order_line_id = 0;
        $route_name = "";
        $order_no = '';
        $customer_code = '';
        $product_code = '';
        $reason = '';


        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $order_line_id = $req_data['line_id'];
        $route_name = $req_data['route_name'];
        $order_no = $req_data['order_no'];
        //$customer_id = $req_data['customer_id'];
        $customer_id = $req_data['customer_code'];
        $product_code = $req_data['product_code'];
        $reason = $req_data['reason'];

        $data = [];

        if ($order_line_id != null) {
            $model = \backend\models\Orderlinetrans::find()->where(['id' => $order_line_id])->andFilterWhere(['!=', 'status', 500])->one();
            if ($model) {

                //$model_order_stock = \common\models\OrderStock::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id])->one();
                $find_route_id = 0;
                $model_route_order = \backend\models\Orders::find()->select(['order_channel_id'])->where(['id' => $model->order_id])->one();
                if ($model_route_order) {
                    $find_route_id = $model_route_order->order_channel_id;
                }
                $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => date('Y-m-d')])->one();
                if ($model_order_stock) {
                    $model_order_stock->avl_qty = $model_order_stock->avl_qty + $model->qty;
                    if ($model_order_stock->save(false)) {
                        $model->status = 500;
                        if ($model->save(false)) {
                            $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
                            if ($model_update_line) {
                                $new_line_total = ($model_update_line->price * $model->qty);
                                $model_update_line->qty = ($model_update_line->qty - $model->qty);
                                $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
                                if ($model_update_line->save(false)) {
                                    $status = 1;
                                    array_push($data, ['cancel_order' => 'successfully']);
                                    $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
                                }
                            }
                        }
                    }
                } else {
                    $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                    $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => $pre_date])->one();
                    if ($model_order_stock) {
                        $model_order_stock->avl_qty = $model_order_stock->avl_qty + $model->qty;
                        if ($model_order_stock->save(false)) {
                            $model->status = 500;
                            if ($model->save(false)) {
                                $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
                                if ($model_update_line) {
                                    $new_line_total = ($model_update_line->price * $model->qty);
                                    $model_update_line->qty = ($model_update_line->qty - $model->qty);
                                    $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
                                    if ($model_update_line->save(false)) {
                                        $status = 1;
                                        array_push($data, ['cancel_order' => 'successfully']);
                                        $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return ['status' => $status, 'data' => $data];
    }

//    public function actionCancelorder() // 29/01/2023
//    {
//        $status = 0;
//
//        $order_line_id = 0;
//        $route_name = "";
//        $order_no = '';
//        $customer_code = '';
//        $product_code = '';
//        $reason = '';
//
//
//        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//        $req_data = \Yii::$app->request->getBodyParams();
//        $order_line_id = $req_data['line_id'];
//        $route_name = $req_data['route_name'];
//        $order_no = $req_data['order_no'];
//        //$customer_id = $req_data['customer_id'];
//        $customer_id = $req_data['customer_code'];
//        $product_code = $req_data['product_code'];
//        $reason = $req_data['reason'];
//
//        $data = [];
//
//        if ($order_line_id != null) {
//            $model = \backend\models\Orderline::find()->where(['id' => $order_line_id])->andFilterWhere(['!=', 'status', 500])->one();
//            if ($model) {
//
//                //$model_order_stock = \common\models\OrderStock::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id])->one();
//                $find_route_id = 0;
//                $model_route_order = \backend\models\Orders::find()->select(['order_channel_id'])->where(['id' => $model->order_id])->one();
//                if ($model_route_order) {
//                    $find_route_id = $model_route_order->order_channel_id;
//                }
//                $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => date('Y-m-d')])->one();
//                if ($model_order_stock) {
//                    $model_order_stock->avl_qty = $model_order_stock->avl_qty + $model->qty;
//                    if ($model_order_stock->save(false)) {
//                        $model->status = 500;
//                        if ($model->save(false)) {
//                            $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
//                            if ($model_update_line) {
//                                $new_line_total = ($model_update_line->price * $model->qty);
//                                $model_update_line->qty = ($model_update_line->qty - $model->qty);
//                                $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
//                                if ($model_update_line->save(false)) {
//                                    $status = 1;
//                                    array_push($data, ['cancel_order' => 'successfully']);
//                                    $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
//                                }
//                            }
//                        }
//                    }
//                } else {
//                    $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
//                    $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => $pre_date])->one();
//                    if ($model_order_stock) {
//                        $model_order_stock->avl_qty = $model_order_stock->avl_qty + $model->qty;
//                        if ($model_order_stock->save(false)) {
//                            $model->status = 500;
//                            if ($model->save(false)) {
//                                $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
//                                if ($model_update_line) {
//                                    $new_line_total = ($model_update_line->price * $model->qty);
//                                    $model_update_line->qty = ($model_update_line->qty - $model->qty);
//                                    $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
//                                    if ($model_update_line->save(false)) {
//                                        $status = 1;
//                                        array_push($data, ['cancel_order' => 'successfully']);
//                                        $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        return ['status' => $status, 'data' => $data];
//    }

    public function actionCancelorder() // 29/01/2023
    {
        $status = 0;

        $order_line_id = 0;
        $route_name = "";
        $order_no = '';
        $customer_code = '';
        $product_code = '';
        $reason = '';


        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $order_line_id = $req_data['line_id'];
        $route_name = $req_data['route_name'];
        $order_no = $req_data['order_no'];
        //$customer_id = $req_data['customer_id'];
        $customer_id = $req_data['customer_code'];
        $product_code = $req_data['product_code'];
        $reason = $req_data['reason'];

        $data = [];

        if ($order_line_id != null) {
            $model = \backend\models\Orderline::find()->where(['id' => $order_line_id])->andFilterWhere(['!=', 'status', 500])->one();
            if ($model) {

                //$model_order_stock = \common\models\OrderStock::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id])->one();
                $find_route_id = 0;
                $model_route_order = \backend\models\Orders::find()->select(['order_channel_id'])->where(['id' => $model->order_id])->one();
                if ($model_route_order) {
                    $find_route_id = $model_route_order->order_channel_id;
                }

                try {
                    $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => date('Y-m-d')])->one();
                    if ($model_order_stock) {
                       // $model_order_stock->avl_qty = ($model_order_stock->avl_qty + $model->qty);
                        $model_order_stock->avl_qty = ((float)$model_order_stock->avl_qty + (float)$model->qty);
                        if ($model_order_stock->save(false)) {
                            $model->status = 500;
                            if ($model->save(false)) {
                                $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
                                if ($model_update_line) {
                                    //  $new_line_total = ($model_update_line->price * $model->qty);
//                                $model_update_line->qty = ($model_update_line->qty - $model->qty);
//                                $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
                                    $model_update_line->qty = 0;
                                    $model_update_line->line_total = 0;
                                    if ($model_update_line->save(false)) {
                                        // \common\models\PaymentReceiveLine::updateAll(['payment_amount'=>0],['order_id'=>$model->order_id]); // delete payment history
                                        $status = 1;
                                        array_push($data, ['cancel_order' => 'successfully']);
                                        $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
                                    }
                                }
                            }
                        }
                    } else {
                        $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                        $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => $pre_date])->one();
                        if ($model_order_stock) {
                            $model_order_stock->avl_qty = ($model_order_stock->avl_qty + $model->qty);
                            if ($model_order_stock->save(false)) {
                                $model->status = 500;
                                if ($model->save(false)) {
                                    $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
                                    if ($model_update_line) {
//                                    $new_line_total = ($model_update_line->price * $model->qty);
//                                    $model_update_line->qty = ($model_update_line->qty - $model->qty);
//                                    $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);

                                        $model_update_line->qty = 0;
                                        $model_update_line->line_total = 0;
                                        if ($model_update_line->save(false)) {
                                            $status = 1;
                                            array_push($data, ['cancel_order' => 'successfully']);
                                            $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $exc) {

                }
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function actionCancelordervp19() // 29/01/2023
    {
        $status = 0;

        $order_line_id = 0;
        $route_name = "";
        $order_no = '';
        $customer_code = '';
        $product_code = '';
        $reason = '';


        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $order_line_id = $req_data['line_id'];
        $route_name = $req_data['route_name'];
        $order_no = $req_data['order_no'];
        //$customer_id = $req_data['customer_id'];
        $customer_id = $req_data['customer_code'];
        $product_code = $req_data['product_code'];
        $reason = $req_data['reason'];

        $data = [];

        if ($order_line_id != null) {
            $model = \backend\models\Orderline::find()->where(['id' => $order_line_id])->andFilterWhere(['!=', 'status', 500])->one();
            if ($model) {
                //$model_order_stock = \common\models\OrderStock::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id])->one();
                $find_route_id = 0;
                $model_route_order = \backend\models\Orders::find()->select(['order_channel_id'])->where(['id' => $model->order_id])->one();
                if ($model_route_order) {
                    $find_route_id = $model_route_order->order_channel_id;
                }
                try {
                    $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => date('Y-m-d')])->one();
                    if ($model_order_stock) {
                       // $model_order_stock->avl_qty = ($model_order_stock->avl_qty + $model->qty);
                        $model_order_stock->avl_qty = ((float)$model_order_stock->avl_qty + (float)$model->qty);
                        if ($model_order_stock->save(false)) {
                            $model->status = 500;
                            if ($model->save(false)) {
                                $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
                                if ($model_update_line) {
//                                $new_line_total = ($model_update_line->price * $model->qty);
//                                $model_update_line->qty = ($model_update_line->qty - $model->qty);
//                                $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
                                    //$new_line_total = ($model_update_line->price * $model->qty);
                                    $model_update_line->qty = 0;
                                    $model_update_line->line_total = 0;
                                    if ($model_update_line->save(false)) {
                                        $status = 1;
                                        $this->updateOrdermasterCancel($model->order_id);
                                        array_push($data, ['cancel_order' => 'successfully']);
                                        $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
                                    }
                                }
                            }
                        }
                    } else {
                        $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                        $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $find_route_id, 'product_id' => $model->product_id, 'date(trans_date)' => $pre_date])->one();
                        if ($model_order_stock) {
                            $model_order_stock->avl_qty = ($model_order_stock->avl_qty + $model->qty);
                            if ($model_order_stock->save(false)) {
                                $model->status = 500;
                                if ($model->save(false)) {
                                    $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
                                    if ($model_update_line) {
//                                    $new_line_total = ($model_update_line->price * $model->qty);
//                                    $model_update_line->qty = ($model_update_line->qty - $model->qty);
//                                    $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
                                        $model_update_line->qty = 0;
                                        $model_update_line->line_total = 0;
                                        if ($model_update_line->save(false)) {
                                            $status = 1;
                                            $this->updateOrdermasterCancel($model->order_id);
                                            array_push($data, ['cancel_order' => 'successfully']);
                                            $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $exc) {

                }
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function updateOrdermasterCancel($order_id){
        if($order_id != null){
            $cnt = 0;
            $model = \common\models\OrderLine::find()->where(['order_id' => $order_id])->all();
            if($model){
                $cnt = count($model);
                $is_cancel = 0;
                foreach ($model as $value) {
                    if($value->status == 500){
                        $is_cancel += 1;
                    }
                }
                if($cnt == $is_cancel) { // check all line is cancel
                    \common\models\Orders::updateAll(['status'=>3],['id'=>$order_id]); // update order master to cancel status
                }
            }
        }
    }

    public function actionCancelorder2()
    {
        $status = 0;

        $order_line_id = 0;
        $route_name = "";
        $order_no = '';
        $customer_code = '';
        $product_code = '';
        $reason = '';


        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $order_line_id = $req_data['line_id'];
        $route_name = $req_data['route_name'];
        $order_no = $req_data['order_no'];
        //$customer_id = $req_data['customer_id'];
        $customer_id = $req_data['customer_code'];
        $product_code = $req_data['product_code'];
        $reason = $req_data['reason'];

        $data = [];

        if ($order_line_id != null) {
            $model = \backend\models\Orderlinetrans::find()->where(['id' => $order_line_id])->andFilterWhere(['!=', 'status', 500])->one();
            if ($model) {
                $route_id = \backend\models\Deliveryroute::findId($route_name);
                $model_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $model->product_id])->one();
                if ($model_order_stock) {
                    $model_order_stock->avl_qty = $model_order_stock->avl_qty + $model->qty;
                    if ($model_order_stock->save(false)) {
                        $model->status = 500;
                        if ($model->save(false)) {
                            $model_update_line = \backend\models\Orderline::find()->where(['order_id' => $model->order_id, 'product_id' => $model->product_id, 'customer_id' => $customer_id])->one();
                            if ($model_update_line) {
                                $new_line_total = ($model_update_line->price * $model->qty);
                                $model_update_line->qty = ($model_update_line->qty - $model->qty);
                                $model_update_line->line_total = ($model_update_line->line_total - $new_line_total);
                                if ($model_update_line->save(false)) {
                                    $status = 1;
                                    array_push($data, ['cancel_order' => 'successfully']);
                                    $this->notifymessage('สายส่ง: ' . $route_name . ' ยกเลิกรายการขาย ' . $order_no . ' ลูกค้า: ' . $customer_code . ' ยอดเงิน: ' . $model->line_total . ' เหตุผล: ' . $reason);
                                }
                            }
                        }
                    }
                }
            }
        }
        return ['status' => $status, 'data' => $data];
    }

    public function notifymessage($message)
    {
//        //$message = "This is test send request from camel paperless";
//        $line_api = 'https://notify-api.line.me/api/notify';
//       // $line_token = 'ZMqo4ZqwBGafMOXKVht2Liq9dCGswp4IRofT2EbdRNN'; // NKY
//        // $line_token = 'N3x9CANrOE3qjoAejRBLjrJ7FhLuTBPFuC9ToXh0szh';
//        $line_token = 'TxAUAOScIROaBexBWXaYrVcbjBItIKUwGzFpoFy3Jrx'; // BKT

//        $line_token = '';
//
//        if ($company_id == 1 && $branch_id == 1) {
//            //  $line_token = 'ZMqo4ZqwBGafMOXKVht2Liq9dCGswp4IRofT2EbdRNN'; // vorapat
//            $b_token = \backend\models\Branch::findLintoken($company_id, $branch_id);
//            //   $line_token = '6kL3UlbKb1djsoGE7KFXSo9SQ1ikYb2MxmTHDeEy3GE'; // omnoi
//            $line_token = trim($b_token);
//        } else if ($company_id == 1 && $branch_id == 2) {
//            $b_token = \backend\models\Branch::findLintoken($company_id, $branch_id);
//            $line_token = trim($b_token);
//            //   $line_token = 'TxAUAOScIROaBexBWXaYrVcbjBItIKUwGzFpoFy3Jrx'; // BKT
//        }

        // $queryData = array('message' => $message);
//        $queryData = array('message' => $message);
//        $queryData = http_build_query($queryData, '', '&');
//        $headerOptions = array(
//            'http' => array(
//                'method' => 'POST',
//                'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
//                    . "Authorization: Bearer " . $line_token . "\r\n"
//                    . "Content-Length: " . strlen($queryData) . "\r\n",
//                'content' => $queryData
//            )
//        );
//        $context = stream_context_create($headerOptions);
//        $result = file_get_contents($line_api, FALSE, $context);
//        $res = json_decode($result);

        /// telegram
        $telegram_api_url = "https://api.telegram.org/bot";
        $telegram_token = "7773151435:AAFVgTs4A2dU8mY3bhDAg4gXWd38iuzekfE";
        $telegram_chat_id = "-4727306105";// "7653537115"; //-4787477639 group chat id
        // $message = "This is test send request from icesystem ";


        $url = $telegram_api_url . $telegram_token . "/sendMessage?chat_id=" . $telegram_chat_id . "&text=" . urlencode($message);
        //Send message to telegram
        file_get_contents($url);
        // end telegram
       //  return $res;
    }

    public function notifymessageorderclose($route_id, $user_id, $company_id, $branch_id)
    {
//        //$message = "This is test send request from camel paperless";
//        $line_api = 'https://notify-api.line.me/api/notify';
//        $line_token = '';
//
//        //   6kL3UlbKb1djsoGE7KFXSo9SQ1ikYb2MxmTHDeEy3GE   token omnoi
//        if ($company_id == 1 && $branch_id == 1) {
//            //  $line_token = 'ZMqo4ZqwBGafMOXKVht2Liq9dCGswp4IRofT2EbdRNN'; // vorapat
//            $b_token = \backend\models\Branch::findLintoken($company_id, $branch_id);
//            //   $line_token = '6kL3UlbKb1djsoGE7KFXSo9SQ1ikYb2MxmTHDeEy3GE'; // omnoi
//            $line_token = trim($b_token);
//        } else if ($company_id == 1 && $branch_id == 2) {
//            $b_token = \backend\models\Branch::findLintoken($company_id, $branch_id);
//            $line_token = trim($b_token);
//            //   $line_token = 'TxAUAOScIROaBexBWXaYrVcbjBItIKUwGzFpoFy3Jrx'; // BKT
//        }
//
//        // $line_token = 'N3x9CANrOE3qjoAejRBLjrJ7FhLuTBPFuC9ToXh0szh'

        // $queryData = array('message' => $message);
        $credit_total = \backend\models\Orders::findordercredit2($route_id, 1);
        $cash_total = \backend\models\Orders::findordercash2($route_id, 1);
        $credit_discount_total = \backend\models\Orders::findordercreditdiscount($route_id, 1);
        $cash_discount_total = \backend\models\Orders::findordercashdiscount($route_id, 1);
        $total = $credit_total + $cash_total;

        $message = '' . "\n";
        $message .= 'BKT:' . \backend\models\Deliveryroute::findName($route_id) . "\n";
        $message .= 'User:' . $this->findEmpName($user_id) . "\n";
        //   $message .= 'User:' . \backend\models\User::findName($user_id) . "\n";
        $message .= "รวมยอดขาย วันที่: " . date('Y-m-d') . "(" . date('H:i') . ")" . "\n";

        $message .= 'ขายสด: ' . number_format($cash_total, 2) . "\n";
        $message .= "ขายเชื่อ: " . number_format($credit_total, 2) . "\n";
        $message .= "ลดสด: " . number_format($cash_discount_total, 2) . "\n";
        $message .= "ลดเชื่อ: " . number_format($credit_discount_total, 2) . "\n";
        $message .= "รวม: " . number_format(($total) - ($cash_discount_total + $credit_discount_total), 2) . "\n";

        $message .= "รับชำระหนี้ " . "\n";
        $message .= "เงินสด: " . number_format($this->getPayment($route_id, date('Y-m-d')), 2) . "\n";
        $message .= "เงินโอน: " . number_format(0, 2) . "\n";

       // $message .= 'สามารถดูรายละเอียดได้ที่ http://103.253.73.108/icesystem/backend/web/index.php?r=dailysum/indexnew' . "\n"; // nky
        $message .= 'สามารถดูรายละเอียดได้ที่ http://103.13.28.31/icesystem/backend/web/index.php?r=dailysum/indexnew' . "\n"; // bkt


//        $queryData = array('message' => $message);
//        $queryData = http_build_query($queryData, '', '&');
//        $headerOptions = array(
//            'http' => array(
//                'method' => 'POST',
//                'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
//                    . "Authorization: Bearer " . $line_token . "\r\n"
//                    . "Content-Length: " . strlen($queryData) . "\r\n",
//                'content' => $queryData
//            )
//        );
//        $context = stream_context_create($headerOptions);
//        $result = file_get_contents($line_api, FALSE, $context);
//        $res = json_decode($result);
//        return $res;

        /// telegram
        $telegram_api_url = "https://api.telegram.org/bot";
        $telegram_token = "7773151435:AAFVgTs4A2dU8mY3bhDAg4gXWd38iuzekfE";
        $telegram_chat_id = "-4727306105";// "7653537115"; //-4787477639 group chat id
        // $message = "This is test send request from icesystem ";


        $url = $telegram_api_url . $telegram_token . "/sendMessage?chat_id=" . $telegram_chat_id . "&text=" . urlencode($message);
        //Send message to telegram
        file_get_contents($url);
        // end telegram
    }

    public function findEmpName($user_id)
    {
        $emp_name = '';
        if ($user_id != '') {
            $model_x = User::find()->where(['id' => $user_id])->one();
            if ($model_x) {
                $model_emp = Employee::find()->where(['id' => $model_x->employee_ref_id])->one();
                if ($model_emp) {
                    $emp_name = $model_emp->fname . ' ' . $model_emp->lname;
                }
            }
        }
        return $emp_name;

    }

    function getPayment($route_id, $order_date)
    {
        $pay_amount = 0;

        $sql = "SELECT SUM(t1.payment_amount) as pay_amount";
        $sql .= " FROM query_payment_receive as t1 INNER JOIN customer as t2 ON t1.customer_id = t2.id";
        $sql .= " WHERE  date(t1.trans_date) =" . "'" . date('Y-m-d', strtotime($order_date)) . "'" . " ";
        $sql .= " AND t1.payment_method_id = 2";
        if ($route_id != null) {
            $sql .= " AND t2.delivery_route_id=" . $route_id;
        }
        $sql .= " GROUP BY t2.delivery_route_id";
        $query = \Yii::$app->db->createCommand($sql);
        $model = $query->queryAll();
        if ($model) {
            for ($i = 0; $i <= count($model) - 1; $i++) {
                $pay_amount = $model[$i]['pay_amount'];
            }
        }
        return $pay_amount;
    }

//    public function actionAddordernew()
//    {
//        $customer_id = null;
//        $status = false;
//        $user_id = 0;
//        $issue_id = 0;
//        $route_id = 0;
//        $car_id = 0;
//        $company_id = 0;
//        $branch_id = 0;
//        $datalist = null;
//
//        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
//        $req_data = \Yii::$app->request->getBodyParams();
//        if ($req_data != null) {
//            $customer_id = $req_data['customer_id'];
//            $user_id = $req_data['user_id'] == null ? 0 : $req_data['user_id'];
//            //  $issue_id = $req_data['issue_id'];
//            $route_id = $req_data['route_id'];
//            $car_id = $req_data['car_id'];
//            $payment_type_id = $req_data['payment_type_id'];
//            $company_id = $req_data['company_id'];
//            $branch_id = $req_data['branch_id'];
//            $datalist = $req_data['data'];
//        }
//
//        $data = [];
//        $is_free = 0;
//        if ($payment_type_id == 3) {
//            $is_free = 1;
//        }
//        if ($customer_id && $route_id && $car_id) {
//            //  $sale_date = date('Y/m/d');
//            $sale_date = date('Y/m/d');
//
//            $sale_time = date('H:i:s');
//            $order_total_all = 0;
//            $has_order = $this->hasOrder($sale_date, $route_id, $car_id);
//            if ($has_order != null) {
//                $has_order_id = $has_order->id;
//                if ($has_order_id) {
//                    //$this->registerissue($has_order_id, $issue_id, $company_id, $branch_id);
//                    //$price = $this->findCustomerprice($customer_id, $product_id, $route_id);
//
//                    if (count($datalist) > 0) {
//                        for ($i = 0; $i <= count($datalist) - 1; $i++) {
//                            if ($datalist[$i]['qty'] <= 0) continue;
//
//                            // $price_group_id = $this->findCustomerpricgroup($customer_id, $datalist[$i]['product_id'], $route_id);
//
//                            $modelx = \common\models\OrderLine::find()->where(['product_id' => $datalist[$i]['product_id'], 'order_id' => $has_order_id, 'customer_id' => $customer_id])->one();
//                            if ($modelx) {
//                                $modelx->qty = ($modelx->qty + $datalist[$i]['qty']);
//                                $modelx->line_total = $payment_type_id == 3 ? 0 : ($modelx->qty * $datalist[$i]['price']);
//                                $modelx->status = 1;
//                                $modelx->is_free = $is_free;
//                                if ($modelx->save(false)) {
//                                    $status = true;
//
//                                }
//                            } else {
//                                $model_line = new \backend\models\Orderline();
//                                $model_line->order_id = $has_order_id;
//                                $model_line->customer_id = $customer_id;
//                                $model_line->product_id = $datalist[$i]['product_id'];
//                                $model_line->qty = $datalist[$i]['qty'];
//                                $model_line->price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
//                                $model_line->line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);
//                                $model_line->price_group_id = $datalist[$i]['price_group_id'] ;//$price_group_id;
//                                $model_line->sale_payment_method_id = $payment_type_id;
//                                $model_line->issue_ref_id = $issue_id;
//                                $model_line->status = 1;
//                                $model_line->is_free = $is_free;
//                                if ($model_line->save(false)) {
//
//                                    //  if ($payment_type_id == 2) {
//                                    if ($payment_type_id != 3) {
//                                        $this->addpayment($has_order_id, $customer_id, ($datalist[$i]['qty'] * $datalist[$i]['price']), $company_id, $branch_id, $payment_type_id);
//                                    }
//
//                                    //  }
//
//                                    $order_total_all += $model_line->line_total;
//                                    $status = true;
//
//                                    // issue order stock
//                                    $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                                    if ($model_update_order_stock) {
//                                        if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) {
//                                            $model_update_order_stock->order_id = $has_order_id;
//                                            $model_update_order_stock->avl_qty = $model_update_order_stock->avl_qty - $datalist[$i]['qty'];
//                                            $model_update_order_stock->save(false);
//                                        } else {
//                                            $remain_qty = $datalist[$i]['qty'] - $model_update_order_stock->avl_qty;
//
//                                            $model_update_order_stock->order_id = $has_order_id;
//                                            $model_update_order_stock->avl_qty = 0;
//                                            if ($model_update_order_stock->save(false)) {
//
//                                                $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                                                if ($model_update_order_stock2) {
//                                                    $model_update_order_stock2->order_id = $has_order_id;
//                                                    $model_update_order_stock2->avl_qty = $model_update_order_stock2->avl_qty - $remain_qty;
//                                                    $model_update_order_stock2->save(false);
//                                                }
//                                            }
//                                        }
//                                    }
//                                    // end issue order stock
//
//                                }
//                            }
//
////                    $model->order_total_amt = $order_total_all;
////                    $model->save(false);
//                        }
//                    }
//
//                }
//            } else {
//                $model = new \backend\models\Ordermobile();
//                $model->order_no = $model->getLastNo($sale_date, $company_id, $branch_id);
//                // $model->order_date = date('Y-m-d H:i:s', strtotime($sale_date . ' ' . $sale_time));
//                $model->order_date = date('Y-m-d H:i:s');
//                $model->customer_id = 0;
//                $model->order_channel_id = $route_id; // สายส่ง
//                $model->sale_channel_id = 1; //ช่องทาง
//                $model->car_ref_id = $car_id;
//                $model->issue_id = $issue_id;
//                $model->status = 1;
//                $model->created_by = $user_id;
//                $model->company_id = $company_id;
//                $model->branch_id = $branch_id;
//                $model->sale_from_mobile = 1;
//                if ($model->save(false)) {
//                    array_push($data, ['order_id' => $model->id]);
//                    //$this->registerissue($model->id, $issue_id, $company_id, $branch_id);
//                    //   $price = $this->findCustomerprice($customer_id, $product_id, $route_id);
//
//                    if (count($datalist) > 0) {
//                        for ($i = 0; $i <= count($datalist) - 1; $i++) {
//                            if ($datalist[$i]['qty'] <= 0) continue;
//
//
//                            // $price_group_id = $this->findCustomerpricgroup($customer_id, $datalist[$i]['product_id'], $route_id);
//
//                            $model_line = new \backend\models\Orderline();
//                            $model_line->order_id = $model->id;
//                            $model_line->customer_id = $customer_id;
//                            $model_line->product_id = $datalist[$i]['product_id'];
//                            $model_line->qty = $datalist[$i]['qty'];
//                            $model_line->price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
//                            $model_line->line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);
//                            $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
//                            $model_line->status = 1;
//                            $model_line->sale_payment_method_id = $payment_type_id;
//                            $model_line->issue_ref_id = $issue_id;
//                            $model_line->is_free = $is_free;
//                            if ($model_line->save(false)) {
//
//                                //   if ($payment_type_id == 2) {
//
//                                if ($payment_type_id != 3) {
//                                    $this->addpayment($model->id, $customer_id, ($datalist[$i]['qty'] * $datalist[$i]['price']), $company_id, $branch_id, $payment_type_id);
//                                }
//                                //  }
//
//                                $order_total_all += $model_line->line_total;
//                                $status = true;
////
////                        $model_update_issue_line = \common\models\JournalIssueLine::find()->where(['issue_id' => $issue_id, 'product_id' => $product_id])->one();
////                        if ($model_update_issue_line) {
////                            $model_update_issue_line->avl_qty = $model_update_issue_line->avl_qty - $qty;
////                            $model_update_issue_line->save(false);
////                        }
//                                // issue order stock
//                                $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                                if ($model_update_order_stock) {
//                                    if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) {
//                                        $model_update_order_stock->order_id = $model->id;
//                                        $model_update_order_stock->avl_qty = $model_update_order_stock->avl_qty - $datalist[$i]['qty'];
//                                        $model_update_order_stock->save(false);
//                                    } else {
//                                        $remain_qty = $datalist[$i]['qty'] - $model_update_order_stock->avl_qty;
//
//                                        $model_update_order_stock->order_id = $model->id;
//                                        $model_update_order_stock->avl_qty = 0;
//                                        if ($model_update_order_stock->save(false)) {
//
//                                            $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
//                                            if ($model_update_order_stock2) {
//                                                $model_update_order_stock2->order_id = $model->id;
//                                                $model_update_order_stock2->avl_qty = $model_update_order_stock2->avl_qty - $remain_qty;
//                                                $model_update_order_stock2->save(false);
//                                            }
//                                        }
//                                    }
//                                }
//                                // end issue order stock
//
//                            }
//
//                        }
//                    }
//
//                    $model->order_total_amt = $order_total_all;
//                    $model->save(false);
//                    if ($model->issue_id > 0) {
//                        $model_issue = \backend\models\Journalissue::find()->where(['id' => $model->issue_id])->one();
//                        if ($model_issue) {
//                            $model_issue->status = 2;
//                            $model_issue->order_ref_id = $model->id;
//                            $model_issue->company_id = $company_id;
//                            $model_issue->branch_id = $branch_id;
//                            $model_issue->save(false);
//                        }
//                    }
//                }
//            }
//        }
//        //  array_push($data,['data'=>$req_data]);
//
//        return ['status' => $status, 'data' => $data];
//    }

    public function actionAddordernew3()
    {
        $customer_id = null;
        $status = false;
        $user_id = 0;
        $issue_id = 0;
        $route_id = 0;
        $car_id = 0;
        $company_id = 0;
        $branch_id = 0;
        $datalist = null;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $customer_id = $req_data['customer_id'];
            $user_id = $req_data['user_id'] == null ? 0 : $req_data['user_id'];
            //  $issue_id = $req_data['issue_id'];
            $route_id = $req_data['route_id'];
            $car_id = $req_data['car_id'];
            $payment_type_id = $req_data['payment_type_id'];
            $company_id = $req_data['company_id'];
            $branch_id = $req_data['branch_id'];
            $datalist = $req_data['data'];
        }

        $data = [];
        $is_free = 0;
        if ($payment_type_id == 3) {
            $is_free = 1;
        }
        if ($customer_id != null && $route_id != null && $car_id != null) {
            //  $sale_date = date('Y/m/d');
            $sale_date = date('Y/m/d');

            $order_total_all = 0;
            $order_shift = 0;
            $has_order = null;
            //  $has_order = $this->hasOrderCustomer($sale_date, $route_id, $customer_id); // find today customer order

            // $order_shift = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->groupBy(['route_id'])->count('issue_id');

            $sql_shift = "select route_id,count(distinct issue_id) AS cnt";
            $sql_shift .= " FROM order_stock";
            $sql_shift .= " WHERE  route_id =" . $route_id;
            $sql_shift .= " AND date(trans_date) =" . "'" . date('Y-m-d') . "'" . " ";
            $sql_shift .= " GROUP BY route_id ";

            $sql_shift_query = \Yii::$app->db->createCommand($sql_shift);
            $shift_data = $sql_shift_query->queryAll();

            if ($shift_data != null) {
                $order_shift = $shift_data[0]['cnt'];
            }


            $sql = "SELECT t1.id";
            $sql .= " FROM orders as t1 INNER JOIN order_line as t2 ON t2.order_id=t1.id";
            $sql .= " WHERE  t2.customer_id =" . $customer_id;
            $sql .= " AND t1.order_shift =" . $order_shift;
            $sql .= " AND date(t1.order_date) =" . "'" . date('Y-m-d') . "'" . " ";
            $query = \Yii::$app->db->createCommand($sql);
            $has_order = $query->queryAll();


            //  $has_order = $modelx;

            if ($has_order != null) {
                $has_order_id = $has_order[0]['id'];
                if ($has_order_id) {
                    if (count($datalist) > 0) {
                        for ($i = 0; $i <= count($datalist) - 1; $i++) {
                            if ($datalist[$i]['qty'] <= 0) continue;

                            // $price_group_id = $this->findCustomerpricgroup($customer_id, $datalist[$i]['product_id'], $route_id);

                            //  $line_total = ($datalist[$i]['qty'] * $datalist[$i]['price']);

                            $line_price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
                            $line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);

                            $model_line_trans = new \backend\models\Orderlinetrans();
                            $model_line_trans->order_id = $has_order_id;
                            $model_line_trans->customer_id = $customer_id;
                            $model_line_trans->product_id = $datalist[$i]['product_id'];
                            $model_line_trans->qty = $datalist[$i]['qty'];
                            $model_line_trans->price = $line_price;
                            $model_line_trans->line_total = $line_total;
                            $model_line_trans->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                            $model_line_trans->sale_payment_method_id = $payment_type_id;
                            $model_line_trans->issue_ref_id = $issue_id;
                            $model_line_trans->status = 1;
                            $model_line_trans->is_free = $is_free;
                            if ($model_line_trans->save(false)) {

                                $modelx = \common\models\OrderLine::find()->select(['qty', 'line_total', 'status', 'is_free'])->where(['product_id' => $datalist[$i]['product_id'], 'order_id' => $has_order_id, 'customer_id' => $customer_id])->one();
                                if ($modelx) {
                                    $modelx->qty = ($modelx->qty + $datalist[$i]['qty']);
                                    $modelx->line_total = $line_total;//$payment_type_id == 3 ? 0 : ($modelx->qty * $datalist[$i]['price']);
                                    $modelx->status = 1;
                                    $modelx->is_free = $is_free;
                                    if ($modelx->save(false)) {
                                        $status = true;
                                    }
                                } else {

                                    $model_line = new \backend\models\Orderline();
                                    $model_line->order_id = $has_order_id;
                                    $model_line->customer_id = $customer_id;
                                    $model_line->product_id = $datalist[$i]['product_id'];
                                    $model_line->qty = $datalist[$i]['qty'];
                                    $model_line->price = $line_price;
                                    $model_line->line_total = $line_total;
                                    $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                                    $model_line->sale_payment_method_id = $payment_type_id;
                                    $model_line->issue_ref_id = $issue_id;
                                    $model_line->status = 1;
                                    $model_line->is_free = $is_free;
                                    $model_line->save(false);
                                }

                                if ($payment_type_id != 3) { // credit
                                    $this->addpayment($has_order_id, $customer_id, $line_total, $company_id, $branch_id, $payment_type_id, $user_id);
                                }

                                //  $order_total_all += $model_line_trans->line_total;

                                // issue order stock
                                $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                if ($model_update_order_stock) {
                                    if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) {
                                        $model_update_order_stock->order_id = $has_order_id;
                                        $model_update_order_stock->avl_qty = ($model_update_order_stock->avl_qty - $datalist[$i]['qty']);
                                        $model_update_order_stock->save(false);
                                    } else {
                                        $remain_qty = ($datalist[$i]['qty'] - $model_update_order_stock->avl_qty);

                                        $model_update_order_stock->order_id = $has_order_id;
                                        $model_update_order_stock->avl_qty = 0;
                                        if ($model_update_order_stock->save(false)) {

                                            $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                            if ($model_update_order_stock2) {
                                                $model_update_order_stock2->order_id = $has_order_id;
                                                $model_update_order_stock2->avl_qty = ($model_update_order_stock2->avl_qty - $remain_qty);
                                                $model_update_order_stock2->save(false);
                                            }
                                        }
                                    }
                                }
                                // end issue order stock
                                $status = true;
                            }
                        }
                    }
                }
            } else {
                $emp_1 = 0;
                $emp_2 = 0;
                $empdaily = $this->findCarempdaily($car_id);
                if ($empdaily != null) {
                    $xx = 0;
                    foreach ($empdaily as $value_emp) {
                        if ($xx == 0) {
                            $emp_1 = $value_emp->employee_id;
                        } else {
                            $emp_2 = $value_emp->employee_id;
                        }
                        $xx += 1;
                    }
                }


                $model = new \backend\models\Ordermobile();
                $model->order_no = $model->getLastNoMobileNew($sale_date, $company_id, $branch_id, $route_id);
                // $model->order_no = $model->getLastNoMobile($sale_date, $company_id, $branch_id, $route_id);
                // $model->order_date = date('Y-m-d H:i:s', strtotime($sale_date . ' ' . $sale_time));
                $model->order_date = date('Y-m-d H:i:s');
                $model->customer_id = 0;
                $model->order_channel_id = $route_id; // สายส่ง
                $model->sale_channel_id = 1; //ช่องทาง
                $model->car_ref_id = $car_id;
                $model->issue_id = $issue_id;
                $model->status = 1;
                $model->created_by = $user_id;
                $model->company_id = $company_id;
                $model->branch_id = $branch_id;
                $model->sale_from_mobile = 1;
                $model->emp_1 = $emp_1;
                $model->emp_2 = $emp_2;
                $model->order_date2 = date('Y-m-d');
                $model->order_shift = $order_shift;

                if ($model->save(false)) {
                    // array_push($data, ['order_id' => $model->id]);
                    if (count($datalist) > 0) {
                        for ($i = 0; $i <= count($datalist) - 1; $i++) {
                            if ($datalist[$i]['qty'] <= 0) continue;
                            // $price_group_id = $this->findCustomerpricgroup($customer_id, $datalist[$i]['product_id'], $route_id);
                            //  $line_total = ($datalist[$i]['qty'] * $datalist[$i]['price']);

                            $line_price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
                            $line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);

                            $model_line_trans = new \backend\models\Orderlinetrans();
                            $model_line_trans->order_id = $model->id;
                            $model_line_trans->customer_id = $customer_id;
                            $model_line_trans->product_id = $datalist[$i]['product_id'];
                            $model_line_trans->qty = $datalist[$i]['qty'];
                            $model_line_trans->price = $line_price;
                            $model_line_trans->line_total = $line_total;
                            $model_line_trans->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                            $model_line_trans->sale_payment_method_id = $payment_type_id;
                            $model_line_trans->issue_ref_id = $issue_id;
                            $model_line_trans->status = 1;
                            $model_line_trans->is_free = $is_free;

                            if ($model_line_trans->save(false)) {
                                $model_line = new \backend\models\Orderline();
                                $model_line->order_id = $model->id;
                                $model_line->customer_id = $customer_id;
                                $model_line->product_id = $datalist[$i]['product_id'];
                                $model_line->qty = $datalist[$i]['qty'];
                                $model_line->price = $line_price;
                                $model_line->line_total = $line_total;
                                $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                                $model_line->status = 1;
                                $model_line->sale_payment_method_id = $payment_type_id;
                                $model_line->issue_ref_id = $issue_id;
                                $model_line->is_free = $is_free;
                                if ($model_line->save(false)) {
                                }

                                if ($payment_type_id != 3) { // credit
                                    $this->addpayment($model->id, $customer_id, $line_total, $company_id, $branch_id, $payment_type_id, $user_id);
                                }

                                $order_total_all += $model_line_trans->line_total;
                                $status = true;

                                // issue order stock
                                $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                //$model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id,  'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                if ($model_update_order_stock) {
                                    if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) {
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = ($model_update_order_stock->avl_qty - $datalist[$i]['qty']);
                                        $model_update_order_stock->save(false);
                                    } else {
                                        $remain_qty = ($datalist[$i]['qty'] - $model_update_order_stock->avl_qty);
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = 0;
                                        if ($model_update_order_stock->save(false)) {
                                            $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                            if ($model_update_order_stock2) {
                                                $model_update_order_stock2->order_id = $model->id;
                                                $model_update_order_stock2->avl_qty = ($model_update_order_stock2->avl_qty - $remain_qty);
                                                $model_update_order_stock2->save(false);
                                            }
                                        }
                                    }
                                }
                                // end issue order stock
                            }
                        }
                    }

                    $model->order_total_amt = $order_total_all;
                    $model->save(false);
                }
            }
        }
        //  array_push($data,['data'=>$req_data]);

        return ['status' => $status, 'data' => $data];
    }

    public function actionAddordernewOrigin()
    {
        $customer_id = null;
        $status = false;
        $user_id = 0;
        $emp_id = 0;
        $emp2_id = 0;
        $issue_id = 0;
        $route_id = 0;
        $car_id = 0;
        $company_id = 0;
        $branch_id = 0;
        $datalist = null;
        $discount = 0;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $customer_id = $req_data['customer_id'];
            $user_id = $req_data['user_id'] == null ? 1 : $req_data['user_id'];
            $emp_id = isset($req_data['emp_id']) ? $req_data['emp_id'] : 0;
            $emp2_id = isset($req_data['emp2_id']) ? $req_data['emp2_id'] : 0;
            //  $issue_id = $req_data['issue_id'];
            $route_id = $req_data['route_id'];
            $car_id = $req_data['car_id'];
            $payment_type_id = $req_data['payment_type_id'];
            $company_id = $req_data['company_id'];
            $branch_id = $req_data['branch_id'];
            $datalist = $req_data['data'];
            if (!empty($req_data['discount'])) {
                $discount = $req_data['discount'];
            }
        }

        $data = [];
        $is_free = 0;
        if ($payment_type_id == 3) {
            $is_free = 1;
        }
        if ($customer_id != null && $route_id != null) {
            //  $sale_date = date('Y/m/d');
            $sale_date = date('Y/m/d');

            $order_total_all = 0;
            $order_shift = 0;
            $has_order = null;
            //  $has_order = $this->hasOrderCustomer($sale_date, $route_id, $customer_id); // find today customer order

            // $order_shift = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d')])->groupBy(['route_id'])->count('issue_id');

            // last comment
//            $sql_shift = "select route_id,count(distinct issue_id) AS cnt";
//            $sql_shift .= " FROM order_stock";
//            $sql_shift .= " WHERE  route_id =" . $route_id;
//            $sql_shift .= " AND date(trans_date) =" . "'" . date('Y-m-d') . "'" . " ";
//            $sql_shift .= " GROUP BY route_id ";
//
//            $sql_shift_query = \Yii::$app->db->createCommand($sql_shift);
//            $shift_data = $sql_shift_query->queryAll();
//
//            if ($shift_data != null) {
//                $order_shift = $shift_data[0]['cnt'];
//            }

            // last comment

//            $emp_1 = 0;
//            $emp_2 = 0;
//            $empdaily = $this->findCarempdaily($car_id);
//            if ($empdaily != null) {
//                $xx = 0;
//                foreach ($empdaily as $value_emp) {
//                    if ($xx == 0) {
//                        $emp_1 = $value_emp->employee_id;
//                    } else {
//                        $emp_2 = $value_emp->employee_id;
//                    }
//                    $xx += 1;
//                }
//            }


            $model = new \backend\models\Ordermobile();
            $model->order_no = $model->getLastNoMobileNew($sale_date, $company_id, $branch_id, $route_id);
            $model->order_date = date('Y-m-d H:i:s');
            $model->customer_id = 0;
            $model->order_channel_id = $route_id; // สายส่ง
            $model->sale_channel_id = 1; //ช่องทาง
            $model->car_ref_id = $car_id;
            $model->issue_id = $issue_id;
            $model->status = 1;
            $model->created_by = $user_id;
            $model->company_id = $company_id;
            $model->branch_id = $branch_id;
            $model->sale_from_mobile = 1;
//            $model->emp_1 = $emp_1;
//            $model->emp_2 = $emp_2;
            // $model->emp_1 = 0;
            $model->emp_1 = $emp_id;
            $model->emp_2 = $emp2_id;
            $model->order_date2 = date('Y-m-d');
            $model->order_shift = $order_shift;
            $model->discount_amt = $discount;
            $model->payment_method_id = $payment_type_id;

            if ($payment_type_id == 2) {
                $model->payment_status = 0;
            }


            if ($model->save(false)) {

                if (count($datalist) > 0) {
                    $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                    for ($i = 0; $i <= count($datalist) - 1; $i++) {
                        if ($datalist[$i]['qty'] <= 0) continue;
                        // $price_group_id = $this->findCustomerpricgroup($customer_id, $datalist[$i]['product_id'], $route_id);
                        //  $line_total = ($datalist[$i]['qty'] * $datalist[$i]['price']);

                        $line_price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
                        $line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);

                        $model_line_trans = new \backend\models\Orderlinetrans();
                        $model_line_trans->order_id = $model->id;
                        $model_line_trans->customer_id = $customer_id;
                        $model_line_trans->product_id = $datalist[$i]['product_id'];
                        $model_line_trans->qty = $datalist[$i]['qty'];
                        $model_line_trans->price = $line_price;
                        $model_line_trans->line_total = $line_total;
                        $model_line_trans->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                        $model_line_trans->sale_payment_method_id = $payment_type_id;
                        $model_line_trans->issue_ref_id = $issue_id;
                        $model_line_trans->status = 1;
                        $model_line_trans->is_free = $is_free;

                        if ($model_line_trans->save(false)) {
                            $model_line = new \backend\models\Orderline();
                            $model_line->order_id = $model->id;
                            $model_line->customer_id = $customer_id;
                            $model_line->product_id = $datalist[$i]['product_id'];
                            $model_line->qty = $datalist[$i]['qty'];
                            $model_line->price = $line_price;
                            $model_line->line_total = $line_total;
                            $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                            $model_line->status = 1;
                            $model_line->sale_payment_method_id = $payment_type_id;
                            $model_line->issue_ref_id = $issue_id;
                            $model_line->is_free = $is_free;
                            if ($model_line->save(false)) {
                            }

                            if ($payment_type_id != 3) { // credit
                                $this->addpayment($model->id, $customer_id, $line_total, $company_id, $branch_id, $payment_type_id, $user_id);
                            }

                            $order_total_all += $model_line_trans->line_total;
                            $status = true;

                            // $route_type = \backend\models\Deliveryroute::findRouteType($route_id);
                            // if ($route_id == 929 || $route_id == 900) { // BKT
                            if ($route_id == 4 || $route_id == 6) {  // NKY

                                $model_current_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']])->andFilterWhere(['>=', 'date(trans_date)', $pre_date])->sum('avl_qty');
                                if ($model_current_stock > 0) {
                                    $new_avl_qty = ($model_current_stock - $datalist[$i]['qty']);

                                    \common\models\OrderStock::updateAll(['avl_qty' => 0], ['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']]);

                                    $model_boot_update_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']])->andFilterWhere(['>=', 'date(trans_date)', $pre_date])->orderBy(['id' => SORT_DESC])->one();
                                    if ($model_boot_update_stock) {
                                        $model_boot_update_stock->avl_qty = $new_avl_qty;
                                        $model_boot_update_stock->save(false);
                                    }

//

                                }
                            } else {
                                // issue order stock not boot
                                $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                if (!$model_update_order_stock) { // ถอยหลัง 1 วัน
                                    $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => $pre_date])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                }
                                //$model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id,  'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                if ($model_update_order_stock) {
                                    if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) { // stock qty ok
                                        $new_update_qty = ($model_update_order_stock->avl_qty - $datalist[$i]['qty']);
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = $new_update_qty;
                                        $model_update_order_stock->save(false);
                                    } else { // stock not ok
                                        $remain_qty = ($datalist[$i]['qty'] - $model_update_order_stock->avl_qty); // find diff remain 1
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = 0; // update to zero
                                        if ($model_update_order_stock->save(false) && $remain_qty > 0) {
                                            $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                            if ($model_update_order_stock2) {

                                                if (($model_update_order_stock2->avl_qty >= $remain_qty)) {
                                                    $model_update_order_stock2->order_id = $model->id;
                                                    $model_update_order_stock2->avl_qty = ($model_update_order_stock2->avl_qty - $remain_qty); // decrease for
                                                    $model_update_order_stock2->save(false);
                                                } else {
                                                    $remain_qty2 = (($remain_qty - $model_update_order_stock2->avl_qty)); // find diff remain 2
                                                    $model_update_order_stock2->order_id = $model->id;
                                                    $model_update_order_stock2->avl_qty = 0; // update to zero and keep keep remain
                                                    if ($model_update_order_stock2->save(false) && $remain_qty2 > 0) {
                                                        $model_update_order_stock4 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                                        if ($model_update_order_stock4) {

                                                            if (($model_update_order_stock4->avl_qty >= $remain_qty2)) {
                                                                $model_update_order_stock4->order_id = $model->id;
                                                                $model_update_order_stock4->avl_qty = ($model_update_order_stock4->avl_qty - $remain_qty); // decrease for
                                                                $model_update_order_stock4->save(false);
                                                            }
                                                        }
                                                    }
                                                }

                                            } else { // prevouse 1 day
                                                $model_update_order_stock3 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => $pre_date])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                                if ($model_update_order_stock3) {
                                                    $model_update_order_stock3->order_id = $model->id;
                                                    $model_update_order_stock3->avl_qty = ($model_update_order_stock3->avl_qty - $remain_qty); // decrease for
                                                    $model_update_order_stock3->save(false);
                                                }
                                            }
                                        }
                                    }
                                }
                                // end issue order stock
                            }

                        }
                    }
                }

                $model->order_total_amt = $order_total_all;
                $model->save(false);
            }
        }

        //  array_push($data,['data'=>$req_data]);

        return ['status' => $status, 'data' => $data];
    }

    public function actionAddordernewOriginlast() // 29/01/2023
    {
        $customer_id = null;
        $status = false;
        $user_id = 0;
        $emp_id = 0;
        $emp2_id = 0;
        $issue_id = 0;
        $route_id = 0;
        $car_id = 0;
        $company_id = 0;
        $branch_id = 0;
        $datalist = null;
        $discount = 0;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $customer_id = $req_data['customer_id'];
            $user_id = $req_data['user_id'] == null ? 1 : $req_data['user_id'];
            $emp_id = isset($req_data['emp_id']) ? $req_data['emp_id'] : 0;
            $emp2_id = isset($req_data['emp2_id']) ? $req_data['emp2_id'] : 0;
            //  $issue_id = $req_data['issue_id'];
            $route_id = $req_data['route_id'];
            $car_id = $req_data['car_id'];
            $payment_type_id = $req_data['payment_type_id'];
            $company_id = $req_data['company_id'];
            $branch_id = $req_data['branch_id'];
            $datalist = $req_data['data'];
            if (!empty($req_data['discount'])) {
                $discount = $req_data['discount'];
            }
        }

        $data = [];
        $is_free = 0;
        if ($payment_type_id == 3) {
            $is_free = 1;
        }
        if ($customer_id != null && $route_id != null) {
            //  $sale_date = date('Y/m/d');
            $sale_date = date('Y/m/d');

            $order_total_all = 0;
            $order_shift = 0;
            $has_order = null;

            $model = new \backend\models\Ordermobile();
            $model->order_no = $model->getLastNoMobileNew($sale_date, $company_id, $branch_id, $route_id);
            $model->order_date = date('Y-m-d H:i:s');
            $model->customer_id = 0;
            $model->order_channel_id = $route_id; // สายส่ง
            $model->sale_channel_id = 1; //ช่องทาง
            $model->car_ref_id = $car_id;
            $model->issue_id = $issue_id;
            $model->status = 1;
            $model->created_by = $user_id;
            $model->company_id = $company_id;
            $model->branch_id = $branch_id;
            $model->sale_from_mobile = 1;
            $model->emp_1 = $emp_id;
            $model->emp_2 = $emp2_id;
            $model->order_date2 = date('Y-m-d');
            $model->order_shift = $order_shift;
            $model->discount_amt = $discount;
            $model->payment_method_id = $payment_type_id;

            if ($payment_type_id == 2) {
                $model->payment_status = 0;
            }


            if ($model->save(false)) {

                if (count($datalist) > 0) {
                    $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                    for ($i = 0; $i <= count($datalist) - 1; $i++) {
                        if ($datalist[$i]['qty'] <= 0) continue;

                        $line_price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
                        $line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);

                        $model_line_trans = new \backend\models\Orderlinetrans();
                        $model_line_trans->order_id = $model->id;
                        $model_line_trans->customer_id = $customer_id;
                        $model_line_trans->product_id = $datalist[$i]['product_id'];
                        $model_line_trans->qty = $datalist[$i]['qty'];
                        $model_line_trans->price = $line_price;
                        $model_line_trans->line_total = $line_total;
                        $model_line_trans->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                        $model_line_trans->sale_payment_method_id = $payment_type_id;
                        $model_line_trans->issue_ref_id = $issue_id;
                        $model_line_trans->status = 1;
                        $model_line_trans->is_free = $is_free;

                        if ($model_line_trans->save(false)) {
                            $model_line = new \backend\models\Orderline();
                            $model_line->order_id = $model->id;
                            $model_line->customer_id = $customer_id;
                            $model_line->product_id = $datalist[$i]['product_id'];
                            $model_line->qty = $datalist[$i]['qty'];
                            $model_line->price = $line_price;
                            $model_line->line_total = $line_total;
                            $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                            $model_line->status = 1;
                            $model_line->sale_payment_method_id = $payment_type_id;
                            $model_line->issue_ref_id = $issue_id;
                            $model_line->is_free = $is_free;
                            if ($model_line->save(false)) {
                            }

                            if ($payment_type_id != 3) { // credit
                                $this->addpayment($model->id, $customer_id, $line_total, $company_id, $branch_id, $payment_type_id, $user_id);
                            }

                            $order_total_all += $model_line_trans->line_total;
                            $status = true;

                            // $route_type = \backend\models\Deliveryroute::findRouteType($route_id);
                            // if ($route_id == 929 || $route_id == 900) { // BKT
                            if ($route_id == 4 || $route_id == 6) {  // NKY

                                $model_current_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']])->andFilterWhere(['>=', 'date(trans_date)', $pre_date])->sum('avl_qty');
                                if ($model_current_stock > 0) {
                                    $new_avl_qty = ($model_current_stock - $datalist[$i]['qty']);

                                    \common\models\OrderStock::updateAll(['avl_qty' => 0], ['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']]);

                                    $model_boot_update_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']])->andFilterWhere(['>=', 'date(trans_date)', $pre_date])->orderBy(['id' => SORT_DESC])->one();
                                    if ($model_boot_update_stock) {
                                        $model_boot_update_stock->avl_qty = $new_avl_qty;
                                        $model_boot_update_stock->save(false);
                                    }

//

                                }
                            } else {
                                // issue order stock not boot
                                $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                if (!$model_update_order_stock) { // ถอยหลัง 1 วัน
                                    $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => $pre_date])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                }
                                //$model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id,  'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                if ($model_update_order_stock) {
                                    if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) { // stock qty ok
                                        $new_update_qty = ($model_update_order_stock->avl_qty - $datalist[$i]['qty']);
                                        if ($new_update_qty < 0) {
                                            $new_update_qty = 0;
                                        }
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = $new_update_qty;
                                        $model_update_order_stock->save(false);
                                    } else { // stock not ok
                                        $remain_qty = ($datalist[$i]['qty'] - $model_update_order_stock->avl_qty); // find diff remain 1
                                        $model_update_order_stock->order_id = $model->id;
                                        $model_update_order_stock->avl_qty = 0; // update to zero
                                        if ($model_update_order_stock->save(false) && $remain_qty > 0) {
                                            $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                            if ($model_update_order_stock2) {

                                                if (($model_update_order_stock2->avl_qty >= $remain_qty)) {
                                                    $new_update_qty2 = ($model_update_order_stock2->avl_qty - $remain_qty);
                                                    if ($new_update_qty2 < 0) {
                                                        $new_update_qty2 = 0;
                                                    }
                                                    $model_update_order_stock2->order_id = $model->id;
                                                    $model_update_order_stock2->avl_qty = $new_update_qty2;// decrease for
                                                    $model_update_order_stock2->save(false);
                                                } else {
                                                    $remain_qty2 = (($remain_qty - $model_update_order_stock2->avl_qty)); // find diff remain 2
                                                    $model_update_order_stock2->order_id = $model->id;
                                                    $model_update_order_stock2->avl_qty = 0; // update to zero and keep keep remain
                                                    if ($model_update_order_stock2->save(false) && $remain_qty2 > 0) {
                                                        $model_update_order_stock4 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                                        if ($model_update_order_stock4) {

                                                            if (($model_update_order_stock4->avl_qty >= $remain_qty2)) {
                                                                $new_update_qty4 = ($model_update_order_stock4->avl_qty - $remain_qty);
                                                                if ($new_update_qty4 < 0) {
                                                                    $new_update_qty4 = 0;
                                                                }
                                                                $model_update_order_stock4->order_id = $model->id;
                                                                $model_update_order_stock4->avl_qty = $new_update_qty4; // decrease for
                                                                $model_update_order_stock4->save(false);
                                                            }
                                                        }
                                                    }
                                                }

                                            } else { // prevouse 1 day
                                                $model_update_order_stock3 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => $pre_date])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                                if ($model_update_order_stock3) {
                                                    $model_update_order_stock3->order_id = $model->id;
                                                    $model_update_order_stock3->avl_qty = ($model_update_order_stock3->avl_qty - $remain_qty); // decrease for
                                                    $model_update_order_stock3->save(false);
                                                }
                                            }
                                        }
                                    }
                                }
                                // end issue order stock
                            }

                        }
                    }
                }

                $model->order_total_amt = $order_total_all;
                $model->save(false);
            }
        }

        //  array_push($data,['data'=>$req_data]);

        return ['status' => $status, 'data' => $data];
    }

    public function actionAddordernew() // 29/01/2023 cut transline
    {
        $customer_id = null;
        $status = false;
        $user_id = 0;
        $emp_id = 0;
        $emp2_id = 0;
        $issue_id = 0;
        $route_id = 0;
        $car_id = 0;
        $company_id = 0;
        $branch_id = 0;
        $datalist = null;
        $discount = 0;
        $route_code = "XXXX";

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $customer_id = $req_data['customer_id'];
            $user_id = $req_data['user_id'] == null ? 1 : $req_data['user_id'];
            $emp_id = isset($req_data['emp_id']) ? $req_data['emp_id'] : 0;
            $emp2_id = isset($req_data['emp2_id']) ? $req_data['emp2_id'] : 0;
            //  $issue_id = $req_data['issue_id'];
            $route_id = $req_data['route_id'];
            // $route_code = $req_data['route_code'];
            $car_id = $req_data['car_id'];
            $payment_type_id = $req_data['payment_type_id'];
            $company_id = $req_data['company_id'];
            $branch_id = $req_data['branch_id'];
            $datalist = $req_data['data'];
            if (!empty($req_data['discount'])) {
                $discount = $req_data['discount'];
            }
        }

        $data = [];
        $is_free = 0;
        if ($payment_type_id == 3) {
            $is_free = 1;
        }
        if ($customer_id != null && $route_id != null) {
            //  $sale_date = date('Y/m/d');
            $sale_date = date('Y/m/d');

            $order_total_all = 0;
            $order_shift = 0;
            $has_order = null;

            $model = new \backend\models\Ordermobile();
            // $model->order_no = $model->getLastNoMobileNew($sale_date, $company_id, $branch_id, $route_id,$route_code); // for go api
            $model->order_no = $model->getLastNoMobileNew($sale_date, $company_id, $branch_id, $route_id);
            $model->order_date = date('Y-m-d H:i:s');
            $model->customer_id = 0;
            $model->order_channel_id = $route_id; // สายส่ง
            $model->sale_channel_id = 1; //ช่องทาง
            $model->car_ref_id = $car_id;
            $model->issue_id = $issue_id;
            $model->status = 1;
            $model->created_by = $user_id;
            $model->company_id = $company_id;
            $model->branch_id = $branch_id;
            $model->sale_from_mobile = 1;
            $model->emp_1 = $emp_id;
            $model->emp_2 = $emp2_id;
            $model->order_date2 = date('Y-m-d');
            $model->order_shift = $order_shift;
            $model->discount_amt = $discount;
            $model->payment_method_id = $payment_type_id;

            if ($payment_type_id == 2) {
                $model->payment_status = 0;
            }

            if ($model->save(false)) {

                if (count($datalist) > 0) {
                    $pre_date = date('Y-m-d', strtotime(date('Y-m-d') . " -1 day"));
                    for ($i = 0; $i <= count($datalist) - 1; $i++) {
                        if ($datalist[$i]['qty'] <= 0) continue;

                        $line_price = $payment_type_id == 3 ? 0 : $datalist[$i]['price'];
                        $line_total = $payment_type_id == 3 ? 0 : ($datalist[$i]['qty'] * $datalist[$i]['price']);

                        try {
                            $model_line = new \backend\models\Orderline();
                            $model_line->order_id = $model->id;
                            $model_line->customer_id = $customer_id;
                            $model_line->product_id = $datalist[$i]['product_id'];
                            $model_line->qty = $datalist[$i]['qty'];
                            $model_line->price = $line_price;
                            $model_line->line_total = $line_total;
                            $model_line->price_group_id = $datalist[$i]['price_group_id'];//$price_group_id;
                            $model_line->status = 1;
                            $model_line->sale_payment_method_id = $payment_type_id;
                            $model_line->issue_ref_id = $issue_id;
                            $model_line->is_free = $is_free;
                            if ($model_line->save(false)) {

                                if ($payment_type_id != 3) { // credit
                                    $this->addpayment($model->id, $customer_id, $line_total, $company_id, $branch_id, $payment_type_id, $user_id);
                                }

                                $order_total_all += $line_total;
                                $status = true;

                                // $route_type = \backend\models\Deliveryroute::findRouteType($route_id);
                                if ($route_id == 929 || $route_id == 900) { // BKT
                                    // if ($route_id == 4 || $route_id == 6) {  // NKY boot

                                    $model_current_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']])->andFilterWhere(['>=', 'date(trans_date)', $pre_date])->sum('avl_qty');
                                    if ($model_current_stock > 0) {
                                        $new_avl_qty = ($model_current_stock - $datalist[$i]['qty']);

                                        \common\models\OrderStock::updateAll(['avl_qty' => 0], ['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']]);

                                        $model_boot_update_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id']])->andFilterWhere(['>=', 'date(trans_date)', $pre_date])->orderBy(['id' => SORT_DESC])->one();
                                        if ($model_boot_update_stock) {
                                            $model_boot_update_stock->avl_qty = $new_avl_qty;
                                            $model_boot_update_stock->save(false);
                                        }

                                    }
                                } else {
                                    // issue order stock not boot
                                    $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                    if (!$model_update_order_stock) { // ถอยหลัง 1 วัน
                                        $model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => $pre_date])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                    }
                                    //$model_update_order_stock = \common\models\OrderStock::find()->where(['route_id' => $route_id,  'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                    if ($model_update_order_stock) {
                                        if ($model_update_order_stock->avl_qty >= $datalist[$i]['qty']) { // stock qty ok
                                            $new_update_qty = ($model_update_order_stock->avl_qty - $datalist[$i]['qty']);
                                            if ($new_update_qty <= 0) {
                                                $new_update_qty = 0;
                                            }
                                            $model_update_order_stock->order_id = $model->id;
                                            $model_update_order_stock->avl_qty = $new_update_qty;
                                            $model_update_order_stock->save(false);
                                        } else { // stock not ok
                                            $remain_qty = ($datalist[$i]['qty'] - $model_update_order_stock->avl_qty); // find diff remain 1
                                            $model_update_order_stock->order_id = $model->id;
                                            $model_update_order_stock->avl_qty = 0; // update to zero
                                            if ($model_update_order_stock->save(false) && $remain_qty > 0) {
                                                $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                                if ($model_update_order_stock2) {
                                                    if (($model_update_order_stock2->avl_qty >= $remain_qty)) {
                                                        $new_update_qty2 = ($model_update_order_stock2->avl_qty - $remain_qty);
                                                        if ($new_update_qty2 < 0) {
                                                            $new_update_qty2 = 0;
                                                        }
                                                        $model_update_order_stock2->order_id = $model->id;
                                                        $model_update_order_stock2->avl_qty = $new_update_qty2;// decrease for
                                                        $model_update_order_stock2->save(false);
                                                    } else {
                                                        $remain_qty2 = (($remain_qty - $model_update_order_stock2->avl_qty)); // find diff remain 2
                                                        $model_update_order_stock2->order_id = $model->id;
                                                        $model_update_order_stock2->avl_qty = 0; // update to zero and keep keep remain
                                                        if ($model_update_order_stock2->save(false) && $remain_qty2 > 0) {
                                                            $model_update_order_stock4 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy(['avl_qty' => SORT_DESC])->one();
                                                            if ($model_update_order_stock4) {

                                                                if (($model_update_order_stock4->avl_qty >= $remain_qty2)) {
                                                                    $new_update_qty4 = ($model_update_order_stock4->avl_qty - $remain_qty);
                                                                    if ($new_update_qty4 < 0) {
                                                                        $new_update_qty4 = 0;
                                                                    }
                                                                    $model_update_order_stock4->order_id = $model->id;
                                                                    $model_update_order_stock4->avl_qty = $new_update_qty4; // decrease for
                                                                    $model_update_order_stock4->save(false);
                                                                }
                                                            }
                                                        }
                                                    }

                                                } else { // prevouse 1 day
                                                    $model_update_order_stock3 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $datalist[$i]['product_id'], 'date(trans_date)' => $pre_date])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
                                                    if ($model_update_order_stock3) {
                                                        $model_update_order_stock3->order_id = $model->id;
                                                        $model_update_order_stock3->avl_qty = ($model_update_order_stock3->avl_qty - $remain_qty); // decrease for
                                                        $model_update_order_stock3->save(false);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    // end issue order stock
                                }
                            }


                        } catch (Exception $exception) {
                            $status = false;
                        }

                    }
                }

                $model->order_total_amt = $order_total_all;
                $model->save(false);
            }
        }

        //  array_push($data,['data'=>$req_data]);

        return ['status' => $status, 'data' => $data];
    }

    public function getrouteqty($route_id, $product_id)
    {

        $model_update_order_stock2 = \common\models\OrderStock::find()->where(['route_id' => $route_id, 'product_id' => $product_id, 'date(trans_date)' => date('Y-m-d')])->andFilterWhere(['>', 'avl_qty', 0])->orderBy('id')->one();
        return $model_update_order_stock2;
    }

    public function actionOrderdiscount()
    {
        $status = false;
        $route_id = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        $route_id = $req_data['route_id'];

        $data = [];
        $cash_dis = 0;
        $credit_dis = 0;

        $last_shift_order = \common\models\Orders::find()->where(['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d')])->max('order_shift');
        $check_close_order = \common\models\Orders::find()->where(['order_channel_id' => $route_id, 'status' => 1, 'date(order_date)' => date('Y-m-d'), 'order_shift' => $last_shift_order])->one();

        if ($check_close_order) {
            $model_cash_amt = \common\models\Orders::find()->innerJoin('order_line', 'orders.id=order_line.order_id')->where(['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'payment_method_id' => 1])->andFilterWhere(['!=', 'order_line.status', 500])->andFilterWhere(['order_shift' => $last_shift_order])->sum('discount_amt');
            $model_credit_amt = \common\models\Orders::find()->innerJoin('order_line', 'orders.id=order_line.order_id')->where(['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'payment_method_id' => 2])->andFilterWhere(['!=', 'order_line.status', 500])->andFilterWhere(['order_shift' => $last_shift_order])->sum('discount_amt');

//            $model_cash_amt = \common\models\Orders::find()->innerJoin('order_line_trans', 'orders.id=order_line_trans.order_id')->where(['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'payment_method_id' => 1])->andFilterWhere(['!=', 'order_line_trans.status', 500])->andFilterWhere(['order_shift' => $last_shift_order])->sum('discount_amt');
//            $model_credit_amt = \common\models\Orders::find()->innerJoin('order_line_trans', 'orders.id=order_line_trans.order_id')->where(['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'payment_method_id' => 2])->andFilterWhere(['!=', 'order_line_trans.status', 500])->andFilterWhere(['order_shift' => $last_shift_order])->sum('discount_amt');


            //    $model_cash_amt = \common\models\Orders::find()->innerJoin('order_line_trans','orders.id=order_line_trans.order_id')->where(['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'payment_method_id' => 1])->andFilterWhere(['!=','order_line_trans.status',500])->sum('discount_amt');
            //    $model_credit_amt = \common\models\Orders::find()->innerJoin('order_line_trans','orders.id=order_line_trans.order_id')->where(['order_channel_id' => $route_id, 'date(order_date)' => date('Y-m-d'), 'payment_method_id' => 2])->andFilterWhere(['!=','order_line_trans.status',500])->sum('discount_amt');
            // $model = \common\models\Orders::find()->where(['id'=>131])->all();
            //  $model = \common\models\Orders::find()->where(['car_ref_id' => $car_id])->all();
            if ($model_cash_amt != null) {
                $cash_dis = $model_cash_amt;
            }
            if ($model_credit_amt != null) {
                $credit_dis = $model_credit_amt;
            }

            $status = true;
            array_push($data, [
                'discount_cash_amount' => $cash_dis,
                'discount_credit_amount' => $credit_dis,
            ]);

        } else {
            $status = true;
            array_push($data, [
                'discount_cash_amount' => 0,
                'discount_credit_amount' => 0,
            ]);
        }


        return ['status' => $status, 'data' => $data];
    }


    //// cal comision daily after close order
    ///
    public function comdailycal($id)
    {
        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }

        $car_id = 0;
        $emp_1 = 0;
        $emp_2 = 0;

        $total_com_all = 0;
        $total_qty_all = 0;
        $total_special_all = 0;
        $total_com_sum_all = 0;
        $total_amt_all = 0;
        $total_amt = 0;
        $xx = 0;

        $route_emp_count = 0;

        $model_order_mobile = \common\models\Orders::find()->select(['id', 'order_date', 'order_channel_id', 'car_ref_id', 'emp_1', 'emp_2', 'car_ref_id'])
            ->Where(['company_id' => $company_id, 'branch_id' => $branch_id, 'order_channel_id' => $id])
            ->andFilterWhere(['date(order_date)' => date('Y-m-d')])
            ->andFilterWhere(['sale_from_mobile' => 1])->all();
        // print_r($model_order_mobile);return;
        // echo count($model_order_mobile); return;
        foreach ($model_order_mobile as $value) {
            $xx++;
            $com_rate = 0;
            $route_emp_count = 0;

            $car_id = $value->car_ref_id;
            $emp_1 = $value->emp_1;
            $emp_2 = $value->emp_2;

            $route_total = null;
            $route_name = \backend\models\Deliveryroute::findName($value->order_channel_id);

            $order_data = null;
            if (substr($route_name, 0, 2) == 'CJ') {
                $order_data = $this->getOrderlineCJ($value->id, $company_id, $branch_id);
            } else {
                $order_data = $this->getOrderline($value->id, $company_id, $branch_id);
            }

            // print_r($order_data);return;


            $com_rate = 0;
            if ($emp_1 > 0) {
                $route_emp_count += 1;
            }
            if ($emp_2 > 0) {
                $route_emp_count += 1;
            }
//            if ((double)$order_data[0]['emp_1'] > 0) {
//                $route_emp_count += 1;
//            }
//            if ((double)$order_data[0]['emp_2'] > 0) {
//                $route_emp_count += 1;
//            }
            $com_rate = $this->getComRate($route_emp_count, $company_id, $branch_id);

            $total_qty_all = $total_qty_all + (double)$order_data[0]['total_qty'];
            $total_amt_all = $total_amt_all + (double)$order_data[0]['total_amt'];

            $line_com = 0;

            if (substr($route_name, 0, 2) == 'CJ') {
                if ($route_emp_count == 1) {
                    $line_com = (($order_data[0]['total_qty'] * $com_rate) * 1.75);
                } else {
//                  $line_com = $order_data[0]['total_qty'] * $com_rate;
                    $line_com = $order_data[0]['total_qty'] * $com_rate;
                }

            } else {
                $order_data_p2 = $this->getOrderlineP2($value->id, $company_id, $branch_id);
                $not_p2_qty = $order_data[0]['total_qty'];
                if ($order_data_p2 != null) {
                    $not_p2_qty = $order_data[0]['total_qty'] - $order_data_p2[0]['total_qty'];

                    $line_com = ($not_p2_qty * $com_rate) + ($order_data_p2[0]['total_qty'] * 1.75);

                } else {
                    $line_com = $order_data[0]['total_qty'] * $com_rate;
                }


                // $line_com = $order_data[0]['total_qty'] * $com_rate;
            }


            $total_amt = ($total_amt + $order_data[0]['total_amt']);

            $line_com_total = $line_com;

            $total_com_all = $total_com_all + $line_com_total;

        }

        //echo $xx;return;

        \common\models\ComDailyCal::deleteAll(['date(trans_date)' => date('Y-m-d'), 'route_id' => $id]);

        $line_special = $total_amt >= 3500 && $route_emp_count == 1 ? 30 : 0;
        $total_special_all = $total_special_all + $line_special;
        $total_com_sum_all = $total_com_sum_all + ($line_com + $line_special);

        $model_com_daily = new \common\models\ComDailyCal();
        $model_com_daily->trans_date = date('Y-m-d H:i:s');
        $model_com_daily->emp_1 = $emp_1;
        $model_com_daily->emp_2 = $emp_2;
        $model_com_daily->total_qty = $total_qty_all;
        $model_com_daily->total_amt = $total_amt;
        $model_com_daily->line_com_amt = $total_com_all;
        $model_com_daily->line_com_special_amt = $total_special_all;
        $model_com_daily->line_total_amt = $total_com_sum_all;
        $model_com_daily->created_at = time();
        $model_com_daily->route_id = $id;
        $model_com_daily->car_id = $car_id;
        $model_com_daily->company_id = $company_id;
        $model_com_daily->branch_id = $branch_id;
        if ($model_com_daily->save(false)) {
            return 1;
        }

        return 1;

    }

    public function getRouteSum($route_id, $f_date, $t_date, $company_id, $branch_id)
    {

        $data = [];
        if ($route_id != null) {
//        $sql = "SELECT t2.order_no, SUM(t3.qty) as total_qty, SUM(t3.line_total) as total_amt
//              FROM orders as t2 INNER  JOIN order_line as t3 ON t3.order_id = t2.id
//             WHERE  t2.order_date >=" . "'" . date('Y-m-d H:i:s', strtotime($f_date)) . "'" . "
//             AND t2.order_date <=" . "'" . date('Y-m-d H:i:s', strtotime($t_date)) . "'" . "
//             AND t2.order_channel_id =" . $route_id . "
//             AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

            $sql = "SELECT t2.order_no, SUM(t3.qty) as total_qty, SUM(t3.line_total) as total_amt
              FROM orders as t2 INNER  JOIN order_line as t3 ON t3.order_id = t2.id
             WHERE  (t2.order_date BETWEEN " . "'" . date('Y-m-d H:i:s', strtotime($f_date)) . "'" . "
             AND " . "'" . date('Y-m-d H:i:s', strtotime($t_date)) . "')" . "
             AND t2.order_channel_id =" . $route_id . "
             AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

            $sql .= " GROUP BY t2.order_channel_id";
            $query = \Yii::$app->db->createCommand($sql);
            $model = $query->queryAll();
            if ($model) {
                for ($i = 0; $i <= count($model) - 1; $i++) {

                    array_push($data, [
                        'total_qty' => $model[$i]['total_qty'],
                        'total_amt' => $model[$i]['total_amt'],
                    ]);
                }
            }
        }

        return $data;
    }


    public function getRouteSumCJ($route_id, $f_date, $t_date, $company_id, $branch_id)
    {

        $data = [];
        if ($route_id != null) {
            $sql = "SELECT t2.order_no, SUM(t3.qty / t4.nw) as total_qty, SUM(t3.line_total) as total_amt 
              FROM orders as t2 INNER  JOIN order_line as t3 ON t3.order_id = t2.id INNER JOIN product as t4 ON t3.product_id = t4.id
             WHERE  t2.order_date >=" . "'" . date('Y-m-d H:i:s', strtotime($f_date)) . "'" . " 
             AND t2.order_date <=" . "'" . date('Y-m-d H:i:s', strtotime($t_date)) . "'" . " 
             AND t2.order_channel_id =" . $route_id . " 
             AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

            $sql .= " GROUP BY t2.order_channel_id";
            $query = \Yii::$app->db->createCommand($sql);
            $model = $query->queryAll();
            if ($model) {

                for ($i = 0; $i <= count($model) - 1; $i++) {

                    array_push($data, [
                        'total_qty' => $model[$i]['total_qty'],
                        'total_amt' => $model[$i]['total_amt'],
                    ]);
                }
            }
        }

        return $data;
    }

    public function getComRate($count, $company_id, $branch_id)
    {
        $rate = 0;
        $model = \backend\models\Salecom::find()->where(['emp_qty' => $count, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();
        if ($model) {
            $rate = $model->com_extra;
        }
        return $rate;
    }


    public function getRouteSumAll($order_id, $company_id, $branch_id, $route_name)
    {

        $data = [];
        $sql = '';
        if ($order_id != null) {
            if ($route_name == 'CJ') {
                $sql = "SELECT  SUM(t3.qty / t4.nw) as total_qty, SUM(t3.line_total) as total_amt 
              FROM orders as t2 INNER  JOIN order_line as t3 ON t3.order_id = t2.id INNER JOIN product as t4 ON t3.product_id = t4.id
             WHERE  t2.id=" . $order_id . "
             AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

                $sql .= " GROUP BY t2.order_channel_id";
            } else {
                $sql = "SELECT SUM(t3.qty) as total_qty, SUM(t3.line_total) as total_amt
              FROM orders as t2 INNER  JOIN order_line as t3 ON t3.order_id = t2.id
             WHERE  t2.id=" . $order_id . "
             AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

                $sql .= " GROUP BY t2.order_channel_id";
            }

            $query = \Yii::$app->db->createCommand($sql);
            $model = $query->queryAll();
            if ($model) {
                for ($i = 0; $i <= count($model) - 1; $i++) {
                    array_push($data, [
                        'total_qty' => $model[$i]['total_qty'],
                        'total_amt' => $model[$i]['total_amt'],
                    ]);
                }
            }
        }

        return $data;
    }

    public function getRouteComSum($route_id, $from_date, $to_date, $company_id, $branch_id, $com_rate, $route_emp_count)
    {
        $data = [];
        $total_com_sum_all = 0;
        $total_special_all = 0;
        $order_data = getOrderline($route_id, $from_date, $company_id, $branch_id);
        for ($m = 0; $m <= count($order_data) - 1; $m++) {
            $line_special = $order_data[$m]['total_amt'] >= 3500 && $route_emp_count == 1 ? 30 : 0;
            $total_special_all = $total_special_all + $line_special;
        }
        array_push($data, ['special' => $total_special_all]);
        return $data;
    }

    public function getOrderCJ($route_id, $f_date, $t_date, $company_id, $branch_id)
    {

        $data = [];
        if ($route_id != null) {
            $sql = "SELECT date(t2.order_date) as order_date, SUM(t3.qty / t4.nw) as total_qty, SUM(t3.line_total) as total_amt 
              FROM orders as t2 INNER  JOIN order_line as t3 ON t3.order_id = t2.id INNER JOIN product as t4 ON t3.product_id = t4.id
             WHERE  t2.order_date >=" . "'" . date('Y-m-d H:i:s', strtotime($f_date)) . "'" . " 
             AND t2.order_date <=" . "'" . date('Y-m-d H:i:s', strtotime($t_date)) . "'" . " 
             AND t2.order_channel_id =" . $route_id . " 
             AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

            $sql .= " GROUP BY date(t2.order_date)";
            $sql .= " ORDER BY date(t2.order_date) ASC";
            $query = \Yii::$app->db->createCommand($sql);
            $model = $query->queryAll();
            if ($model) {
                for ($i = 0; $i <= count($model) - 1; $i++) {

                    array_push($data, [
                        //   'order_no' => $model[$i]['order_no'],
                        'order_date' => $model[$i]['order_date'],
                        'total_qty' => $model[$i]['total_qty'],
                        'total_amt' => $model[$i]['total_amt'],

                    ]);
                }
            }
        }

        return $data;
    }

    public function getOrderlineCJ($order_id, $company_id, $branch_id)
    {

        $data = [];
        if ($order_id != null) {
            $sql = "SELECT date(t2.order_date) as order_date, SUM(t3.qty / t4.nw) as total_qty, SUM(t3.line_total) as total_amt , t2.emp_1,t2.emp_2
              FROM orders as t2 INNER  JOIN order_line as t3 ON t3.order_id = t2.id INNER JOIN product as t4 ON t3.product_id = t4.id
             WHERE  t2.id=" . $order_id . "
             AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

            $sql .= " GROUP BY date(t2.order_date)";
            $sql .= " ORDER BY date(t2.order_date) ASC";
            $query = \Yii::$app->db->createCommand($sql);
            $model = $query->queryAll();
            if ($model) {
                for ($i = 0; $i <= count($model) - 1; $i++) {

                    array_push($data, [
                        //   'order_no' => $model[$i]['order_no'],
                        'order_date' => $model[$i]['order_date'],
                        'total_qty' => $model[$i]['total_qty'],
                        'total_amt' => $model[$i]['total_amt'],
                        'emp_1' => $model[$i]['emp_1'],
                        'emp_2' => $model[$i]['emp_2'],
                    ]);
                }
            }
        }

        return $data;
    }

    public function getOrderline($order_id, $company_id, $branch_id)
    {
        $data = [];
        if ($order_id != null) {
            $sql = "SELECT date(t2.order_date) as order_date, SUM((t2.line_qty_cash + t2.line_qty_credit)/t4.nw) as total_qty, SUM(t2.line_total) as total_amt, t3.emp_1,t3.emp_2
              FROM query_sale_mobile_data_new as t2 INNER  JOIN  orders as t3 ON t2.id = t3.id INNER JOIN product as t4 ON t2.product_id = t4.id
             WHERE  t2.id=" . $order_id . "
             AND t2.qty > 0  
              AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

            $sql .= " GROUP BY date(t2.order_date)";
            $sql .= " ORDER BY date(t2.order_date) ASC";
            $query = \Yii::$app->db->createCommand($sql);
            $model = $query->queryAll();
            if ($model) {
                for ($i = 0; $i <= count($model) - 1; $i++) {
                    array_push($data, [
                        //   'order_no' => $model[$i]['order_no'],
                        'order_date' => $model[$i]['order_date'],
                        'total_qty' => $model[$i]['total_qty'],
                        'total_amt' => $model[$i]['total_amt'],
                        'emp_1' => $model[$i]['emp_1'],
                        'emp_2' => $model[$i]['emp_2'],
                    ]);
                }
            }
        }

        return $data;
    }

    public function getOrderlineP2($order_id, $company_id, $branch_id)
    {
        $data = [];
        if ($order_id != null) {
            $sql = "SELECT date(t2.order_date) as order_date, SUM((t2.line_qty_cash + t2.line_qty_credit)/t4.nw) as total_qty, SUM(t2.line_total) as total_amt, t3.emp_1,t3.emp_2
              FROM query_sale_mobile_data_new as t2 INNER  JOIN  orders as t3 ON t2.id = t3.id INNER JOIN product as t4 ON t2.product_id = t4.id
             WHERE  t2.id=" . $order_id . "
          
             AND t2.qty > 0  
             AND t2.product_id = 4  
              AND t2.company_id=" . $company_id . " AND t2.branch_id=" . $branch_id;

            $sql .= " GROUP BY date(t2.order_date)";
            $sql .= " ORDER BY date(t2.order_date) ASC";
            $query = \Yii::$app->db->createCommand($sql);
            $model = $query->queryAll();
            if ($model) {
                for ($i = 0; $i <= count($model) - 1; $i++) {
                    array_push($data, [
                        //   'order_no' => $model[$i]['order_no'],
                        'total_qty' => $model[$i]['total_qty'],

                    ]);
                }
            }
        }

        return $data;
    }

    public function actionGetlastorderno()
    {

        $route_id = 0;
        $route_code = '';
        $company_id = 0;
        $branch_id = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $route_id = $req_data['route_id'];
            $route_code = $req_data['route_code'];
            $company_id = $req_data['company_id'];
            $branch_id = $req_data['branch_id'];
        }

        $data = [];

        $last_no = $this->getOrderLastno($company_id, $branch_id, $route_id, $route_code);
        array_push($data, ['last_no' => $last_no]);
        return ['status' => 1, 'data' => $data];
    }

    public function getOrderLastno($company_id, $branch_id, $route_id, $route_code)
    {
        $model = Orders::find()->where(['date(order_date)' => date('Y-m-d')])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id, 'sale_from_mobile' => 1, 'order_channel_id' => $route_id])->andFilterWhere(['like', 'order_no', 'CO'])->MAX('order_no');
        //$route_code = \backend\models\Deliveryroute::findRoutecode($route_id);
        $pre = "CO-" . $route_code;
        if ($model != null) {
            $prefix = $pre . '-' . substr(date("Y"), 2, 2) . date('m') . date('d') . '-';
            $cnum = substr((string)$model, 15, 4);
            // $len = strlen($model_seq->maximum);
            $len = strlen($cnum);
            $clen = strlen($cnum + 0);
            $loop = $len - $clen;
            // $loop = 4;

            for ($i = 1; $i <= $loop; $i++) {
                $prefix .= "0";
            }
            $prefix .= $cnum + 0;
            return $prefix;
        } else {
            return 'EMPTY';
        }
    }

    public function actionCreatenotifyclose()
    {
        $route_id = 0;
        $user_id = '';
        $company_id = 0;
        $branch_id = 0;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $req_data = \Yii::$app->request->getBodyParams();
        if ($req_data != null) {
            $route_id = $req_data['route_id'];
            $user_id = $req_data['user_id'];
            $company_id = $req_data['company_id'];
            $branch_id = $req_data['branch_id'];
        }

        // return $company_id;

        return $this->notifymessageorderclose($route_id, $user_id, $company_id, $branch_id);
    }

}
