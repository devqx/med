<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/16/18
 * Time: 11:39 AM
 */
$_GET['suppress'] = true;
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFDrug.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFDrugDAO.php';
	
	if(!is_blank($_POST['name']) && !is_blank($_POST['generic_id'])){
		$drug = (new IVFDrug())->setName($_POST['name'])->setGeneric($_POST['generic_id'])->add();
		if(!is_null($drug)){
			exit("success:drug saved");
		} else {
			exit("error:Failed to save drug");
		}
	} else {
		exit("error:Drug AND Generic required");
	}
}

?>
<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: finish})">
		<label>
			Drug Name
			<input type="text" name="name">
		</label>
		<label>
			Generic
			<input type="hidden" name="generic_id" id="generic_id">
		</label>
		<button type="submit" class="btn">Add Drug</button>
		<button type="button" class="btn-link" reset>Cancel</button>
		<span name="messageBox"></span>
	</form>
</section>
<script type="text/javascript">
	var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
   $(document).ready(function () {
	   refreshGen();
   })
	function finish(s) {
		var data = s.split(":");
		if(data[0]==="error"){
			$('span[name="messageBox"]').html('<div class="error alert-box">'+data[1]+'</div>');
		} else {
			$('span[name="messageBox"]').html('<div class="notice alert-box">'+data[1]+'</div>');
			$('button[reset]').click();
		}

	}

	function refreshGen() {
		$("#generic_id").select2("destroy");
		setTimeout(function () {
			$("#generic_id").select2({
				width: '100%',
				allowClear: true,
				placeholder: "select drug generic",
				data: {results: drugGens, text: 'name'},
				formatResult: function (source) {
					return source.name + ' [' + source.weight + ' ' + source.form + ']';
				},
				formatSelection: function (source) {
					return source.name + ' [' + source.weight + ' ' + source.form + ']';
				}
			});
		}, 50);

	}
</script>
