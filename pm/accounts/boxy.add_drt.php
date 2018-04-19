<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/6/17
 * Time: 1:35 PM
 */
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DRT.php';
	
	if(is_blank($_POST['name'])){exit('error:The DRT name is required');}
	if(is_blank($_POST['description'])){exit('error:The DRT description is required');}
	if(is_blank($_POST['amount'])){exit('error:The DRT base price is required');}
	
	if((new DRT())->setName($_POST['name'])->setBasePrice(parseNumber($_POST['amount']))->setDescription($_POST['description'])->add()){
		exit('success:DRT saved');
	}
	exit('error:Failed to add DRT service');
	
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: __start__, onComplete: __complete__})">
		<label>Name <input type="text" name="name"> </label>
		<label>Description <textarea name="description"></textarea> </label>
		<label>Base Price <input type="number" min="0" name="amount"> </label>
		<p style="margin-bottom: 50px;"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Create</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var __start__ = function () {
		$(document).trigger('ajaxSend');
	};
	
	var __complete__ = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if(data[0]==='error'){
			Boxy.warn(data[1]);
		} else if (data[0]==='success'){
			Boxy.get($('.close')).hideAndUnload();
			showTabs(6);
		}
	}
</script>
