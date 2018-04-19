<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 6/28/17
 * Time: 10:08 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.printer.php';
$code = isset($_GET['pCode']) ? $_GET['pCode'] : '';
if ($_POST) {
	$return = (object)null;
	$return->info = (object)null;
	$print_ip = $_POST['selectPrinter'];
	if ($print_ip == '') {
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
	$return->info->message = $print_ip;
	$return->info->count = $_POST['print_no'];
	exit(json_encode($return));
}
?>
<div style="width: 500px;">
	<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : done})">
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
			<label class="span9">
				<input name="print_no" type="number" data-decimals="0" value="1" min="1" placeholder="Number of copies" required>
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
		$('form > span ').html('<img src="/img/loading.gif">');
	}

	function done(s) {
		$('form > spapn').html('');
		s = JSON.parse(s);
		if (s.status === 'error') {
			Boxy.alert(s.info.message);
		} else {
			$.get('/pharmaceuticals/print_filled_prescription.php?pCode=<?= $code ?>&printer=' + s.info.message + '&count=' + s.info.count, function (xhr) {
				if (xhr.toLowerCase() === 'ok') {
					Boxy.info("Prescription slip printed");
					Boxy.get($('.close')).hideAndUnload();
				} else {
					Boxy.alert('Failed to print prescription');
				}
			})
		}

	}
</script>
