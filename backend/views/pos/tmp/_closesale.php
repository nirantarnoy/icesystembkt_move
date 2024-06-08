<?php

use common\models\LoginLog;
use common\models\QuerySalePosData;

$this->title = 'ยอดขายสรุปประจำวันแยกกะ';

$user_id = \Yii::$app->user->id;
$default_wh = 0;
$company_id = 0;
$branch_id = 0;
if (!empty(\Yii::$app->user->identity->company_id)) {
    $company_id = \Yii::$app->user->identity->company_id;
}
if (!empty(\Yii::$app->user->identity->branch_id)) {
    $branch_id = \Yii::$app->user->identity->branch_id;
}

$default_wh = \backend\models\Warehouse::findPrimary($company_id, $branch_id);

//$user_login_time = \backend\models\User::findLogintime($user_id);
$user_login_datetime = '';
$user_logout_datetime = '';
$t_date = date('Y-m-d H:i:s');
//$model_c_login = LoginLog::find()->where(['user_id' => $user_id, 'status' => 1])->andFilterWhere(['date(login_date)' => date('Y-m-d')])->one();

$model_c_login = \common\models\LoginLogCal::find()->select('MAX(login_date) as login_date')->where(['user_id' => $user_id, 'status' => 1])->one();

//$model_c_login = LoginLog::find()->select('MIN(login_date) as login_date')->where(['user_id' => $user_id, 'status' => 1])->one();

//$model_c_login = LoginLog::find()->select('MIN(login_date) as login_date')->where(['user_id' => $user_id,'date(login_date)'=>date('Y-m-d')])->one();

if ($model_c_login) {
    $user_login_datetime = date('Y-m-d H:i:s', strtotime($model_c_login->login_date));
} else {
    $user_login_datetime = date('Y-m-d H:i:s');
}

$model_c_logout = LoginLog::find()->select('MAX(logout_date) as logout_date')->where(['user_id' => $user_id, 'status' => 2])->one();
if ($model_c_logout != null) {
    $user_logout_datetime = date('Y-m-d H:i:s', strtotime($model_c_logout->logout_date));
} else {
    $user_logout_datetime = date('Y-m-d H:i:s');
}

$is_close_status = '';
if (\Yii::$app->session->getFlash('after-save') !== null) {
    $is_close_status = 'close';
}


//echo $user_login_datetime; //return;

//$model_product_daily = \common\models\QueryProductTransDaily::find()->where(['date(trans_date)' => date('Y-m-d', strtotime($user_login_datetime))])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id])->groupBy('product_id')->all();
$model_product_daily = \common\models\Product::find()->select(['id'])->where(['status' => 1])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id])->orderBy(['id' => SORT_ASC])->all();
?>
<br/>
<div id="div1">
    <form id="form-sale-end"
          action="<?= \yii\helpers\Url::to(['pos/saledailyend'], true) ?>"
          method="post">
        <input type="hidden"
               name="close_date"
               value="<?= date('Y-m-d') ?>">
        <input type="hidden"
               name="close_from_time"
               value="<?= \backend\models\User::findLogintime($user_id) ?>">
        <input type="hidden"
               name="close_to_time"
               value="<?= date('H:i') ?>">

        <!--    <input type="submit" value="ok">-->

        <input type="hidden" id="close-status" value="<?= $is_close_status ?>">
        <input type="hidden" name="login_date" value="<?= $user_login_datetime ?>">
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-striped table-bordered" id="table-data">
                    <thead>
                    <tr>
                        <th>
                            สินค้า
                        </th>
                        <th style="text-align: right">
                            ยกมา
                        </th>
                        <th style="text-align: right">
                            ยอดผลิต
                        </th>
                        <th style="text-align: right;">โอนรถ</th>
                        <th style="text-align: right">
                            แปรสภาพ
                        </th>
                        <th style="text-align: right;">แปรสภาพรถ</th>
                        <th style="text-align: right">
                            เบิกเติม
                        </th>
                        <th style="text-align: right">
                            ขายสด
                        </th>
                        <th style="text-align: right">
                            ขายเชื่อ
                        </th>
                        <th style="text-align: right">
                            รวม
                        </th>
