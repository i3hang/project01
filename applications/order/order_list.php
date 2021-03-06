<?php
    check_user($_SESSION['user_type'], array(3));
    $store = $_SESSION['store_id'];
    $result = select_db("
        SELECT
            *
        FROM
            lot_tb
        INNER JOIN products_tb ON lot_tb.products_id = products_tb.products_id
        WHERE
            lot_tb.store_order = '$store' AND
            (lot_tb.lot_status != '1' OR lot_tb.lot_status != '3')
    ");
?>

<div class="detail">
    <p class="title">รายการสั่งซื้อสินค้า</p>
    <a href="?app=order&action=order_buy" class="btn btn-secondary btn-sm mb-2">สั่งซื้อสินค้า</a>
    <table class="table table-bordered table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th class="text-center" style="width:80px;">#</th>
                <th class="text-center" style="width:120px;">วันที่สั่งซื้อสินค้า</th>
                <th class="text-center">ชื่อสินค้า</th>
                <th class="text-center" style="width:120px;">น้ำหนัก(กก.)</th>
                <th class="text-center" style="width:120px;">ราคาขาย(บาท)</th>
                <th class="text-center" style="width:120px;">ยอดจริง(บาท)</th>
                <th class="text-center" style="width:150px;">สถานะ</th>
                <th class="text-center" style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result->num_rows > 0) {
                $i = 1; 
            while ($row = $result->fetch_assoc()) {    
            ?>
            <tr>
                <td class="text-center"><?=$i?></td>
                <td class="text-center"><?=date_format(date_create($row['lot_order_date']),"d/m/Y")?></td>
                <td><?=$row['products_name']?></td>
                <td class="text-right"><?=number_format($row['lot_weight'], 2)?></td>
                <td class="text-right"><?=number_format($row['lot_price_sale'], 2)?></td>
                <td class="text-right"><?=number_format($row['lot_weight']*$row['lot_price_sale'], 2)?></td>
                <td class="text-center"><?=$statusLot[$row['lot_status']]?></td>
                <td class="text-center">
                    <?php if ($row['lot_status'] == 4) {?>
                    <a href="?app=order&action=order_cancel&id=<?=$row['lot_id']?>" onclick="return confirm('ยืนยันการยกเลิก')" class="btn btn-danger btn-sm" title="ยกเลิกการสั่งซื้อ"><i class="fas fa-window-close"></i></a>
                    <?php } ?>
                </td>
            </tr>
            <?php $i++;}} else { ?>
            <tr>
                <td class="text-center" colspan="7">ไม่มีรายการสั่งซื้อ</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>