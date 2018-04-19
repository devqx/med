<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:43 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/Insemination.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/SampleSource.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SampleSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$methods = (new IVFMethodDAO())->all();
$sources = (new SampleSourceDAO())->all();
if($_POST){
	if(is_blank($_POST['method_id'])){exit('error:Select method of insemination');}
	if(is_blank($_POST['source_id'])){exit('error:Select source');}
	if(is_blank($_POST['total_eggs'])){exit('error:Total # of eggs used?');}
	if(is_blank($_POST['total_sperm'])){exit('error:Total # of sperm cells used?');}
	if(is_blank($_POST['comment'])){exit('error:Comment is required');}
	$witnesses = [];
	$post = json_encode(explode(',', @$_POST['witness_ids'][0]));
	foreach (json_decode($post) as $wit) {
		if ((int)$wit !== 0) {
			$witnesses[] = (int)$wit;
		}
	}
	$save = (new Insemination())->setUser(new StaffDirectory($_SESSION['staffID']))->setInstance(new IVFEnrollment($_POST['aid']))->setMethod(new IVFMethod($_POST['method_id']))->setSource(new SampleSource($_POST['source_id']))->setTotalEggs($_POST['total_eggs'])->setTotalSperm($_POST['total_sperm'])->setComment($_POST['comment'])->setWitnesses($witnesses)->add();
	if ($save !== null) {
		exit('success:Data saved');
	}
	exit('error:Failed to save data');

}
?>
<section style="width: 400px;">
	<div class="well">Insemination/Injection</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: __sdfl, onComplete: __rjwe})">
		<div class="row-fluid">
			<label class="span6">Method <select name="method_id" data-placeholder="-- select --"><?php foreach($methods as $method){?><option value="<?= $method->getId()?>"><?= $method->getName()?></option><?php }?></select> </label>
			<label class="span6">Source <select name="source_id" data-placeholder="-- select --"><?php foreach ($sources as $source){?><option value="<?= $source->getId()?>"><?= $source->getName() ?></option><?php }?></select> </label>
		</div>
		<div class="row-fluid">
			<label class="span6"># of Eggs <input data-decimals="0" type="number" step="1" min="1" name="total_eggs"> </label>
			<label class="span6"># of Sperms <input data-decimals="0" type="number" step="any" name="total_sperm"> </label>
		</div>

		<label>Comment <textarea name="comment"></textarea> </label>
		<label>Witness <input type="hidden" data-witnesses name="witness_ids[]"> </label>
		<!-- witness is a m2m -->
		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="aid" value="<?= $_GET['aid']?>">
	</form>
</section>
<script type="text/javascript">
	__sdfl = function () {
		$(document).trigger('ajaxSend');
	};

	__rjwe = function (s) {
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
					//console.log(data)
					return {results: data};
				}
			}
		});//End Staff Select2
	})
</script>
