<?php
    check_user($_SESSION['user_type'], array(1,2));
    if (empty($_POST['date_start']) && empty($_POST['date_end'])) {
        $dateStart = date('Y-m-d');
        $dateEnd = date('Y-m-d');
    } else {
        $dateStart = $_POST['date_start'];
        $dateEnd = $_POST['date_end'];
    }

    $result = select_db("
        SELECT
            lot_tb.*,
            store_tb.store_name
        FROM
            lot_tb
        INNER JOIN store_tb ON lot_tb.store_sale_id = store_tb.store_id
        WHERE
            lot_tb.lot_status  != '3' AND
            lot_date_sale BETWEEN '$dateStart 00:00:00' AND '$dateEnd 23:59:59'
        ORDER BY 
            lot_date_sale ASC
    ");
?>

<div class="detail">
    <p class="title">ตรวจสอบข้อมูลการขายสินค้า</p>

    <form action="" class="form-inline" method="POST">
        <div class="input-group mb-3">
            <input type="date" name="date_start" value="<?=$dateStart?>" class="form-control form-control-sm">
            <div class="mt-1">&nbsp; ถึงวันที่ &nbsp;</div>
            <input type="date" name="date_end" value="<?=$dateEnd?>" class="form-control form-control-sm">&nbsp;
            <button type="submit" class="btn btn-secondary btn-sm">ค้นหา</button>
        </div>
    </form>

    <table class="table table-bordered table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th class="text-center" style="width: 100px;">#</th>
                <th class="text-center" style="width: 150px;">วันที่ขายสินค้า</th>
                <th class="text-center">ขายสินค้าให้กับ</th>
                <th class="text-center" style="width: 150px;">ราคาขาย</th>
                <th class="text-center" style="width: 150px;">น้ำหนัก</th>
                <th class="text-center" style="width: 150px;">ยอดจริง</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($result->num_rows > 0) { 
                $i = 1;
                while($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td class="text-center"><?=$i?></td>
                <td class="text-center"><?=date_format(date_create($row['lot_date_sale']),"d/m/Y")?></td>
                <td><?=$row['store_name']?></td>
                <td class="text-right"><?=number_format($row['lot_price_sale'],2)?></td>
                <td class="text-right"><?=number_format($row['lot_weight'],2)?></td>
                <td class="text-right"><?=number_format($row['lot_weight']*$row['lot_price_sale'],2)?></td>
            </tr>
        <?php $i++;}} else { ?>
            <tr>
                <td class="text-center" colspan="6">ไม่มีข้อมูล</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>