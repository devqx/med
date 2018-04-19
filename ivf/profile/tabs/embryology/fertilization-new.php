<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/6/16
 * Time: 12:59 PM
 */

//include_once 'zygotes.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/Fertilization.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/ZygoteType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/ZygoteTypeDAO.php';
$zygotes = (new ZygoteTypeDAO())->all();

$methods = (new IVFMethodDAO())->all();


if($_POST){
	@session_start();
	if(!isset($_SESSION['staffID'])){exit('error:Sorry please login again');}
	if(is_blank($_POST['method_id'])){exit('error:Method of fertilization?');}
	if(is_blank($_POST['zygote_type'])){exit('error:What type of zygote is that?');}
	if(is_blank($_POST['cell_no'])){exit('error:How many cells?');}
	if(is_blank($_POST['comment'])){exit('error:No comment?');}
	$witnesses = [];
	$post = json_encode(explode(',', @$_POST['witness_ids'][0]));
	foreach (json_decode($post) as $wit) {
		if ((int)$wit !== 0) {
			$witnesses[] = (int)$wit;
		}
	}
	$save = (new Fertilization())->setUser(new StaffDirectory($_SESSION['staffID']))->setInstance(new IVFEnrollment($_POST['aid']))->setMethod(new IVFMethod($_POST['method_id']))->setZygoteType($_POST['zygote_type'])->setCellNo($_POST['cell_no'])->setWitnesses($witnesses)->add();
	if($save !== null){
		exit('success:Saved data!');
	}
	exit('error:Failed to save data');
}
?>
<section style="width:400px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: __sdfl_osdnf7w, onComplete: __rjw_sdfe})">
		<label>Method <select name="method_id" data-placeholder="-- select --"><?php foreach($methods as $method){?><option value="<?= $method->getId()?>"><?= $method->getName()?></option><?php }?></select></label>
		<p></p>
		<div class="paper-card">
			<div class="heading">Embryo status</div>
			<div class="card-content">
				<!--<div class="row-fluid">-->
				<!--	<label class="span6"><input type="radio" name="fertilization_status" value="true"> Fertilized  </label>-->
				<!--	<label class="span6"> <input type="radio" name="fertilization_status" value="false"> Not Fertilized </label>-->
				<!--</div>-->
				<p></p>
				<div class="row-fluid">
					<!--<label class="span6">Zygote Type <input type="hidden" name="zygote_type" data-placeholder="--select--"></label>-->
					<label class="span6">
						Zygote Type
						<select name="zygote_type" data-placeholder="--select--">
							<option></option>
							<?php foreach ($zygotes as $zy){ ?>
								 <option value="<?= $zy->getName() ?>"><?= $zy->getName() ?> </option>
								
						 <?php	} ?>
						</select>
					</label>
					
					<label class="span6">Number <input type="number" step="1" min="1" name="cell_no"> </label>
				</div>
			</div>
		</div>
		<p class="btn-block"></p>
		<label>Comment <textarea name="comment"></textarea> </label>
		<label><span id="witnessLabel">Witness</span> <input data-witnesses type="hidden" name="witness_ids[]"> </label>
		<!-- witness is a m2m -->
		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="aid" value="<?=$_GET['aid']?>">
	</form>
</section>
<script type="text/javascript">
	//var zygotes = JSON.parse('<?//= json_encode($zygotes)?>//');
	var $filtered = [];
	__sdfl_osdnf7w = function () {
		$(document).trigger('ajaxSend');
	};

	__rjw_sdfe = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
			setTimeout(function () {
				$('a[data-href^="fertilization.php"]')[0].click();
			}, 50);
		}
	};

	$(document).ready(function () {
		$('[name="witness_ids[]"]').select2({
			placeholder: "Staff Name (Specialization [Staff ID])",
			allowClear: true,
			multiple:true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "]) " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "])";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the staff name or ID or phone or specialization";
			},
			ajax: {
				url: '/api/search_staffs.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, // search term
						limit: 100,
						asArray: true
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			}
		});//End Staff Select2

		//$('input[name="zygote_type"]').select2({
		//	width: '100%',
		//	allowClear: true,
		//	data: function () {
		//		return {results: $filtered, text: 'name'};
		//	},
		//	formatResult: function (source) {
		//		return source.name;
		//	},
		//	formatSelection: function (source) {
		//		return source.name;
		//	},
		//	id: function (source) {
		//		return source.name;
		//	}
		//
		//});
  
	//$('input[name="fertilization_status"]', function (e) {
	//		$('input[name="zygote_type"]').select2("val","");
	//		if(!JSON.parse($(e.currentTarget).val())){
	//			$('#witnessLabel').html('Failure To Fertilize')
	//		} else {
	//			$('#witnessLabel').html('Witness');
	//		}
	//		$filtered = _.filter(zygotes, ["fertilized", JSON.parse($(e.currentTarget).val())]);
	//	})
	
	})
	
</script>
