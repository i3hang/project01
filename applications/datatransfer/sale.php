<?php
    $result = select_db("
        SELECT
            lot_tb.*,
            store_tb.store_name
        FROM
            lot_tb
        INNER JOIN store_tb ON lot_tb.store_sale_id = store_tb.store_id
    ");
?>

<div class="detail">
    <p class="title">ตรวจสอบข้อมูลการขายสินค้า</p>
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
                <td class="text-center">ไม่มีข้อมูล</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>