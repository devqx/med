<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 1/6/17
 * Time: 11:23 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.printer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';


if($_POST) {
	$q = (new PatientQueueDAO())->getPatientQueue($_POST['id'], true);
	//error_log(json_encode($q));
	$emrId = $q->getPatient()->getId();
	$patientName = $q->getPatient()->getFullname();
	$check_in_time = $q->getEntryTime();
	$specialty = $q->getSpecialization() ? str_replace('&','and', $q->getSpecialization()->getName()) : 'N/A';
	
	$tagNo = $q->getTagNo();
	//todo validation first
	if(is_blank($_POST['selectPrinter'])){
		exit('error:Select Printer first');
	}
	$clinic_name = $q->getClinic() ? str_replace('&', 'and', $q->getClinic()->getName() ) : '--';
	$printer = (isset($_POST['selectPrinter'])) ? $_POST['selectPrinter'] : '';
	$clinic = str_replace('&', 'and', (new ClinicDAO())->getClinic(1)->getName());

	//error_log("The clinic name is ". $clinic_name);
    //$tagOutput = "<receipt font='b' width='60'><p align='center'>$clinic</p><hr font='a'/><line line-ratio='0.15' font='a'><left>Name</left><right align='right'>$patientName</right></line><line line-ratio='0.15' font='a'><left>EMR ID</left><right align='right'>($emrId)</right></line><line line-ratio='0.15' font='a'><left>To see</left><right align='right'>$specialty</right></line> <h5 align='center'>TAG NO.</h5><h1 font='a' align='center'>$tagNo</h1> <h5 align='center'>Check-In Time</h5><h1 font='a' align='center'>$check_in_time</h1> <barcode encoding='CODE39'>$emrId</barcode> <cashdraw /></receipt>";

    $tagOutput ="<receipt font='b' width='60'>
    <p align='center'> $clinic </p>
    <hr font='b' />
    <line line-ratio='0.15' font='a'><!--<left> Name </left>--> <right align='left'>$patientName</right></line>
    <line line-ratio='0.15' font='a'><!--<left> EMR ID </left>--><right align='left'>($emrId)</right></line>
    <h5 align='center'>TAG NO.</h5><h1 font='a' align='center'>$tagNo</h1>
    <line line-ration='0.15' font='a'><!--<left> Clinic  </left>--><left align='left'> $clinic_name </left></line>
    <line line-ration='0.15' font='a'><!--<left> Clinic  </left>--><left align='left'> $specialty </left></line>
    <!--<line line-ratio='0.15' font='a'><left>To see</left><right align='right'></right></line>-->
    <h5 align='center'>Check-In Time</h5><h1 font='a' align='center'>$check_in_time</h1>
    
    <barcode encoding='CODE39'>$emrId</barcode> <cashdraw /></receipt>";



    $url = 'http://' . $printer . '/receipt/printnow/';
	$fields = array('q' => $tagOutput);
	$postData = '';
	foreach ($fields as $k => $v) {
		$postData .= $k . '=' . $v . '&';
	}
	rtrim($postData, '&');
	
	if(!function_exists('curl_init')){
		exit('error:Please contact admin to install something');
	}
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	
	//execute post
	if (curl_exec($ch) === false) {
		curl_close($ch);
		exit('error:Server not found');
		
	}
	
	ob_end_clean();
	
	//close connection
	curl_close($ch);
	
	exit('success:Tag # printed');

}
?>

<div style="width: 500px">
	<form method="post" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
		<span></span>
		<label>Print To:
			<select id="selectPrinter" name="selectPrinter" data-placeholder="Select printer">
				<option></option>
				<?php foreach (PrinterConfig::$printServerPrinters as $location => $ip) { ?>
					<option value="<?= $ip ?>"><?= $location ?></option>
				<?php } ?>
			</select>
		</label>
		<div class="row-fluid hide">
			<span class="span3">Number of copies to print:</span>
			<label class="span9"><input name="print_no" type="number" min="1" placeholder="Number of copies" value="1" required="required"></label>
		</div>
		<div class="btn-block"></div>
		<div class="btn-block">
			<button class="btn" type="submit"><i class="icon-print"></i> Print</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
			<input type="hidden" name="id" value="<?=$_GET['id']?>">
		</div>
	</form>
</div>
<script type="text/javascript">
	var start = function () {
		$(document).trigger('ajaxSend');
	};
	
	var done = function (s) {
		console.log(s);
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if(data[0] === 'error'){
			Boxy.warn(data[1]);
		} else if (data[0] === 'success'){
			Boxy.get($('.close')).hideAndUnload();
		}
	}
</script>