<!--                        <th style="text-align: right">-->
<!--                            ขายสด(เงิน)-->
<!--                        </th>-->
<!--                        <th style="text-align: right">-->
<!--                            ขายเชื่อ(เงิน)-->
<!--                        </th>-->
<!--                        <th style="text-align: right">-->
<!--                            รวมเงิน-->
<!--                        </th>-->
                        <th style="text-align: right">
                            เสีย
                        </th>
                        <th style="text-align: right">
                            ยกไป
                        </th>
                        <th style="text-align: right;width: 8%">
                            นับจริง
                        </th>
                        <th style="text-align: right;width: 8%">
                            +/-
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $total_order_cash_qty = 0;
                    $total_order_credit_qty = 0;
                    $total_order_cash_amount = 0;
                    $total_order_credit_amount = 0;
                    $total_production_qty = 0;
                    $total_balance_in = 0;
                    $total_balance_out = 0;
                    $total_issue_refill = 0;
                    $total_issue_reprocess = 0;
                    $total_production_repack_qty = 0;
                    $total_scrap_qty = 0;
                    ?>
                    <?php foreach ($model_product_daily as $value): ?>

                        <?php
//                    $model_product_status = \backend\models\Product::find()->select('status')->where(['id'=>$value->id])->one();
//                    if($model_product_status){
//                        if($model_product_status->status != 1)continue;
//                    }

                        if ($company_id == 1 && $branch_id == 2 && ($value->id == 1 || $value->id == 2 || $value->id == 3 || $value->id == 4 || $value->id == 5 || $value->id == 6 || $value->id == 8 || $value->id == 9 || $value->id == 1 || $value->id == 7 || $value->id == 15 || $value->id == 16 || $value->id == 20)) {
                            continue;
                        }
                        //  $production_rec_qty = getProdDaily($value->id, $user_login_datetime, $user_logout_datetime);
                        $production_rec_qty = getProdDaily($value->id, $user_login_datetime, $t_date, $company_id, $branch_id, $user_id);
                        $order_cash_qty = getOrderCashQty($value->id, $user_id, $user_login_datetime, $t_date);
                        $order_credit_qty = getOrderCreditQty($value->id, $user_id, $user_login_datetime, $t_date);
//
                        $total_order_cash_qty = $total_order_cash_qty + $order_cash_qty;
                        $total_order_credit_qty = $total_order_credit_qty + $order_credit_qty;
                        $total_production_qty = $total_production_qty + $production_rec_qty;

                        $order_cash_amount = getOrderCashAmount($value->id, $user_id, $user_login_datetime, $t_date);
                        $total_order_cash_amount = $total_order_cash_amount + $order_cash_amount;

                        $order_credit_amount = getOrderCreditAmount($value->id, $user_id, $user_login_datetime, $t_date);
                        $total_order_credit_amount = $total_order_credit_amount + $order_credit_amount;

                        $balance_in = getBalancein($value->id, $user_login_datetime, $t_date, $company_id, $branch_id);
                        //$balance_in = $value->id;
                        $total_balance_in = $total_balance_in + $balance_in;

                        $repack_qty = getProdRepackDaily($value->id, $user_login_datetime, $t_date);
                        $refill_qty = getIssueRefillDaily($value->id, $user_login_datetime, $t_date);
                        $issue_reprocess_qty = getIssueReprocessDaily($value->id, $user_login_datetime, $t_date, $default_wh);

                        $scrap_qty = getScrapDaily($value->id, $user_login_datetime, $t_date);
                        $total_scrap_qty = $total_scrap_qty + $scrap_qty;

                        $line_balance_out = ($balance_in + $production_rec_qty + $repack_qty) - ($order_cash_qty + $order_credit_qty) - $refill_qty - $issue_reprocess_qty - $scrap_qty;
                        $total_balance_out = $total_balance_out + $line_balance_out;
                        $total_issue_refill = $total_issue_refill + $refill_qty;
                        $total_issue_reprocess = $total_issue_reprocess + $issue_reprocess_qty;
                        $total_production_repack_qty = $total_production_repack_qty + $repack_qty;
