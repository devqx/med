<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:43 AM
 */
if($_POST){
	@session_start();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/SpermAnalysis.php';

	if(is_blank($_POST['volume'])){exit('error:Enter the Volume');}
	if(is_blank($_POST['cell_no'])){exit('error:Enter the Cell #');}
	if(is_blank($_POST['density'])){exit('error:Enter the density');}
	if(is_blank($_POST['motility'])){exit('error:Enter the % motility');}
	if(is_blank($_POST['prog'])){exit('error:Enter the PROG');}
	if(is_blank($_POST['abnormal'])){exit('error:Enter the % abnormal ');}
	if(is_blank($_POST['mar'])){exit('error:Enter the mar');}
	if(is_blank($_POST['aggl'])){exit('error:Enter the agglutination');}
	if(is_blank($_POST['comment'])){exit('error:Won\'t you enter comment?');}
	if(is_blank($_POST['aid'])){exit('error:An id error occurred');}

	$witnesses = [];
	$post = json_encode(explode(',', @$_POST['witness_ids'][0]));
	foreach (json_decode($post) as $wit) {
		if ((int)$wit !== 0) {
			$witnesses[] = (int)$wit;
		}
	}

	$save = (new SpermAnalysis())->setInstance(new IVFEnrollment($_POST['aid']))->setUser(new StaffDirectory($_SESSION['staffID']))->setVolume($_POST['volume'])->setCellNo($_POST['cell_no'])->setDensity($_POST['density'])->setMotility($_POST['motility'])->setProg($_POST['prog'])->setAbnormal($_POST['abnormal'])->setMar($_POST['mar'])->setAggl($_POST['aggl'])->setWitnesses($witnesses)->setComment($_POST['comment'])->add();
	if ($save !== null) {
		exit('success:Data saved!');
	}
	exit('error:Failed to save data');
}
?>
<section style="width: 400px;">
	<div class="well">New Sperm Analysis Record</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: __03945sdfl, onComplete: __ksd4rjwe})">
		<div class="row-fluid">
			<label class="span6">Volume (ml)<input type="number" step="any" name="volume"> </label>
			<label class="span6"># of sperm Cells <input type="number" step="1" name="cell_no"> </label>
		</div>
		<div class="row-fluid">
			<label class="span6">Density <input type="number" step="any" name="density"> </label>
			<label class="span6">Motility (%) <input data-decimals="1" type="number" min="0" max="100" step="any" name="motility"> </label>
		</div>
		<div class="row-fluid">
			<label class="span6">PROG <input type="text" name="prog"> </label>
			<label class="span6">Abnormal (%) <input data-decimals="1" type="number" min="0" max="100" step="any" name="abnormal"> </label>
		</div>
		<div class="row-fluid">
			<label class="span6">Mar <input type="text" name="mar"> </label>
			<label class="span6">Agglutination <input type="text" name="aggl"> </label>
		</div>
		<label>Comment <textarea name="comment"></textarea> </label>
		<label>Witness <input type="hidden" data-witnesses name="witness_ids[]"> </label>
		<!-- witness is a m2m -->
		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
	</form>
</section>
<script type="text/javascript">
	__03945sdfl = function () {
		$(document).trigger('ajaxSend');
	};

	__ksd4rjwe = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
			setTimeout(function () {
				$('a[data-href^="sperm-coll.php"]')[0].click();
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
					//console.log(data)
					return {results: data};
				}
			}
		});//End Staff Select2
	})
</script>
