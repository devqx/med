<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/15/16
 * Time: 12:37 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Package.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageSubscription.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageSubscriptionDAO.php';
$packages = (new PackageDAO())->all();

if($_POST){
	if(is_blank($_POST['package_id'])){exit('error:You did not select any package');}
	if(is_blank($_POST['patient_id'])){exit('error:How come we don\'t know the patient');}
	
	$sub = (new PackageSubscription())->setPackage( new Package($_POST['package_id']) )->setPatient( new PatientDemograph($_POST['patient_id']) )->setDateSubscribed(date(MainConfig::$mysqlDateTimeFormat))->setActive(TRUE)->add();
	
	if($sub != null){
		exit('success:Subscription success');
	}
	exit('error:Subscription failed');
}
?>
<section>
	<form method="post" action="<?=$_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: $osdkf, onComplete: $0ew})">
		<span class="notif"></span>
		<label>Select Package
		<select name="package_id" data-placeholder="-- Select package --">
			<option></option>
			<?php foreach ($packages as $package) {if($package->getActive() && new DateTime($package->getExpiration()) > new DateTime() ){?>
				<option value="<?=$package->getId()?>" data-value="<?=$package->getPrice()?>"><?=$package->getName()?></option>
			<?php }}?>
		</select>
		</label>
		<input type="hidden" name="patient_id" value="<?= $_GET['patient']?>">
		<p class="clearBoth" style="margin-bottom:50px"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Subscribe</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).on('change', '[name="package_id"]', function (e) {
		if($(e.target).find('option:selected').text()!==''){
			$('form > span.notif').html('A charge of ₦' + $.number($(e.target).find('option:selected').data('value'), 2)+' will be made to the patient');
		} else {
			$('form > span.notif').html('');
		}
	});
	var $osdkf = function () {
		$(document).trigger('ajaxSend');
	};
	var $0ew = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if(data[0]=='error'){
			Boxy.warn(data[1]);
		} else {
			Boxy.info(data[1]);
			Boxy.get($('.close')).hideAndUnload();
			showDoc('promos');
		}
	}
</script>
