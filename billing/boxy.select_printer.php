<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 6/4/15
 * Time: 9:25 AM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.printer.php';


$bid = (isset($_REQUEST['bid'])) ? $_REQUEST['bid'] : '';
$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';
$v = (isset($_REQUEST['type'])) ? $_REQUEST['type'] : '';
$reprint = (isset($_REQUEST['reprint'])) ? $_REQUEST['reprint'] : '';
$grouped = (isset($_REQUEST['grouped']));

if ($_POST) {
	$return = (object)null;
	$return->info = (object)null;
	$printer_ip = $_POST['selectPrinter'];
	if ($printer_ip == '') {
		$return->status = "error";
		$return->info->message = "Select your printer";
		exit(json_encode($return));
	}
	if (trim($_POST['print_no']) == '' || $_POST['print_no'] < 1) {
		$return->status = "error";
		$return->info->message = "Enter number of copies to print";
		exit(json_encode($return));
	}
	$return->status = "success";
	$return->info->message = $printer_ip;
	$return->info->grouped = $grouped;
	$return->info->count = $_POST['print_no'];
	exit(json_encode($return));
}
?>
<div style="width: 500px">
	<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
		<span></span>
		<div class="row-fluid">
			<span class="span3">Print To:</span>
			<label class="span9">
				<select id="selectPrinter" name="selectPrinter" data-placeholder="Select printer">
					<option></option>
					<?php foreach (PrinterConfig::$printServerPrinters as $location => $ip) { ?>
						<option value="<?= $ip ?>"><?= $location ?></option>
					<?php } ?>
				</select>
			</label>
		</div>
		<div class="row-fluid">
			<span class="span3">Number of copies to print:</span>
			<label class="span9"><input name="print_no" data-decimals="0" type="number" min="1" placeholder="Number of copies" value="1" required="required"></label>
		</div>
		<div class="btn-block"></div>
		<div class="btn-block" id="print_div">
			<button class="btn" type="submit"><i class="icon-print"></i> Print Thermal </button>
			<button class="btn" type="button"  onclick="a5Handler(event)" data-billID="<?php echo $bid;?>"><i class="icon-print"></i> Print A5 </button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script>

	function a5Handler(e){

		//get the bill ID 
		var bill_id = e.target.dataset.billid;

		console.log(bill_id);

		//redirect to the handler file 
		var purl = `/billing/print_a5_receipt.php?bill_id=${bill_id}`;

		window.location.href = purl;

	}

	function start() {
		$('form > span').html('<img src="/img/loading.gif">');
	}
	function done(s) {
		$('form > span').html('');
		s = JSON.parse(s);
		if (s.status === 'error') {
			Boxy.alert(s.info.message);
		}	else {
			
			$.get('/billing/print_receipt.php?type=<?= $v ?>&billId=<?=$bid ?>&mode=<?=$mode ?><?= isset($_REQUEST['grouped']) ? '&grouped':'' ?>&reprint=<?= $reprint ?>&printer=' + s.info.message + '&count=' + s.info.count, function (xhr) {
				//console.log(xhr);
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