<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 6/4/15
 * Time: 9:25 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.printer.php';

$return = (object)null;
$voucher_id = (isset($_REQUEST['vid']))? $_REQUEST['vid'] : '';
if($_POST) {
    $printer_ip = $_POST['selectPrinter'];
    if($printer_ip==''){
        $return->status = "error";
        $return->message = "Select your printer";
        exit(json_encode($return));
    }
    $return->status = "success";
    $return->message = $printer_ip;
    exit(json_encode($return));
}
?>
<div style="width: 500px">
    <form method="post" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
        <span></span>
        <div class="row-fluid">
            <span class="span2">Print To:</span>
            <label class="span10">
                <select id="selectPrinter" name="selectPrinter" data-placeholder="Select printer">
                    <option></option>
                    <?php foreach(PrinterConfig::$printServerPrinters as $location=>$ip){ ?>
                        <option value="<?= $ip ?>"><?= $location ?></option>
                    <?php } ?>
                </select>
            </label>
        </div>
        <div class="btn-block"></div>
        <div class="btn-block">
            <button class="btn" type="submit"><i class="icon-print"></i> Print</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script>
function start() {
    $('form > span').html('<img src="/img/loading.gif">');
}
function done(s) {
    $('form > span').html('');
    console.log(s);
    s = JSON.parse(s);
    console.log(s);
    if(s.status=='error'){
        Boxy.alert(s.message);
    }
    else {
        $.get('/vouchers/print_voucher.php?id=<?=$voucher_id ?>&printer=' + s.message, function (xhr) {
            console.log(xhr);
            if (xhr.toLowerCase() === 'ok') {
                Boxy.info("Receipt printed");
                Boxy.get($('.close')).hideAndUnload();
            } else {
                Boxy.alert("Failed to print receipt");
            }
        });
    }
}
</script>