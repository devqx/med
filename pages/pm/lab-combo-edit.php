<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/7/17
 * Time: 5:09 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabComboDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
$combo = (new LabComboDAO())->get($_GET['id']);
$labs = (new LabDAO())->getLabs(FALSE);

if($_POST){
	if(is_blank($_POST['name'])){
		exit('error:Name is required');
	}
	if(is_blank($_POST['lab_ids'])){
		exit('error:Select at least a lab');
	}
	
	$labComboData = [];
	foreach ($_POST['lab_ids'] as $l) {
		$labComboData[] = (new LabComboData())->setLab(new Lab($l));
	}
	
	if((new LabComboDAO())->get($_POST['c_id'])->setName($_POST['name'])->setCombos($labComboData)->update()){
		exit('ok:Combo updated');
	}
	exit('error:Failed to update');
}

?>
<section style="width: 650px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: null, onComplete: cf1})">
		<label>Combo Name: <input type="text" name="name" value="<?=$combo->getName() ?>"> </label>
		<label>Constituent Labs
			<select name="lab_ids[]" multiple data-placeholder="Search and select labs">
				<?php foreach ($labs as $lab){?>
					<option value="<?=$lab->getId()?>"<?php foreach ($combo->getCombos() as $data) {?><?= ($data->getLab()->getId()==$lab->getId())? ' selected':'' ?><?php }?>><?= $lab->getName()?></option>
				<?php }?>
			</select>
			<!--<input type="hidden" name="lab_ids"> -->
		</label>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="c_id" value="<?=$combo->getId() ?>">
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('.boxy-content input[name="lab_ids"]').select2({
			placeholder: "Search and select labs",
			minimumInputLength: 3,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_labs.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						search: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			formatSelection: function (data) {
				return data.name + " (" + data.category.name + ")";
			}
		});
	});
	
	var cf1 = function (data) {
		ret = data.split(":");
		if (ret[0] === "error") {
			Boxy.warn(ret[1]);
		} else if (ret[0] === "ok") {
			Boxy.info(ret[1], function () {
				reloadLabCombos();
			});
			Boxy.get($(".close")).hideAndUnload();
		}
	};
</script>
