<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class AdminreportController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionCardaily()
    {

        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }
        $from_date = \Yii::$app->request->post('from_date');
        $to_date = \Yii::$app->request->post('to_date');
        $find_emp_id = \Yii::$app->request->post('find_emp_id');
        $print_type = \Yii::$app->request->post('print_type');

            return $this->render('_cardaily', [
                'from_date' => $from_date,
                'to_date' => $to_date,
                'company_id' => $company_id,
                'branch_id' => $branch_id,
                'find_emp_id' => $find_emp_id,
                'print_type' => $print_type
            ]);


    }

    public function actionCardaily2()
    {

        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }
        $from_date = \Yii::$app->request->post('from_date');
        $to_date = \Yii::$app->request->post('to_date');
        $find_emp_id = \Yii::$app->request->post('find_emp_id');
        $print_type = \Yii::$app->request->post('print_type');


        return $this->render('_cardaily_new', [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'find_emp_id' => $find_emp_id,
            'print_type' => $print_type
        ]);


    }

    public function actionCardailyamount()
    {

        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }
        $from_date = \Yii::$app->request->post('from_date');
        $to_date = \Yii::$app->request->post('to_date');
        $find_emp_id = \Yii::$app->request->post('find_emp_id');
        return $this->render('_cardailyamount', [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'find_emp_id' => $find_emp_id
        ]);
    }

    public function actionPosdaily()
    {

        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }
        $from_date = \Yii::$app->request->post('from_date');
        $to_date = \Yii::$app->request->post('to_date');
        $find_emp_id = \Yii::$app->request->post('find_emp_id');
        return $this->render('_posdailyqty2', [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'find_emp_id' => $find_emp_id
        ]);
    }

    public function actionSummaryall()
    {
        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }
        $from_date = \Yii::$app->request->post('from_date');
        $to_date = \Yii::$app->request->post('to_date');
        $find_product_id = \Yii::$app->request->post('find_product_id');
        $data_type_selected = \Yii::$app->request->post('data_type_selected');

        if ($find_product_id == null || $find_product_id == '') {
            $find_product_id = 1;
        }
        return $this->render('_summaryall', [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'find_product_id' => $find_product_id,
            'data_type_selected' => $data_type_selected,
        ]);
    }

    public function actionEditsummary()
    {

        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }
        $from_date = \Yii::$app->request->post('search_from_date');
        $to_date = \Yii::$app->request->post('search_to_date');
        $trans_date = \Yii::$app->request->post('trans_date');
        $shift_seq = \Yii::$app->request->post('shift_seq');
        $find_product_id = \Yii::$app->request->post('product_id');

        $prod_rec_qty = \Yii::$app->request->post('prod_rec_qty');
        $transfer_qty = \Yii::$app->request->post('transfer_qty');
        $return_qty = \Yii::$app->request->post('return_qty');
        $scrap_qty = \Yii::$app->request->post('scrap_qty');
        $counting_qty = \Yii::$app->request->post('counting_qty');
        $refill_qty = \Yii::$app->request->post('refill_qty');
        $reprocess_qty = \Yii::$app->request->post('reprocess_qty');
        $data_type_selected = \Yii::$app->request->post('data_type_selected');
        $balance_in_qty = \Yii::$app->request->post('balance_in_qty');
        $sale_qty = \Yii::$app->request->post('sale_qty');

        $cash_qty = \Yii::$app->request->post('cash_qty');
        $credit_qty = \Yii::$app->request->post('credit_qty');
        $issue_car_qty = \Yii::$app->request->post('issue_car_qty');
        $issue_transfer_qty = \Yii::$app->request->post('issue_transfer_qty');

        if ($find_product_id == null || $find_product_id == '') {
            $find_product_id = 1;
        }

