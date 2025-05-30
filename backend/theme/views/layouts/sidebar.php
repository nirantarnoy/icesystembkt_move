<aside class="main-sidebar sidebar-dark-blue elevation-4">
    <!-- Brand Logo -->
    <a href="index.php?r=site/index" class="brand-link">
<!--        <img src="--><?php //echo Yii::$app->request->baseUrl; ?><!--/uploads/logo/logo_bp.jpg" alt="Mind account"-->
<!--             class="brand-image img-circle elevation-3"-->
<!--             style="opacity: .8">-->
        <span class="brand-text font-weight-light">BKT ICE</span>
<!--        <span class="brand-text font-weight-light">TARAICE</span>-->
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="index.php?r=site/index" class="nav-link site">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            ภาพรวมระบบ
                            <!--                                <i class="right fas fa-angle-left"></i>-->
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-building"></i>
                        <p>
                            ข้อมูลบริษัท
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('company/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=company/index" class="nav-link company">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>บริษัท</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('branch/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=branch" class="nav-link branch">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        สาขา
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php if (\Yii::$app->user->can('mainconfig/index')): ?>
                    <li class="nav-item has-treeview has-sub">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>
                                ตั้งค่าทั่วไป
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="index.php?r=mainconfig" class="nav-link mainconfig">
                                    <i class="far fa-file-import nav-icon"></i>
                                    <p>Import Master</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="index.php?r=sequence" class="nav-link sequence">
                                    <i class="far fa-file-import nav-icon"></i>
                                    <p>เลขที่เอกสาร</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            ที่จัดเก็บสินค้า
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('warehouse/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=warehouse/index" class="nav-link warehouse">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>คลังสินค้า</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('location/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=location" class="nav-link location">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        Location
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('productionrec/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=productionrec" class="nav-link productionrec">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        บันทึกเข้าคลัง
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('issuerefill/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=issuerefill" class="nav-link issuerefill">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        เบิกสินค้าเติม
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('adjustment/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adjustment" class="nav-link adjustment">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        ปรับสต๊อก
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_group_id'])): ?>
                            <?php if ($_SESSION['user_group_id'] == 1): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=branchtransfer" class="nav-link branchtransfer">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>
                                            โอนระหว่างสาขา
                                            <!--                                <span class="right badge badge-danger">New</span>-->
                                        </p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('transform/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=transform" class="nav-link transform">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        เบิกแปรสภาพ
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('stocktrans/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=stocktrans" class="nav-link stocktrans">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        ประวัติรับเข้า-ออกคลัง
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('stocksum/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=stocksum" class="nav-link stocksum">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        สินค้าคงเหลือ
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('prodrecreport/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=prodrecreport" class="nav-link prodrecreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รายงานรับเข้าผลิต
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('prodrecreportsum/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=prodrecreportsum/index" class="nav-link prodrecreportsum">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รายงานรับเข้าผลิตรวม
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('reprocessreport/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=reprocessreport/index" class="nav-link reprocessreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รับเข้าผลิต Re pack
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('issuereport/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=issuereport" class="nav-link issuereport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รายงานจ่ายสินค้า
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('orderissue/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=orderissue" class="nav-link orderissue">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รายงานขาย+จ่าย
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('scrapreport/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=scrapreport" class="nav-link scrapreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รายงานสินค้าเสีย
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('dailycount/print')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=dailycount/print" class="nav-link dailycount">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รายงานนับสต๊อก
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('assetsitem/print')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=assetsitem/print" class="nav-link assetsitem">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        รายงานเช็คถัง
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cubes"></i>
                        <p>
                            ข้อมูลสินค้า
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('producttype/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=producttype/index" class="nav-link producttype">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ประเภทสินค้า</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('productgroup/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=productgroup" class="nav-link productgroup">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        กลุ่มสินค้า
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('unit/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=unit" class="nav-link unit">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        หน่วยนับ
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('product/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=product" class="nav-link product">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        สินค้า
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('pricegroup/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=pricegroup" class="nav-link pricegroup">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        ราคามาตรฐาน
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('assetsitem/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=assetsitem" class="nav-link assetsitem">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        ถัง/กระสอบ
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            ลูกค้า
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('customergroup/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=customergroup/index" class="nav-link customergroup">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>กลุ่มลูกค้า</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('customertype/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=customertype/index" class="nav-link customertype">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ประเภทลูกค้า</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('customer/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=customer" class="nav-link customer">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>
                                        ลูกค้า
                                        <!--                                <span class="right badge badge-danger">New</span>-->
                                    </p>
                                </a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </li>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>
                            จัดการใบสั่งขาย
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('paymentmethod/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=paymentmethod/index" class="nav-link paymentmethod">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>วิธีชำระเงิน</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('paymentterm/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=paymentterm/index" class="nav-link paymentterm">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>เงื่อนไขชำระเงิน</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('pos/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=pos/index" class="nav-link pos">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ขาย POS</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('orders/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=orders/index2" class="nav-link orders">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ใบสั่งขาย</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('journalissue/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=journalissue/index" class="nav-link journalissue">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>เบิกสินค้า</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('paymentreceive/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=paymentreceive/customerloanprint" class="nav-link paymentreceive">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ค้างชำระหน้าบ้าน</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('paymentreceivecar/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=paymentreceivecar/customerloanprint"
                                   class="nav-link paymentreceivecar">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ค้างชำระสายส่ง</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('customerinvoice/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=customerinvoice/index" class="nav-link customerinvoice">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>วางบิล</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('customerinvoice/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=customerinvoicecash/index" class="nav-link customerinvoicecash">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>วางบิลเงินสด</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('paymentreceive/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=paymentreceive/index" class="nav-link paymentreceive">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ชำระหนี้</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('paymentrechistory/printcarpayment')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=paymentrechistory/printcarpayment"
                                   class="nav-link paymentrechistory">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ประวัติชำระหนี้</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('paymentposrechistory/printpospayment')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=paymentposrechistory/printpospayment"
                                   class="nav-link paymentposrechistory">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ประวัติชำระหนี้ POS </p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <!--                        <li class="nav-item">-->
                        <!--                            <a href="index.php?r=closeorder" class="nav-link closeorder">-->
                        <!--                                <i class="far fa-circlez nav-icon"></i>-->
                        <!--                                <p>จบการขาย</p>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                        <?php if (\Yii::$app->user->can('dailysum/indexnew')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=dailysum/indexnew" class="nav-link dailysum">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>สรุปการขาย</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php //if (\Yii::$app->user->can('pos/manageclose')): ?>
                        <?php if (\backend\models\User::checkhasrole(\Yii::$app->user->id, 'System Administrator')): ?>

                            <!--                            3-->
                            <li class="nav-item">
                                <a href="index.php?r=pos/manageclose" class="nav-link manageclose">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>แก้ไขปิดกะ</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('pos/printsummary')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=pos/printsummary" class="nav-link printsummary">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ยอดขายแยกสินค้า</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('pos/printcarsummary')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=pos/printcarsummary" class="nav-link printcarsummary">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ยอดขายแยกสายส่ง</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('ordercarcredit/printcarsummary')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=ordercarcredit/printcarsummary" class="nav-link ordercarcredit">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ยอดขายเชื่อสายส่ง</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('adminreport/cardailyamount')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminreport/cardailyamount" class="nav-link adminreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รายงานสายส่งประจำวัน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('pos/printsummarycarnky')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=pos/printsummarycarnky" class="nav-link pos">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>แยกสินค้าสายส่ง</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('pos/printsummaryposnky')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=pos/printsummaryposnky" class="nav-link pos">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>แยกสินค้าหน้าบ้าน</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>
                            จัดการขนส่ง
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('deliveryroute/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=deliveryroute/index" class="nav-link deliveryroute">
                                    <i class="far fa-route nav-icon"></i>
                                    <p>เส้นทางขนส่ง</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('cartype/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=cartype/index" class="nav-link cartype">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ประเภทรถ</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('salegroup/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=salegroup/index" class="nav-link salegroup">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>กลุ่มสายส่ง</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('car/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=car/index" class="nav-link car">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ข้อมูลรถ</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('cardaily/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=cardaily/index" class="nav-link cardaily">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ข้อมูลรถประจำวัน</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('cardaily/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=transferbranch/index" class="nav-link transferbranch">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>สาขารับโอน</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>
                            พนักงาน/คอมมิชชั่น
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('position/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=position/index" class="nav-link position">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ตำแหน่ง</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('employee/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=employee/index" class="nav-link employee">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>พนักงาน</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('salecom/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=salecom/index" class="nav-link salecom">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ตั้งค่าคอมฯ</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('salecomcon/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=salecomcon/index" class="nav-link salecomcon">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>เงื่อนไขพิเศษ</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <!--                        <li class="nav-item">-->
                        <!--                            <a href="index.php?r=salereportemp/empcomlist" class="nav-link salereport">-->
                        <!--                                <i class="far fa-circlez nav-icon"></i>-->
                        <!--                                <p>รายงานคอมมิชชั่น</p>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                        <!--                        <li class="nav-item">-->
                        <!--                            <a href="index.php?r=salereportemp/empcomnew" class="nav-link salereportemp">-->
                        <!--                                <i class="far fa-circlez nav-icon"></i>-->
                        <!--                                <p>รายงานคอมฯใหม่</p>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                    </ul>
                </li>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>
                            การผลิต
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <!--                        <li class="nav-item">-->
                        <!--                            <a href="index.php?r=reprocess/index" class="nav-link reprocess">-->
                        <!--                                <i class="far fa-circlez nav-icon"></i>-->
                        <!--                                <p>Re process</p>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                        <!--                        <li class="nav-item">-->
                        <!--                            <a href="index.php?r=ordercollection/index" class="nav-link ordercollection">-->
                        <!--                                <i class="far fa-circlez nav-icon"></i>-->
                        <!--                                <p>สรุปคำสั่งซื้อ</p>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                        <?php if (\Yii::$app->user->can('plan/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=machine/index" class="nav-link machine">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>เครื่องผลิต</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (\Yii::$app->user->can('plan/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=plan/index" class="nav-link plan">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>แผนการผลิต</p>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (\Yii::$app->user->can('production/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=production/index" class="nav-link production">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ใบสั่งผลิต</p>
                                </a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </li>
                <?php if (\Yii::$app->user->can('customertaxinvoice/index')): ?>
                    <li class="nav-item has-treeview has-sub">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-money-bill"></i>
                            <p>
                                สดย่อ
                                <i class="fas fa-angle-left right"></i>
                                <!--                                <span class="badge badge-info right">6</span>-->
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (\Yii::$app->user->can('customertaxinvoice/index')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=customertaxinvoice" class="nav-link customertaxinvoice">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>ใบกำกับสด-ย่อ</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (\Yii::$app->user->can('customertaxinvoice/printcheck')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=customertaxinvoice/printcheck" class="nav-link customertaxinvoice">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>รายงานสรุป</p>
                                    </a>
                                </li>
                            <?php endif;?>

                        </ul>
                    </li>
                <?php endif;?>
                <li class="nav-item has-treeview has-sub">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            รายงาน
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if (\Yii::$app->user->can('salecomreport/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=salecomreport" class="nav-link salecomreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รายงานค่าคอมฯ</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('salecomreport/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=salecomreport/index3" class="nav-link salecomreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รายงานค่าคอม(แยก)</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('salereport/index')): ?>
                        <li class="nav-item">
                            <a href="index.php?r=salereport" class="nav-link salereport">
                                <i class="far fa-circlez nav-icon"></i>
                                <p>รายงานขายแยกตามสายส่ง</p>
                            </a>
                        </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('adminreport/cardaily')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminreport/cardaily" class="nav-link adminreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รายงานสายส่งประจำวัน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('adminreport/posdaily')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminreport/posdaily" class="nav-link adminreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รายงานหน้าบ้าน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('adminrecal/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminrecal/index" class="nav-link adminrecal">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ประมวลขายหน้าบ้าน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('adminreport/summaryall')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminreport/summaryall" class="nav-link adminreport">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ประวัติสรุปขาย</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('adminreportreturn/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminreportreturn/index" class="nav-link adminreportreturn">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รายงานยอดคืน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('adminreportreturn/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminreportreturn/index2" class="nav-link adminreportreturn">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ยอดคืนแยกสายส่ง</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('pos/printpossummary')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=pos/printpossummary" class="nav-link pos">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>ยอดขายหน้าบ้าน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('adminreportcarissue/index2')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=adminreportcarissue/index2" class="nav-link adminreportcarissue">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>สรุปเบิกสายส่ง</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('salereportemp/index')): ?>
<!--                        <li class="nav-item">-->
<!--                            <a href="index.php?r=salereportemp" class="nav-link salereportemp">-->
<!--                                <i class="far fa-circlez nav-icon"></i>-->
<!--                                <p>รายงานขายแยกตามพนักงาน</p>-->
<!--                            </a>-->
<!--                        </li>-->
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('routesummarybystdgroup/index2')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=routesummarybystdgroup/index2" class="nav-link routesummarybystdgroup">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รวมยอดประจำวัน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('salereportdistributor/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=salereportdistributor/index" class="nav-link salereportdistributor">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>รายงานสรุปขายยี่ปั๊วะ</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('saleqtysumbyprice/index')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=saleqtysumbyprice/index2" class="nav-link saleqtysumbyprice">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>แคทหน้าบ้าน</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <?php if (\Yii::$app->user->can('saleqtysumbyprice/index2')): ?>
                            <li class="nav-item">
                                <a href="index.php?r=saleqtysumbyprice/index2" class="nav-link saleqtysumbyprice">
                                    <i class="far fa-circlez nav-icon"></i>
                                    <p>แคทขายรถ</p>
                                </a>
                            </li>
                        <?php endif;?>
                        <!--                        <li class="nav-item">-->
                        <!--                            <a href="index.php?r=report" class="nav-link report">-->
                        <!--                                <i class="far fa-circlez nav-icon"></i>-->
                        <!--                                <p>รายรับ</p>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                    </ul>
                </li>
                <?php // if (isset($_SESSION['user_group_id'])): ?>
                <?php //if ($_SESSION['user_group_id'] == 1): ?>
                <?php //if (\Yii::$app->user->identity->username == 'iceadmin'): ?>
                    <li class="nav-item has-treeview has-sub">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-user"></i>
                            <p>
                                ผู้ใช้งาน
                                <i class="fas fa-angle-left right"></i>
                                <!--                                <span class="badge badge-info right">6</span>-->
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (\Yii::$app->user->can('usergroup/index')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=usergroup" class="nav-link usergroup">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>กลุ่มผู้ใช้งาน</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if (\Yii::$app->user->can('user/index')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=user" class="nav-link user">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>ผู้ใช้งาน</p>
                                    </a>
                                </li>
                            <?php endif;?>

                            <?php if (\Yii::$app->user->can('authitem/index')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=authitem" class="nav-link auth">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>สิทธิ์การใช้งาน</p>
                                    </a>
                                </li>
                            <?php endif;?>
                            <?php if (\Yii::$app->user->can('user/printlogindaily')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=user/printlogindaily" class="nav-link user">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>เช็คเข้าระบบ</p>
                                    </a>
                                </li>
                            <?php endif;?>
                            <?php if (\Yii::$app->user->can('computerlist/index')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=computerlist" class="nav-link computerlist">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>เครื่องใช้งาน</p>
                                    </a>
                                </li>
                            <?php endif;?>
                            <?php if (\Yii::$app->user->can('admintools/index')): ?>
                                <li class="nav-item">
                                    <a href="index.php?r=admintools" class="nav-link admintools">
                                        <i class="far fa-circlez nav-icon"></i>
                                        <p>Admin tools</p>
                                    </a>
                                </li>
                            <?php endif;?>
                        </ul>
                    </li>
                <?php if (\Yii::$app->user->can('dbbackup/backuplist')): ?>
                    <li class="nav-item has-treeview has-sub">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                สำรองข้อมูล
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="index.php?r=dbbackup/backuplist" class="nav-link dbbackup">
                                    <i class="far fa-file-archive nav-icon"></i>
                                    <p>สำรองข้อมูล</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="index.php?r=dbrestore/restorepage" class="nav-link dbrestore">
                                    <i class="fa fa-upload nav-icon"></i>
                                    <p>กู้คืนข้อมูล</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif;?>
                <?php //endif; ?>
                <?php //endif; ?>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

