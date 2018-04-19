<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/17
 * Time: 5:50 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DRTDAO.php';
$drt = (new DRTDAO())->get($_GET['id']);
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DRT.php';
	
	if(is_blank($_POST['name'])){exit('error:The DRT name is required');}
	if(is_blank($_POST['description'])){exit('error:The DRT description is required');}
	if(is_blank($_POST['amount'])){exit('error:The DRT base price is required');}
	
	$upd = (new DRTDAO())->get($_POST['id'])->setName($_POST['name'])->setBasePrice(parseNumber($_POST['amount']))->setDescription($_POST['description'])->update();
	
	if($upd !== null){
		exit('success:DRT updated');
	}
	exit('error:Failed to update DRT service');
	
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: __start___, onComplete: __complete___})">
		<label>Name <input type="text" name="name" value="<?= $drt->getName()?>"> </label>
		<label>Description <textarea name="description"><?= $drt->getDescription()?></textarea> </label>
		<label>Base Price <input type="number" min="0" name="amount" value="<?= $drt->getBasePrice()?>"> </label>
		<p style="margin-bottom: 50px;"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="id" value="<?= $_GET['id']?>">
	</form>
</section>
<script type="text/javascript">
	var __start___ = function () {
		$(document).trigger('ajaxSend');
	};

	var __complete___ = function (s) {
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
