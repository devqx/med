<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/17/16
 * Time: 1:20 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/ReagentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/ReagentUsedDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/ReagentUsed.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/GeneticRequest.php';

$reagents = (new ReagentDAO())->all();
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	if(is_blank($_POST['reagent_id'])){exit("error:Reagent is required.");	}
	if(is_blank($_POST['lot_number'])){exit("error:Reagent Lot number is required.");	}
	
	$use = (new ReagentUsed())->setRequest( (new GeneticRequest($_POST['request_id'])) )->setLotNumber($_POST['lot_number'])->setReagent( new Reagent($_POST['reagent_id']) )->add();
	
	if($use != null){
		ob_end_clean();
		exit("success:Added Reagent");
	}
	ob_end_clean();
	exit("error:Failed to add reagent");
}
?>
<section style="width: 500px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: starting_, onComplete:completing_})">
		<label>Reagent <select name="reagent_id" required="required" data-placeholder=" Select Reagent Used">
				<option></option>
				<?php foreach ($reagents as $reagent){?>
					<option value="<?= $reagent->getId()?>"><?= $reagent->getName()?></option>
				<?php }?>
			</select></label>
		<label>Lot # <input name="lot_number" type="text" required="required"></label>
		<input type="hidden" name="request_id" value="<?= $_GET['request_id']?>">
		<div class="clear"><p></p></div>
		<div class="btn-block">
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function starting_() {
		$(document).trigger('ajaxSend');
	}
	function completing_(s) {
		//console.info(s);
		$(document).trigger('ajaxStop');
		data = s.split(":");
		if(data[0]=="error"){
			Boxy.warn(data[1]);
		} else if(data[0]=="success"){
			Boxy.get($(".close")).hideAndUnload();
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload(function () {
					setTimeout(function () {
						$('.openLink[data-id="<?= $_GET['request_id']?>"]').get(0).click();
					}, 1000);
				});
			})
		}
	}
</script>
