<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 5:10 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFPackageDAO.php';
$pkg = (new IVFPackageDAO())->get($_GET['id']);
if ($_POST) {
	$package = (new IVFPackageDAO())->get($_POST['id']);
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFPackage.php';
	if(is_blank($_POST['name'])){
		exit('error:Package name is really required');
	}
	if(is_blank($_POST['default_price'])){
		exit('error:The default price for this package is missing.');
	}

	$package->setName($_POST['name'])->setAmount(parseNumber($_POST['default_price']))->update();
	if($package != null){
		exit('success:Package Updated');
	}
	exit('error:Failed to Update Package');
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish__})">
		<label>Package Name <input type="text" name="name" value="<?=$pkg->getName() ?>"> </label>
		<label>Base Price <input type="number" name="default_price" step="any" value="<?=$pkg->getAmount()?>"> </label>
		<input type="hidden" name="id" value="<?= $pkg->getId() ?>">

		<div class="clear"></div>
		<div class="clear" style="margin-bottom: 20px"></div>
		<!--<div class="btn-block">-->
		<button class="btn" type="submit">Save</button>
		<button class="btn-link" type="button" reset>Cancel</button>
		<!--</div>-->
		<span name="messageBox"></span>

	</form>
</section>
<script type="text/javascript">
	function finish__(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			$('span[name="messageBox"]').html('<div class="error alert-box">' + data[1] + '</div>');
		} else {
			$('span[name="messageBox"]').html('<div class="notice alert-box">' + data[1] + '</div>');
			$('button[reset]').click();
		}
	}
</script>