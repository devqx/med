<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/23/16
 * Time: 1:08 PM
 */

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EncounterAddendum.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

	if(is_blank($_POST['addendum'])){
		exit("error:Note is blank ");
	}
	$encounter = new Encounter($_POST['encounter_id']);
	$addendum = (new EncounterAddendum())->setEncounter($encounter)->setNote($_POST['addendum'])->add();
	if($addendum != null){
		exit("success:Note added successfully");
	}
	exit("error:Something went wrong");
}
?>
<section style="width: 850px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: done__})">
		<label>Add Note to Encounter
			<textarea name="addendum"></textarea>
		</label>
		<input type="hidden" name="encounter_id" value="<?= $_GET['enc_id'] ?>">

		<div class="btn-block">
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('textarea[name="addendum"]').summernote(SUMMERNOTE_CONFIG);
	});
	function done__(data) {
		var response = data.split(":");
		if (response[0] == "success") {
			Boxy.get($(".close")).hideAndUnload();
			setTimeout(function(){
				Boxy.get($(".close")).hideAndUnload();

				if($('a[data-link-type="encounterDetails"][data-id="<?= $_GET['enc_id'] ?>"]').get(0) != undefined){
					$('a[data-link-type="encounterDetails"][data-id="<?= $_GET['enc_id'] ?>"]').get(0).click();
				}
			}, 500);
		} else {
			Boxy.warn(response[1]);
		}
	}
</script>
