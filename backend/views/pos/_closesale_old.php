<?php

use common\models\LoginLog;
use common\models\QuerySalePosData;

$this->title = 'ยอดขายสรุปประจำวันแยกกะ';

$user_id = \Yii::$app->user->id;

$company_id = 1;
$branch_id = 1;
if (!empty(\Yii::$app->user->identity->company_id)) {
    $company_id = \Yii::$app->user->identity->company_id;
}
if (!empty(\Yii::$app->user->identity->branch_id)) {
    $branch_id = \Yii::$app->user->identity->branch_id;
}

$user_login_time = \backend\models\User::findLogintime($user_id);
$user_login_datetime = '';
$user_logout_datetime = '';
$t_date = date('Y-m-d H:i:s');
//$model_c_login = LoginLog::find()->where(['user_id' => $user_id, 'status' => 1])->andFilterWhere(['date(login_date)' => date('Y-m-d')])->one();
$model_c_login = LoginLog::find()->select('MIN(login_date) as login_date')->where(['user_id' => $user_id, 'status' => 1])->one();
if ($model_c_login != null) {
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
if(\Yii::$app->session->getFlash('after-save') !== null){
    $is_close_status = 'close';
}


//echo $user_login_datetime; //return;

$model_product_daily = \common\models\QueryProductTransDaily::find()->where(['date(trans_date)' => date('Y-m-d', strtotime($user_login_datetime))])->andFilterWhere(['company_id' => $company_id, 'branch_id' => $branch_id])->all();
?>
<br/>
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

    <input type="hidden" id="close-status" value="<?=$is_close_status?>">

    <br/>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>
                        สินค้า
                    </th>
                    <th style="text-align: right">
                        ยอดยกมา
                    </th>
                    <th style="text-align: right">
                        ยอดผลิต
                    </th>
                    <th style="text-align: right">
                        แปรสภาพ
                    </th>
                    <th style="text-align: right">
                        เบิกเติม
                    </th>
                    <th style="text-align: right">
                        ขายสด(จำนวน)
                    </th>
                    <th style="text-align: right">
                        ขายเชื่อ(จำนวน)
                    </th>
                    <th style="text-align: right">
                        รวมจำนวน
                    </th>
                    <th style="text-align: right">
                        ขายสด(เงิน)
                    </th>
                    <th style="text-align: right">
                        ขายเชื่อ(เงิน)
                    </th>
                    <th style="text-align: right">
                        รวมเงิน
                    </th>
                    <th style="text-align: right">
                        เสีย
                    </th>
                    <th style="text-align: right">
                        ยอดยกไป
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
                $total_production_repack_qty = 0;
                $total_scrap_qty = 0;
                ?>
                <?php foreach ($model_product_daily as $value): ?>
                    <?php
                  //  $production_rec_qty = getProdDaily($value->product_id, $user_login_datetime, $user_logout_datetime);
                   $production_rec_qty = getProdDaily($value->product_id, $user_login_datetime, $t_date);
                    $order_cash_qty = getOrderCashQty($value->product_id, $user_id, $user_login_datetime, $t_date);
                    $order_credit_qty = getOrderCreditQty($value->product_id, $user_id, $user_login_datetime, $t_date);
//
                    $total_order_cash_qty = $total_order_cash_qty + $order_cash_qty;
                    $total_order_credit_qty = $total_order_credit_qty + $order_credit_qty;
                    $total_production_qty = $total_production_qty + $production_rec_qty;

                    $order_cash_amount = getOrderCashAmount($value->product_id, $user_id, $user_login_datetime, $t_date);
                    $total_order_cash_amount = $total_order_cash_amount + $order_cash_amount;

                    $order_credit_amount = getOrderCreditAmount($value->product_id, $user_id, $user_login_datetime, $t_date);
                    $total_order_credit_amount = $total_order_credit_amount + $order_credit_amount;

                    $balance_in = getBalancein($value->product_id, $user_login_datetime, $t_date);
                    $total_balance_in = $total_balance_in + $balance_in;

                    $repack_qty = getProdRepackDaily($value->product_id, $user_login_datetime, $t_date);
                    $refill_qty = getIssueRefillDaily($value->product_id, $user_login_datetime, $t_date);

                    $scrap_qty = getScrapDaily($value->product_id, $user_login_datetime, $t_date);
                    $total_scrap_qty = $total_scrap_qty + $scrap_qty;

                    $line_balance_out = ($balance_in + $production_rec_qty + $repack_qty) - ($order_cash_qty + $order_credit_qty) - $refill_qty - $scrap_qty;
                    $total_balance_out = $total_balance_out + $line_balance_out;
                    $total_issue_refill = $total_issue_refill + $refill_qty;
                    $total_production_repack_qty = $total_production_repack_qty + $repack_qty;
//
//                    $balance_in = getBalancein($value->product_id);
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
                    ?>
                    <tr>
                        <td style="text-align: left">
                            <input type="hidden"
                                   name="line_prod_id[]"
                                   value="<?= $value->product_id ?>">
                            <?= \backend\models\Product::findName($value->product_id) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_balance_in_id"
                                   value="<?= 0 ?>">
                            <input type="hidden"
                                   name="line_balance_in[]"
                                   value="<?= $balance_in ?>">
                            <?= number_format($balance_in,2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_production_qty[]"
                                   value="<?= $production_rec_qty ?>">
                            <?= number_format($production_rec_qty,2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_repack_qty[]"
                                   value="<?= $repack_qty ?>">
                            <?= number_format($repack_qty,2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_refill_qty[]"
                                   value="<?= $refill_qty ?>">
                            <?= number_format($refill_qty, 2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_cash_qty[]"
                                   value="<?= $order_cash_qty ?>">
                            <?= number_format($order_cash_qty, 2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_credit_qty[]"
                                   value="<?= $order_credit_qty ?>">
                            <?= number_format($order_credit_qty, 2) ?>
                        </td>
                        <td style="text-align: right;background-color: #99c5de">
                            <?= number_format($order_cash_qty + $order_credit_qty, 2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_cash_amount[]"
                                   value="<?= $order_cash_amount ?>">
                            <?= number_format($order_cash_amount, 2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_credit_amount[]"
                                   value="<?= $order_credit_amount ?>">
                            <?= number_format($order_credit_amount, 2) ?>
                        </td>
                        <td style="text-align: right;background-color: #99c5de">
                            <?= number_format($order_cash_amount + $order_credit_amount, 2) ?>
                        </td>
                        <td style="text-align: right;">
                            <input type="hidden"
                                   name="line_scrap_qty[]"
                                   value="<?= $scrap_qty ?>">
                            <?= number_format($scrap_qty, 2) ?>
                        </td>
                        <td style="text-align: right">
                            <input type="hidden"
                                   name="line_balance_out[]"
                                   value="<?= $line_balance_out ?>">
                            <?= number_format($line_balance_out, 2) ?>
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
                    <td style="text-align: right;font-weight: bold">
                        <?= number_format($total_production_repack_qty, 2) ?>
                    </td>
                    <td style="text-align: right;font-weight: bold">
                        <?= number_format($total_issue_refill, 2) ?>
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
                    <td style="text-align: right;font-weight: bold">
                        <?= number_format($total_order_cash_amount, 2) ?>
                    </td>
                    <td style="text-align: right;font-weight: bold">
                        <?= number_format($total_order_credit_amount, 2) ?>
                    </td>
                    <td style="text-align: right;font-weight: bold">
                        <?= number_format($total_order_cash_amount + $total_order_credit_amount, 2) ?>
                    </td>
                    <td style="text-align: right;font-weight: bold">
                        <?= number_format($total_scrap_qty, 2) ?>
                    </td>
                    <td style="text-align: right;font-weight: bold">
                        <?= number_format($total_balance_out, 2); ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-lg-3">
            <h4>
                ตั้งแต่
                <span style="color: #ec4844"><?= date('d/m/Y H:i', strtotime($user_login_datetime)) ?></span>
            </h4>
        </div>
        <div class="col-lg-3">
            <h4>
                ถึง
                <span
                        style="color: #ec4844"><?= date('d/m/Y H:i') ?></span>
            </h4>
        </div>
        <div class="col-lg-3">
            <h4>
                พนักงาน
                <span style="color: #ec4844"><?= \backend\models\User::findName(\Yii::$app->user->id) ?></span>
            </h4>
        </div>
    </div>
    <hr/>
    <div class="row"
         style="text-align: right">
        <div class="col-lg-2">
            <h5>
                ยอดยกมา</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   value="<?= number_format($total_balance_in, 2) ?>"
                   readonly
                   name="balance_in">
        </div>
        <div class="col-lg-2">
            <h5>
                ยอดผลิต</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="today_production_qty"
                   value="<?= number_format($total_production_qty, 2) ?>"
                   readonly>
        </div>
        <div class="col-lg-2">
            <h5>
                รับเข้าแปรสภาพ</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_return_qty"
                   value="<?= number_format($total_production_repack_qty,2) ?>"
                   readonly>
        </div>
    </div>
    <div style="height: 10px;"></div>
    <div class="row"
         style="text-align: right">
        <div class="col-lg-2">
            <h5>
                ขายสด(จำนวน)</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_cash_qty"
                   value="<?= number_format($total_order_cash_qty, 2) ?>"
                   readonly>
        </div>
        <div class="col-lg-2">
            <h5>
                ขายสด(เงิน)</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_cash_amount"
                   value="<?= number_format($total_order_cash_amount, 2) ?>"
                   readonly>
        </div>
        <div class="col-lg-2">
            <h5>
                เบิกเติม</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_refill_qty"
                   value="<?= number_format($total_issue_refill, 2) ?>"
                   readonly>
        </div>
    </div>
    <div style="height: 10px;"></div>
    <div class="row"
         style="text-align: right">
        <div class="col-lg-2">
            <h5>
                ขายเชื่อ(จำนวน)</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_credit_qty"
                   value="<?= number_format($total_order_credit_qty, 2) ?>"
                   readonly>
        </div>
        <div class="col-lg-2">
            <h5>
                ขายเชื่อ(เงิน)</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_credit_amount"
                   value="<?= number_format($total_order_credit_amount, 2) ?>"
                   readonly>
        </div>
    </div>
    <div style="height: 10px;"></div>
    <div class="row"
         style="text-align: right">
        <div class="col-lg-2">
            <h5>
                ขายทั้งหมด(จำนวน)</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_qty"
                   value="<?= number_format($total_order_cash_qty + $total_order_credit_qty, 2) ?>"
                   readonly>
        </div>
        <div class="col-lg-2">
            <h5>
                ขายทั้งหมด(เงิน)</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_amount"
                   value="<?= number_format($total_order_credit_amount + $total_order_cash_amount, 2) ?>"
                   readonly>
        </div>
    </div>
    <div style="height: 10px;"></div>
    <div class="row"
         style="text-align: right">
        <div class="col-lg-2">
            <h5>
                ยอดยกไป</h5>
        </div>
        <div class="col-lg-2">
            <input type="text"
                   class="form-control"
                   name="order_qty"
                   value="<?= number_format($total_balance_out, 2) ?>"
                   readonly>
        </div>
    </div>
    <br/>
    <hr/>

    <div class="row"
         style="text-align: center">
        <div class="col-lg-12">
            <div class="btn btn-success btn-lg btn-save"
                 onclick="submittotal($(this));">
                <i class="fa fa-save"></i>
                บันทึกปิดการขาย
            </div>
        </div>
    </div>
</form>
<?php
function getBalancein($product_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($product_id != null) {
        $model = \common\models\BalanceDaily::find()->where(['product_id' => $product_id])->one();
        if($model){
            $qty = $model->balance_qty;
        }
    }

    return $qty;
}
function getProdDaily($product_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($product_id != null) {
        $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 15])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
    }

    return $qty;
}

function getProdRepackDaily($product_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($product_id != null) {
        $qty = \backend\models\Stocktrans::find()->where(['activity_type_id' => 21])->andFilterWhere(['product_id' => $product_id])->andFilterWhere(['between', 'trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('qty');
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

function getScrapDaily($product_id, $user_login_datetime, $t_date)
{
    $qty = 0;
    if ($product_id != null) {
        $qty = \backend\models\Scrap::find()->join('inner join','scrap_line','scrap_line.scrap_id = scrap.id')->where(['scrap_line.product_id' => $product_id])->andFilterWhere(['between', 'scrap.trans_date', date('Y-m-d H:i:s', strtotime($user_login_datetime)), date('Y-m-d H:i:s', strtotime($t_date))])->sum('scrap_line.qty');
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

?>

<?php
$js = <<<JS
  $(function(){
     var x = $("#close-status").val();
     if(x!=''){
         $(".btn-save").hide();
     }else{
         $(".btn-save").show();
     }
  });
  function submittotal(e){
    if(confirm('คุณต้องการทำรายการนี้ใช่หรือไม่ ?')){
        $("form#form-sale-end").submit();
    }
}
JS;

$this->registerJs($js, static::POS_END);
?>
