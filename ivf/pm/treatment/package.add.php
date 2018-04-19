<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 4:22 PM
 */

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFPackage.php';
	if (is_blank($_POST['name'])) {
		exit('error:Package name is really required');
	}
	if (is_blank($_POST['default_price'])) {
		exit('error:The default price for this package is missing.');
	}
	
	$pkg = (new IVFPackage())->setName($_POST['name'])->setAmount(parseNumber($_POST['default_price']))->add();
	if ($pkg != null) {
		exit('success:Package Added?');
	}
	exit('error:Failed to add Package');
}
?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>Package Name <input type="text" name="name"> </label>
		<label>Base Price
			<input type="number" name="default_price" step="any" value="0"> </label>
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
	function finish(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			$('span[name="messageBox"]').html('<div class="error alert-box">' + data[1] + '</div>');
		} else {
			$('span[name="messageBox"]').html('<div class="notice alert-box">' + data[1] + '</div>');
			$('button[reset]').click();
		}
	}
</script>