//
//                    $balance_in = getBalancein($value->id);
//                    $balance_in_id = 0;
//                    $balance_in_qty = 0;
//
//                    if ($balance_in != null) {
//                        $balance_in_id = $balance_in[0]['id'];
//                        $balance_in_qty = $balance_in[0]['qty'] == null ? 0 : $balance_in[0]['qty'];
//                    }
//
//                    $order_cash_amount = 0;
//                    $order_credit_amount = 0;
//
//                    $total_balance_in = $total_balance_in + $balance_in_qty;
//
//                    $balance_out = ($production_rec_qty + $balance_in_qty) - ($order_cash_qty + $order_credit_qty);
//                    $total_balance_out = $total_balance_out + $balance_out;

                        $line_count = getDailycount($value->id, $company_id, $branch_id, $t_date);
                        ?>
                        <tr>
                            <td style="text-align: left;vertical-align: middle">
                                <input type="hidden"
                                       name="line_prod_id[]"
                                       value="<?= $value->id ?>">
                                <?= \backend\models\Product::findName($value->id) ?>
                            </td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_balance_in_id"
                                       value="<?= 0 ?>">
                                <input type="hidden"
                                       name="line_balance_in[]"
                                       value="<?= $balance_in ?>">
                                <?= number_format($balance_in, 2) ?>
                            </td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_production_qty[]"
                                       value="<?= $production_rec_qty ?>">
                                <?= number_format($production_rec_qty, 2) ?>
                            </td>
                            <td style="text-align: right;">0</td>

                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_repack_qty[]"
                                       value="<?= $repack_qty ?>">
                                <?= number_format($repack_qty, 2) ?>
                            </td>
                            <td style="text-align: right;">0</td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_refill_qty[]"
                                       value="<?= ($refill_qty + $issue_reprocess_qty) ?>">
                                <?= number_format(($refill_qty + $issue_reprocess_qty), 2) ?>
                            </td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_cash_qty[]"
                                       value="<?= $order_cash_qty ?>">
                                <?= number_format($order_cash_qty, 2) ?>
                            </td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_credit_qty[]"
                                       value="<?= $order_credit_qty ?>">
                                <?= number_format($order_credit_qty, 2) ?>
                            </td>
                            <td style="text-align: right;background-color: #99c5de;vertical-align: middle">
                                <?= number_format($order_cash_qty + $order_credit_qty, 2) ?>
                                <input type="hidden" name="line_cash_amount[]" value="<?= $order_cash_amount ?>">
                                <input type="hidden" name="line_credit_amount[]" value="<?= $order_credit_amount ?>">
                            </td>
<!--                            <td style="text-align: right;vertical-align: middle">-->
<!--                                <input type="hidden"-->
<!--                                       name="line_cash_amount[]"-->
<!--                                       value="--><?//= $order_cash_amount ?><!--">-->
<!--                                --><?//= number_format($order_cash_amount, 2) ?>
<!--                            </td>-->
<!--                            <td style="text-align: right;vertical-align: middle">-->
<!--                                <input type="hidden"-->
<!--                                       name="line_credit_amount[]"-->
<!--                                       value="--><?//= $order_credit_amount ?><!--">-->
<!--                                --><?//= number_format($order_credit_amount, 2) ?>
<!--                            </td>-->
<!--                            <td style="text-align: right;background-color: #99c5de;vertical-align: middle">-->
<!--                                --><?//= number_format($order_cash_amount + $order_credit_amount, 2) ?>
<!--                            </td>-->
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_scrap_qty[]"
                                       value="<?= $scrap_qty ?>">
                                <?= number_format($scrap_qty, 2) ?>
                            </td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden"
                                       name="line_balance_out[]"
                                       class="line-balance-out"
                                       value="<?= $line_balance_out ?>">
                                <?= number_format($line_balance_out, 2) ?>
                            </td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="number" min="0" style="text-align: right" readonly
                                       name="line_stock_count[]" value="<?= $line_count ?>"
                                       class="form-control line-stock-count" onchange="line_cal($(this));">
                            </td>
                            <td style="text-align: right;vertical-align: middle">
                                <input type="hidden" class="line-diff" name="line_diff[]"
                                       value="<?= ($line_count - $line_balance_out) ?>">
                                <input type="text" class="form-control line-diff-qty"
                                       value="<?= ($line_count - $line_balance_out) ?>" readonly>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr style="background-color: #99c5de">
                        <td></td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_balance_in, 2) ?>
                        </td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_production_qty, 2) ?>
                        </td>
                        <td style="text-align: right;">0</td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_production_repack_qty, 2) ?>
                        </td>
                        <td style="text-align: right;">0</td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format(($total_issue_refill + $total_issue_reprocess), 2) ?>
                        </td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_order_cash_qty, 2) ?>
                        </td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_order_credit_qty, 2) ?>
                        </td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_order_credit_qty + $total_order_cash_qty, 2) ?>
                        </td>