//        echo 'cash_qty: '.$cash_qty;return;

        // if ($prod_rec_qty || $return_qty || $scrap_qty || $counting_qty) {
        $model_shift_all = \common\models\TransactionPosSaleSum::find()->select(['distinct(shift)', 'trans_date'])->where(['date(trans_date)' => date('Y-m-d', strtotime($trans_date))])->all();
        // $model_shift_all = \common\models\TransactionPosSaleSum::find()->where(['>=','id',25663])->all();
        if ($model_shift_all) {

            $loop = -1;
            foreach ($model_shift_all as $value) {
                $loop += 1;
//                    if ($loop == $shift_seq) {
//                        $model_update = \common\models\TransactionPosSaleSum::find()->where(['shift'=>$value->shift,'product_id'=>$find_product_id])->one();
//                        if($model_update){
//                            $model_update->prodrec_qty = $prod_rec_qty;
//                            $model_update->return_qty = $return_qty;
//                            $model_update->scrap_qty = $scrap_qty;
//                            $model_update->counting_qty = $counting_qty;
//                            $model_update->save(false);
//                        }
//                    }
                if ($loop == $shift_seq) {
                    $model_update = \common\models\CloseDailyAdjust::find()->where(['shift' => $value->shift, 'product_id' => $find_product_id])->one();
                    if ($model_update) {
                        $model_update->shift_seq = $loop;
                        if ($transfer_qty != null || $transfer_qty != '') {
                            $model_update->transfer_qty = (float)$transfer_qty;
                        }
                        if ($prod_rec_qty != null || $prod_rec_qty != '') {
                            $model_update->prodrec_qty = (float)$prod_rec_qty;
                        }
                        if ($return_qty != null || $return_qty != '') {
                            $model_update->return_qty = (float)$return_qty;
                        }
                        if ($scrap_qty != null || $scrap_qty != '') {
                            $model_update->scrap_qty = (float)$scrap_qty;
                        }
                        if ($counting_qty != null || $counting_qty != '') {
                            $model_update->counting_qty = (float)$counting_qty;
                        }
                        if ($refill_qty != null || $refill_qty != '') {
                            $model_update->refill_qty = (float)$refill_qty;
                        }
                        if ($reprocess_qty != null || $reprocess_qty != '') {
                            $model_update->reprocess_qty = (float)$reprocess_qty;
                        }
                        if ($balance_in_qty != null || $balance_in_qty != '') {
                            $model_update->balance_in_qty = (float)$balance_in_qty;
                        }
                        if ($sale_qty != null || $sale_qty != '') {
                            $model_update->sale_qty = (float)$sale_qty;
                        }

                        if ($cash_qty != null || $cash_qty != '') {
                            $model_update->cash_qty = (float)$cash_qty;
                           // echo 'has cash: '.$cash_qty;return;
                        }

                        if ($credit_qty != null || $credit_qty != '') {
                            $model_update->credit_qty = (float)$credit_qty;
                        }

                        if ($issue_car_qty != null || $issue_car_qty != '') {
                            $model_update->issue_car_qty = (float)$issue_car_qty;
                        }

                        if ($issue_transfer_qty != null || $issue_transfer_qty != '') {
                            $model_update->issue_transfer_qty = (float)$issue_transfer_qty;
                        }

                       // echo 'cash_qtyxป: '.$cash_qty;return;
                        $model_update->save(false);
                    } else {
                        $model_new = new \common\models\CloseDailyAdjust();
                        $model_new->product_id = $find_product_id;
                        $model_new->emp_id = 0;
                        $model_new->shift_seq = $loop;
                        $model_new->trans_date = date('Y-m-d H:i:s');
                        $model_new->shift = $value->shift;
                        $model_new->shift_date = date('Y-m-d H:i:s', strtotime($value->trans_date));
                        if ($transfer_qty != null || $transfer_qty != '') {
                            $model_new->transfer_qty = (float)$transfer_qty;
                        }
                        if ($prod_rec_qty != null || $prod_rec_qty != '') {
                            $model_new->prodrec_qty = (float)$prod_rec_qty;
                        }
                        if ($return_qty != null || $return_qty != '') {
                            $model_new->return_qty = (float)$return_qty;
                        }
                        if ($scrap_qty != null || $scrap_qty != '') {
                            $model_new->scrap_qty = (float)$scrap_qty;
                        }
                        if ($counting_qty != null || $counting_qty != '') {
                            $model_new->counting_qty = (float)$counting_qty;
                        }
                        if ($refill_qty != null || $refill_qty != '') {
                            $model_new->refill_qty = (float)$refill_qty;
                        }
                        if ($reprocess_qty != null || $reprocess_qty != '') {
                            $model_new->reprocess_qty = (float)$reprocess_qty;
                        }
                        if ($balance_in_qty != null || $balance_in_qty != '') {
                            $model_new->balance_in_qty = (float)$balance_in_qty;
                        }
                        if ($sale_qty != null || $sale_qty != '') {
                            $model_new->sale_qty = (float)$sale_qty;
                        }

                        if ($cash_qty != null || $cash_qty != '') {
                            $model_new->cash_qty = (float)$cash_qty;
                        }

                        if ($credit_qty != null || $credit_qty != '') {
                            $model_new->credit_qty = (float)$credit_qty;
                        }

                        if ($issue_car_qty != null || $issue_car_qty != '') {
                            $model_new->issue_car_qty = (float)$issue_car_qty;
                        }

                        if ($issue_transfer_qty != null || $issue_transfer_qty != '') {
                            $model_new->issue_transfer_qty = (float)$issue_transfer_qty;
                        }
//                            $model_new->transfer_qty = (float)$transfer_qty;
//                            $model_new->prodrec_qty = (float)$prod_rec_qty;
//                            $model_new->return_qty = (float)$return_qty;
//                            $model_new->scrap_qty = (float)$scrap_qty;
//                            $model_new->counting_qty = (float)$counting_qty;
//                            $model_new->refill_qty = (float)$refill_qty;
//                            $model_new->reprocess_qty = (float)$reprocess_qty;
                        $model_new->save(false);
                    }

//                        if($counting_qty > 0){
//                            $model_updatex = \common\models\TransactionPosSaleSum::find()->where(['shift' => $value->shift, 'product_id' => $find_product_id])->one();
//                            if ($model_updatex) {
//                                $model_updatex->counting_qty = (float)$counting_qty;
//                                if($model_updatex->save(false)){
//                                    $model_update_balance_in = \common\models\TransactionPosSaleSum::find()->where(['shift' => ($value->shift + 1), 'product_id' => $find_product_id])->one();
//                                    if($model_update_balance_in){
//                                        $model_update_balance_in->balance_in_qty = (float)$counting_qty;
//                                        $model_update_balance_in->save(false);
//                                    }
//                                }
//                            }
//                        }
                }
                //echo $value->shift . ' '. date('Y-m-d',strtotime($value->trans_date)) . "<br />";
            }
        }
        // echo $shift_seq;
        //return;
        //}


        return $this->render('_summaryall', [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'find_product_id' => $find_product_id,
            'data_type_selected' => $data_type_selected,
        ]);
    }

    public function actionAddlinenote()
    {
        $route_id = \Yii::$app->request->post('route_id');
        $add_line_date = \Yii::$app->request->post('add_line_date');
        $add_amount = \Yii::$app->request->post('add_amount');
        $cash_transfer_amount = \Yii::$app->request->post('cash_transfer_amount');

        if ($route_id) {
            //  echo $add_line_date;return;
            $ex = explode(' ', $add_line_date);
            $ex2 = null;
            $t_date = null;
            if ($ex != null) {
                $ex2 = explode('-', $ex[0]);

                //   print_r($ex2);return;
                if ($ex2 != null) {
                    if (count($ex2) > 1) {
                        $t_date = $ex2[2] . '-' . $ex2[1] . '-' . $ex2[0];
                    }

                }
            }

            if ($t_date != null) {
                $check_has = \common\models\DeliveryNotFullPay::find()->where(['route_id' => $route_id, 'date(trans_date)' => date('Y-m-d', strtotime($t_date))])->one();
                if (!$check_has) {
                    $model = new \common\models\DeliveryNotFullPay();
                    $model->route_id = $route_id;
                    $model->cash_transfer_amount = $cash_transfer_amount;
                    $model->not_full_amount = $add_amount;
                    $model->trans_date = date('Y-m-d', strtotime($t_date));
                    $model->save(false);
                }else{
                    $check_has->not_full_amount = $add_amount;
                    $check_has->cash_transfer_amount = $cash_transfer_amount;
                    $check_has->save(false);
                }
            }
        }

        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }

        return $this->render('_cardailyamount', [
            'from_date' => date('Y-m-d', strtotime($t_date)),
            'to_date' => date('Y-m-d', strtotime($t_date)),
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'find_emp_id' => 0
        ]);
    }
    public function actionPrintallsummary(){
        $company_id = 0;
        $branch_id = 0;

        if (!empty(\Yii::$app->user->identity->company_id)) {
            $company_id = \Yii::$app->user->identity->company_id;
        }
        if (!empty(\Yii::$app->user->identity->branch_id)) {
            $branch_id = \Yii::$app->user->identity->branch_id;
        }

        $from_date = \Yii::$app->request->post('from_date');
        $to_date = \Yii::$app->request->post('to_date');
        $find_sale_type = \Yii::$app->request->post('find_sale_type');
        $find_user_id = \Yii::$app->request->post('find_user_id');
        $is_invoice_req = \Yii::$app->request->post('is_invoice_req');
        $btn_order_type = \Yii::$app->request->post('btn_order_type');

        return $this->render('_print_sale_all_summary',[
            'from_date' => $from_date,
            'to_date' => $to_date,
            'find_sale_type' => $find_sale_type,
            'find_user_id' => $find_user_id,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'is_invoice_req' => $is_invoice_req,
            'btn_order_type'=>$btn_order_type,
        ]);
    }

    public function actionPrintcarcj()
    {
        $from_date = \Yii::$app->request->post('from_date');
        $to_date = \Yii::$app->request->post('to_date');
        $route_id = \Yii::$app->request->post('route_id');
        return $this->render('_cardaily_cj',[
            'from_date' => $from_date,
            'to_date' => $to_date,
            'route_id' => $route_id
        ]);
    }
}