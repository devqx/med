<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/8/17
 * Time: 3:16 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureResourceType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if($_POST){
	if(is_blank($_POST['name'])){exit('error:Resource Type name is required');}
	
	$new = (new ProcedureResourceType())->setName($_POST['name'])->add();
	if($new!==null){
		exit('success:Action completed');
	}
	exit('error:Failed to save');
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: startNewReType, onComplete: proNewResType})">
		<label>Resource Type Name <input type="text" name="name"> </label>
		<p style="margin-bottom:50px"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var startNewReType = function () {
		$(document).trigger('ajaxSend');
	};
	
	var proNewResType = function (s) {
		$(document).trigger('ajaxStop');
		processFormResponse(s, reloadProResourceTypes());
	};
</script>
