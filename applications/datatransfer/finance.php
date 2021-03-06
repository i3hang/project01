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
        order_tb.*,
        store_tb.store_name
    FROM
        order_tb
    INNER JOIN store_tb ON order_tb.store_id = store_tb.store_id
    WHERE
        order_tb.order_status  >= '2' AND
        order_tb.order_fitdate BETWEEN '$dateStart 00:00:00' AND '$dateEnd 23:59:59' 
    ORDER BY 
        order_tb.order_fitdate ASC
");

$resultChartsBuy = select_db("
    SELECT 
        store_tb.store_id,
        store_tb.store_name,
        SUM(ob_weight * ob_price) AS buy
    FROM 
        order_buy_tb 
    INNER JOIN store_tb ON order_buy_tb.store_id = store_tb.store_id 
    WHERE
        order_buy_tb.ob_status  = '2' AND
        order_buy_tb.ob_fitdate BETWEEN '$dateStart 00:00:00' AND '$dateEnd 23:59:59'
    GROUP BY
        store_tb.store_name
");

$resultChartsSale = select_db("
    SELECT 
        store_tb.store_id,
        store_tb.store_name,
        SUM(order_weight * order_price_sale) AS sale
    FROM 
        order_tb 
    INNER JOIN store_tb ON order_tb.store_id = store_tb.store_id 
    WHERE
        order_tb.order_status  >= '3' AND
        order_tb.order_fitdate BETWEEN '$dateStart 00:00:00' AND '$dateEnd 23:59:59'
    GROUP BY
        store_tb.store_name
");
$arrChart = array();

if ($resultChartsBuy->num_rows > 0) {
    
    while($chart = $resultChartsBuy->fetch_assoc()) {
        $arrChart[$chart['store_id']]['name']  = $chart['store_name'];
        $arrChart[$chart['store_id']]['buy']   = $chart['buy'];
        $arrChart[$chart['store_id']]['sale']  = 0;
    }
}

if ($resultChartsSale->num_rows > 0) {
    while($chart = $resultChartsSale->fetch_assoc()) {
        if (empty($arrChart[$chart['store_id']])) {
            $arrChart[$chart['store_id']]['name']  = $chart['store_name'];
            $arrChart[$chart['store_id']]['buy']   = 0;
            $arrChart[$chart['store_id']]['sale'] = $chart['sale'];
        } else {
            $arrChart[$chart['store_id']]['sale'] = $chart['sale'];
        }
    }
}

$textChart = "";
foreach ($arrChart AS $k=>$v) {
    $textChart .= "['".$v['name']."',".$v['buy'].",".$v['sale']."],";
}

// dieArray($textChart);
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

    google.charts.load('current', {'packages':['bar']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['ชื่อร้าน', 'ยอดซื้อสินค้า', 'ยอดขายสินค้า'],
          <?=$textChart?>
        ]);

        var options = {
          chart: {
            title: 'ตรวจสอบข้อมูลการเงิน',
            subtitle: 'ตั้งแต่วันที่ :<?php echo date_format(date_create($dateStart),"d/m/Y"); ?> ถึงวันที่ <?php echo date_format(date_create($dateEnd),"d/m/Y"); ?>',
          },
          bars: 'horizontal' // Required for Material Bar Charts.
        };

        var chart = new google.charts.Bar(document.getElementById('barchart_material'));

        chart.draw(data, google.charts.Bar.convertOptions(options));
      }
</script>

<div class="detail">
    <p class="title">ตรวจสอบข้อมูลการเงิน</p>
    <form action="" class="form-inline" method="POST">
        <div class="input-group mb-3">
            <input type="date" name="date_start" value="<?=$dateStart?>" class="form-control form-control-sm">
            <div class="mt-1">&nbsp; ถึงวันที่ &nbsp;</div>
            <input type="date" name="date_end" value="<?=$dateEnd?>" class="form-control form-control-sm">&nbsp;
            <button type="submit" class="btn btn-secondary btn-sm">ค้นหา</button>
        </div>
    </form>

    <div id="barchart_material" style="width: 100%; height: 350px;" class="mb-3"></div>

    <table class="table table-bordered table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th class="text-center" style="width: 80px;">Lot</th>
                <th class="text-center" style="width: 150px;">วันที่บันทึกข้อมูล</th>
                <th class="text-center" style="width: 80px;">น้ำหนัก</th>
                <th class="text-center" style="width: 100px;">ราคาซื้อ</th>
                <th class="text-center" style="width: 100px;">ราคาขาย</th>
                <th class="text-center" style="width: 100px;">ค่ากรรมกร</th>
                <th class="text-center" style="width: 150px;">ยอดขายจริง</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if ($result->num_rows > 0) { 
                $buyFinance     = 0;
                $saleFinance    = 0;
                $weightFinance  = 0;
                $otherFinance   = 0;
                $totalFinance   = 0;
                while($row = $result->fetch_assoc()) {
                    $weightFinance  += $row['order_weight'];
                    $buyFinance     += $row['order_weight']*$row['order_price_buy'];
                    $saleFinance    += $row['order_weight']*$row['order_price_sale'];
                    $otherFinance   += $row['order_price_transfer'];
                    $totalFinance   += ($row['order_weight']*$row['order_price_sale']) - $row['order_price_transfer'];
        ?>
            <tr>
                <td class="text-center"><?=$row['order_id']?></td>
                <td class="text-center"><?=date_format(date_create($row['order_fitdate']),"d/m/Y")?></td>
                <td class="text-right"><?=number_format($row['order_weight'],2)?></td>
                <td class="text-right"><?=number_format($row['order_weight']*$row['order_price_buy'],2)?></td>
                <td class="text-right"><?=number_format($row['order_weight']*$row['order_price_sale'],2)?></td>
                <td class="text-right"><?=number_format($row['order_price_transfer'],2)?></td>
                <td class="text-right"><?=number_format(($row['order_weight']*$row['order_price_sale']) - $row['order_price_transfer'],2)?></td>
            </tr>
        <?php } ?>
            <tr>
                <td class="text-center font-weight-bold">รวม</td>
                <td></td>
                <td class="text-right text-danger font-weight-bold"><?=number_format($weightFinance,2)?></td>
                <td class="text-right text-danger font-weight-bold"><?=number_format($buyFinance,2)?></td>
                <td class="text-right text-danger font-weight-bold"><?=number_format($saleFinance,2)?></td>
                <td class="text-right text-danger font-weight-bold"><?=number_format($otherFinance,2)?></td>
                <td class="text-right text-danger font-weight-bold"><?=number_format($totalFinance,2)?></td>
            </tr>
            <tr>
                <td class="text-right font-weight-bold" colspan="6">กำไรสุทธิ</td>
                <td class="text-right text-danger font-weight-bold"><?=number_format($saleFinance - $buyFinance,2)?></td>
            </tr>
        <?php } else { ?>
            <tr>
                <td class="text-center" colspan="7">ไม่มีข้อมูล</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>