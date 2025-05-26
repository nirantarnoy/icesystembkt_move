<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\AssetsitemSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="assetsitem-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="row">
        <div class="col-lg-3">
            <!--         <span class="input-group-addon" id="basic-addon1"><i class="fa fa-search"></i></span>-->
            <?= $form->field($model, 'globalSearch')->textInput(['placeholder' => 'ค้นหา', 'class' => 'form-control', 'aria-describedby' => 'basic-addon1'])->label(false) ?>

        </div>
        <div class="col-lg-3">
            <?=$form->field($model, 'route_id')->widget(Select2::classname(), [
                    'data'=>\yii\helpers\ArrayHelper::map(\backend\models\Deliveryroute::find()->where(['status'=>1])->all(),'id','name'),
                'options'=>[
                        'placeholder'=>'--เลือกสายส่ง--',
                        'onchange'=>'this.form.submit();',
                ],
            ])->label(false);
            ?>
        </div>
        <div class="col-lg-3">
            <?php
            $check_role = \backend\models\User::checkhasrole(\Yii::$app->user->id, 'System Administrator');
            if ($check_role) {
                echo \kartik\select2\Select2::widget([
                    'value' => $viewstatus,
                    'name' => 'viewstatus',
                    'data' => \yii\helpers\ArrayHelper::map(\backend\helpers\ViewstatusType::asArrayObject(), 'id', 'name'),
                    'options' => [
                        'onchange' => 'this.form.submit();',
                    ],
                ]);
            }

            ?>
        </div>
        <div class="col-lg-3">
            <?php
            $check_role = \backend\models\User::checkhasrole(\Yii::$app->user->id, 'System Administrator');
            if ($check_role) {
                echo \kartik\select2\Select2::widget([
                    'value' => $viewstatus2,
                    'name' => 'viewstatus2',
                    'data' => \yii\helpers\ArrayHelper::map(\backend\helpers\ViewstatusType2::asArrayObject(), 'id', 'name'),
                    'options' => [
                        'onchange' => 'this.form.submit();',
                    ],
                ]);
            }

            ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
