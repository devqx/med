<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/8/17
 * Time: 3:51 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureResourceType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureResourceTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$data = (new ProcedureResourceTypeDAO)->get($_GET['id']);
if($_POST){
	if(is_blank($_POST['name'])){exit('error:Resource Type name is required');}
	
	$update = (new ProcedureResourceTypeDAO())->get(base64_decode($_POST['data_id']) )->setName($_POST['name'])->update();
	if($update!==null){
		exit('success:Resource Updated');
	}
	exit('error:Failed to update');
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: startEditReType, onComplete: proEditResType})">
		<label>Resource Type Name <input type="text" name="name" value="<?= $data->getName()?>"> </label>
		<input type="hidden" name="data_id" value="<?= base64_encode($data->getId()) ?>">
		<p style="margin-bottom:50px"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var startEditReType = function () {
		$(document).trigger('ajaxSend');
	};

	var proEditResType = function (s) {
		$(document).trigger('ajaxStop');
		processFormResponse(s, reloadProResourceTypes());
	};
</script>