<!--                        <td style="text-align: right;font-weight: bold">-->
<!--                            --><?//= number_format($total_order_cash_amount, 2) ?>
<!--                        </td>-->
<!--                        <td style="text-align: right;font-weight: bold">-->
<!--                            --><?//= number_format($total_order_credit_amount, 2) ?>
<!--                        </td>-->
<!--                        <td style="text-align: right;font-weight: bold">-->
<!--                            --><?//= number_format($total_order_cash_amount + $total_order_credit_amount, 2) ?>
<!--                        </td>-->
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_scrap_qty, 2) ?>
                        </td>
                        <td style="text-align: right;font-weight: bold">
                            <?= number_format($total_balance_out, 2); ?>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <br/>

        <div class="row">
            <div class="col-lg-3">
                <h4>
                    <span> <i class="fa fa-clock text-warning"></i> </span>ตั้งแต่
                    <span style="color: #ec4844"><?= date('d/m/Y H:i', strtotime($user_login_datetime)) ?></span>
                </h4>
            </div>
            <div class="col-lg-3">
                <h4>
                    <span> <i class="fa fa-clock"></i> </span>ถึง
                    <span
                            style="color: #ec4844"><?= date('d/m/Y H:i') ?></span>
                </h4>
            </div>
            <div class="col-lg-3">
                <h4>
                    <span> <i class="fa fa-user-circle text-primary"></i> </span>พนักงาน
                    <span style="color: #ec4844"><?= \backend\models\User::findName(\Yii::$app->user->id) ?></span>
                </h4>
            </div>

        </div>

        <br/>
        <div class="row">
            <div class="col-lg-3">
                <?php $caspay = getPaymentcashAll($user_login_datetime, $t_date, $company_id, $branch_id);?>
                <h4><span><i class="fa fa-money text-success"></i></span> <span>รวมรับชำระเงินสด :  <?=number_format($caspay,2)?></span></h4>
            </div>
            <div class="col-lg-3">
                <h4><span><i class="fa fa-money text-success"></i></span> <span>รวมรับชำระเงินโอน :  <?=number_format(getPaymentbankAll($user_login_datetime, $t_date, $company_id, $branch_id),2)?></span></h4>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-bordered table-strip'">
                    <thead>
                    <tr>
                        <td>ยอดขายสด</td>
                        <td>ยอดขายเชื่อ</td>
                        <td>รวม</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?=number_format($total_order_cash_amount, 2)?>
                        </td>
                        <td>
                            <?= number_format($total_order_credit_amount, 2) ?>
                        </td>
                        <td>
                            <?=number_format($total_order_cash_amount + $total_order_credit_amount, 2) ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <br />
        <table class="table table-bordered">
            <tr>
                <td><h4>รวมส่งเงิน</h4></td>
                <td><?=number_format(($total_order_cash_amount + $caspay), 2)?></td>
            </tr>
        </table>
        <br />
        <div class="row"
             style="text-align: left">
            <div class="col-lg-12">
                <div class="btn btn-success btn-lg btn-save"
                     onclick="submittotal($(this));">
                    <i class="fa fa-save"></i>
                    บันทึกปิดการขาย
                </div>
            </div>
        </div>
        <br />

    </form>
</div>
<br/>
<table width="100%" class="table-title">
    <td style="text-align: right">
        <button id="btn-export-excel" class="btn btn-secondary">Export Excel</button>
        <button id="btn-print" class="btn btn-warning" onclick="printContent('div1')">Print</button>
    </td>
</table>
<?php
function getBalancein($product_id, $user_login_datetime, $t_date, $company_id, $branch_id)
{
    $qty = 0;
    if ($product_id != null) {
        $model = \common\models\BalanceDaily::find()->where(['product_id' => $product_id, 'company_id' => $company_id, 'branch_id' => $branch_id])->one();
        if ($model) {
            $qty = $model->balance_qty;
        }
    }

    return $qty;
}

