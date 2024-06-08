<?php
$this->title = 'Import Master Data';
?>
<div class="row">
    <form action="index.php?r=mainconfig/importcustomer" method="post" enctype="multipart/form-data">
        <div class="col-lg-10">
            <label for="">นำเข้าข้อมูลลูกค้า</label><br/>
            <input type="file" class="file-customer" name="file_customer" accept=".csv">
        </div>
        <div class="col-lg-2">
            <label for=""></label><br/>
            <input type="submit" class="btn btn-primary" value="นำเข้า">
        </div>
    </form>
</div>
<hr style="border-top: 1px dashed">
<div class="row">
    <form action="index.php?r=mainconfig/importemployee" method="post" enctype="multipart/form-data">
        <div class="col-lg-10">
            <label for="">นำเข้าข้อมูลพนักงาน</label><br/>
            <input type="file" class="file-employee" name="file_employee" accept=".csv">
        </div>
        <div class="col-lg-2">
            <label for=""></label><br/>
            <input type="submit" class="btn btn-primary" value="นำเข้า">
        </div>
    </form>
</div>
<br/>