function getProdDaily($product_id, $user_login_datetime, $t_date, $company_id, $branch_id, $user_id)
{
    $qty = 0;
    $cancel_qty = 0;
    $second_user_id = [];
    if ($product_id != null) {

        $model_login = \common\models\LoginLogCal::find()->where(['user_id' => $user_id])->orderBy(['id' => SORT_DESC])->one();
        if ($model_login) {
            //  $second_user_id = $model_login->second_user_id;
            $model_user_ref = \common\models\LoginUserRef::find()->select('user_id')->where(['login_log_cal_id' => $model_login->id])->all();
            if ($model_user_ref) {
                foreach ($model_user_ref as $value) {
                    array_push($second_user_id, $value->user_id);
                }
            }
        }

        if (count($second_user_id) > 0) {
            $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 15, 'production_type' => 1, 'company_id' => $company_id, 'branch_id' => $branch_id, 'created_by' => $second_user_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
            // $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id'=>$company_id,'branch_id'=>$branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
            $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id' => $company_id, 'branch_id' => $branch_id, 'created_by' => $second_user_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['and', ['>=', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime))], ['<=', 'trans_date', date('Y-m-d H:i:s', strtotime($t_date))]])->sum('qty');
        } else {
            $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 15, 'production_type' => 1, 'company_id' => $company_id, 'branch_id' => $branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
            // $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id'=>$company_id,'branch_id'=>$branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
            $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id' => $company_id, 'branch_id' => $branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['and', ['>=', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime))], ['<=', 'trans_date', date('Y-m-d H:i:s', strtotime($t_date))]])->sum('qty');
        }

    }

    return $qty - $cancel_qty; // ลบยอดยกเลิกผลิต
    //return $cancel_qty; // ลบยอดยกเลิกผลิต
}

function getProdDaily2($product_id, $user_login_datetime, $t_date, $company_id, $branch_id)
{
    $qty = 0;
    $cancel_qty = 0;
    $new_login_date = null;
    if ($product_id != null) {


        $postion_name = \backend\models\Employee::findPositionName(\Yii::$app->user->id);
        $check_shift = \common\models\LoginLogCal::find()->where(['>', 'login_date', date('Y-m-d', strtotime($user_login_datetime))])->andFilterWhere(['=', 'user_id', 10])->one();
        if ($check_shift) {
            $new_login_date = date('Y-m-d H:i:s', strtotime($check_shift->login_date));
        }

        if ($new_login_date != null) {
            $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 15, 'production_type' => 1, 'company_id' => $company_id, 'branch_id' => $branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date3', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($new_login_date))])->sum('qty');
            // $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id'=>$company_id,'branch_id'=>$branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
            $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id' => $company_id, 'branch_id' => $branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($new_login_date))])->sum('qty');
        } else {
            $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 15, 'production_type' => 1, 'company_id' => $company_id, 'branch_id' => $branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
            // $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id'=>$company_id,'branch_id'=>$branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
            $cancel_qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 28, 'production_type' => 28, 'company_id' => $company_id, 'branch_id' => $branch_id])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['and', ['>=', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime))], ['<=', 'trans_date', date('Y-m-d H:i:s', strtotime($t_date))]])->sum('qty');
        }


    }

    return $qty - $cancel_qty; // ลบยอดยกเลิกผลิต
    //return $cancel_qty; // ลบยอดยกเลิกผลิต
}

function getProdRepackDaily($product_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($product_id != null) {
        $qty = \backend\models\Stocktrans::find()->where(['in', 'activity_type_id', [26, 27]])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
    }

    return $qty;
}

function getIssueRefillDaily($product_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($product_id != null) {
        $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 18])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
    }

    return $qty;
}

function getIssueReprocessDaily($product_id, $user_login_datetime, $t_date, $default_wh)
{
    $qty = 0;
    if ($product_id != null) {
        $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 20])->andFilterWhere(['product_id' => $product_id, 'warehouse_id' => $default_wh])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
    }

    return $qty;
}

function getScrapDaily($product_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($product_id != null) {
        $qty = \backend\models\Scrap::find()->join('inner join', 'scrap_line', 'scrap_line.scrap_id = scrap.id')->where(['scrap_line.product_id' => $product_id])->andFilterWhere(['between', 'scrap.trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('scrap_line.qty');
    }

    return $qty;
}

//function getOrderCashQty($product_id, $user_id, $user_login_datetime, $t_date)
//{
//    $qty = 0;
//    if ($user_id != null) {
//        $qty = \common\models\QuerySaleDataSummary::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->andFilterWhere(['LIKE', 'name', 'สด'])->sum('qty');
//    }
//
//    return $qty;
//}
function getOrderCashQty($product_id, $user_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($user_id != null) {
        $qty = \common\models\QuerySalePosData::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('line_qty_cash');
    }
    return $qty;
}

function getOrderCreditQty($product_id, $user_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($user_id != null) {
        $qty = \common\models\QuerySalePosData::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('line_qty_credit');
    }
    return $qty;
}

//function getOrderCreditQty($product_id, $user_id, $user_login_datetime, $t_date)
//{
//    $qty = 0;
//    if ($user_id != null) {
//        $qty = \common\models\QuerySaleDataSummary::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->andFilterWhere(['LIKE', 'name', 'เครดิต'])->sum('qty');
//    }
//    return $qty;
//}

//function getOrderCashAmount($product_id, $user_id, $user_login_datetime, $t_date)
//{
//    $qty = 0;
//    if ($user_id != null) {
//        $qty = \common\models\QuerySalePosPayDaily::find()->where(['created_by' => $user_id])->andFilterWhere(['between', 'payment_date', $user_login_datetime, $t_date])->andFilterWhere(['LIKE', 'name', 'สด'])->sum('payment_amount');
//    }
//
//    return $qty;
//}
function getOrderCashAmount($product_id, $user_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($user_id != null) {
        $qty = \common\models\QuerySalePosData::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('line_total_cash');
    }
    return $qty;
}

//function getOrderCashAmount($product_id, $user_id, $user_login_datetime, $t_date)
//{
//    $qty = 0;
//    if ($user_id != null) {
//        $qty = \common\models\QuerySaleDataSummary::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->andFilterWhere(['LIKE', 'name', 'สด'])->sum('line_total');
//    }
//    return $qty;
//}

function getOrderCreditAmount($product_id, $user_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($user_id != null) {
        $qty = \common\models\QuerySalePosData::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('line_total_credit');
    }
    return $qty;
}

//function getOrderCreditAmount($product_id, $user_id, $user_login_datetime, $t_date)
//{
//    $qty = 0;
//    if ($user_id != null) {
//        $qty = \common\models\QuerySaleDataSummary::find()->where(['created_by' => $user_id, 'product_id' => $product_id])->andFilterWhere(['between', 'order_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->andFilterWhere(['LIKE', 'name', 'เครดิต'])->sum('line_total');
//    }
//    return $qty;
//}


//function getBalancein($product_id)
//{
//    $data = [];
//    if ($product_id != null) {
//        $model = \common\models\SaleBalanceOut::find()->where(['product_id' => $product_id, 'status' => 1])->one();
//        if ($model) {
//            array_push($data, ['id' => $model->id, 'qty' => $model->balance_out]);
//        }
//    }
//    return $data;
//}

function getDailycount($product_id, $company_id, $branch_id, $t_date)
{
    $qty = 0;
    if ($product_id != null && $company_id != null && $branch_id != null) {
        $model = \common\models\DailyCountStock::find()->where(['product_id' => $product_id, 'company_id' => $company_id, 'branch_id' => $branch_id, 'status' => 0])->andFilterWhere(['date(trans_date)' => date('Y-m-d', strtotime($t_date))])->all();
        if ($model) {
            foreach ($model as $value) {
//                $production_bill_id = \backend\models\Stockjournal::findProdrecId($value->journal_no,$company_id,$branch_id);
//                $model_sale_after_count_qty = \common\models\IssueStockTemp::find()->where(['prodrec_id'=>$production_bill_id,'product_id'=>$product_id])->sum('qty');
//                $qty += ($value->qty - $model_sale_after_count_qty);

                $qty += $value->qty;
            }

        }
    }
    return $qty;
}


function getPaymentAll($f_date, $t_date, $company_id, $branch_id)
{
    $amount = 0;

//    $sql = "SELECT SUM(t1.payment_amount) as amount
//              from query_payment_receive as t1 INNER JOIN orders as t3 ON t1.order_id = t3.id
//              WHERE (t1.trans_date >= " . "'" . date('Y-m-d H:i', strtotime($f_date)) . "'" . "
//              AND t1.trans_date <= " . "'" . date('Y-m-d H:i', strtotime($t_date)) . "'" . " )
//              AND t1.status <> 100
//              AND t3.sale_channel_id=2
//              AND t1.company_id=" . $company_id . " AND t1.branch_id=" . $branch_id;


    $sql = "SELECT SUM(t1.payment_amount) as amount from query_payment_receive as t1
              WHERE (t1.trans_date>= " . "'" . date('Y-m-d H:i', strtotime($f_date)) . "'" . " 
              AND t1.trans_date <= " . "'" . date('Y-m-d H:i', strtotime($t_date)) . "'" . " )
              AND t1.status <> 100 
              AND t1.payment_method_id=2
              AND t1.company_id=" . $company_id . " AND t1.branch_id=" . $branch_id;



    $sql .= " GROUP BY date(t1.trans_date)";
    $query = \Yii::$app->db->createCommand($sql);
    $model = $query->queryAll();
    if ($model) {
        for ($i = 0; $i <= count($model) - 1; $i++) {
            $amount = $model[$i]['amount'];
        }
    }
    return $amount;
}
function getPaymentcashAll($f_date, $t_date, $company_id, $branch_id)
{
    $amount = 0;
    $sql = "SELECT SUM(t1.payment_amount) as amount from query_payment_receive as t1
              WHERE (t1.trans_date>= " . "'" . date('Y-m-d H:i', strtotime($f_date)) . "'" . " 
              AND t1.trans_date <= " . "'" . date('Y-m-d H:i', strtotime($t_date)) . "'" . " )
              AND t1.status <> 100 
              AND t1.payment_method_id=2
              AND t1.payment_channel_id = 1
              AND isnull(t1.route_id)
              AND t1.company_id=" . $company_id . " AND t1.branch_id=" . $branch_id;



    $sql .= " GROUP BY date(t1.trans_date),t1.payment_channel_id";
    $query = \Yii::$app->db->createCommand($sql);
    $model = $query->queryAll();
    if ($model) {
        for ($i = 0; $i <= count($model) - 1; $i++) {
            $amount = $model[$i]['amount'];
        }
    }
    return $amount;
}
function getPaymentbankAll($f_date, $t_date, $company_id, $branch_id)
{
    $amount = 0;
    $sql = "SELECT SUM(t1.payment_amount) as amount from query_payment_receive as t1
              WHERE (t1.trans_date>= " . "'" . date('Y-m-d H:i', strtotime($f_date)) . "'" . " 
              AND t1.trans_date <= " . "'" . date('Y-m-d H:i', strtotime($t_date)) . "'" . " )
              AND t1.status <> 100 
              AND t1.payment_method_id=2
              AND t1.payment_channel_id = 2
              AND isnull(t1.route_id)
              AND t1.company_id=" . $company_id . " AND t1.branch_id=" . $branch_id;



    $sql .= " GROUP BY date(t1.trans_date),t1.payment_channel_id";
    $query = \Yii::$app->db->createCommand($sql);
    $model = $query->queryAll();
    if ($model) {
        for ($i = 0; $i <= count($model) - 1; $i++) {
            $amount = $model[$i]['amount'];
        }
    }
    return $amount;
}

?>

<?php
$this->registerJsFile(\Yii::$app->request->baseUrl . '/js/jquery.table2excel.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$js = <<<JS
  $(function(){
     var x = $("#close-status").val();
     if(x!=''){
         $(".btn-save").hide();
     }else{
         $(".btn-save").show();
     }
     
     
     $("#btn-export-excel").click(function(){
          $("#table-data").table2excel({
            // exclude CSS class
            exclude: ".noExl",
            name: "Excel Document Name"
          });
        });
     
  });
  function submittotal(e){
    if(confirm('คุณต้องการทำรายการนี้ใช่หรือไม่ ?')){
        $("form#form-sale-end").submit();
    }
}
function line_cal(e){
      var real_stock = e.val();
      var balance_out = e.closest('tr').find('.line-balance-out').val();
      var line_diff = parseFloat(real_stock) - parseFloat(balance_out);
      e.closest('tr').find('.line-diff-qty').val(line_diff);
      e.closest('tr').find('.line-diff').val(line_diff);
}
function printContent(el)
      {
         var restorepage = document.body.innerHTML;
         var printcontent = document.getElementById(el).innerHTML;
         document.body.innerHTML = printcontent;
         window.print();
         document.body.innerHTML = restorepage;
     }
JS;

$this->registerJs($js, static::POS_END);
?>
