<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 12:23 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/TransferTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GradeQualityDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/EmbrayoStageDAO.php';


$transfers = (new TransferTypeDAO())->all();
$grade_qty = (new GradeQualityDAO())->all();
$emb_stage = (new EmbrayoStageDAO())->all();


$maxDays = 31;

if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/Transfer.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/TransferData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/TransferType.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	@session_start();
	if(!isset($_SESSION['staffID'])){exit('error:Session expired, please log in again');}
	$data = [];
	$witnesses = array_filter(explode(",", $_POST['witness_ids']));
	$witnesses_ = [];
	foreach ($witnesses as $witness){
		$witnesses_[] = new StaffDirectory($witness);
	}
	if (is_blank($_POST['day_no'])) {
		exit('error:Please select the Day');
	}
	
	foreach ($_POST['total_embryos'] as $i => $item){
		//if (is_blank($_POST['cell_no'][$i])) {
		//	exit('error:All inputs are required');
		//}
		
		if (is_blank($_POST['total_embryos'][$i])) {
			exit('error:All inputs are required');
		}
		if (is_blank($_POST['transfer_method_id'][$i])) {
			exit('error:All inputs are required');
		}
		
		$data[] = (new TransferData())->setQaulity($_POST['grade_quality'][$i])->setEmbrayoStage($_POST['embrayo_stage'][$i])
			->setCellsTransferred($_POST['total_embryos'][$i])->setType( new TransferType($_POST['transfer_method_id'][$i]));
	}
	$ass = (new Transfer())->setInstance(new IVFEnrollment($_POST['aid']))
		->setCreateDate(date(MainConfig::$mysqlDateTimeFormat))
		->setCreateUser(new StaffDirectory($_SESSION['staffID']))
		->setDay($_POST['day_no'])
		->setComment($_POST['comment'])->setWitnesses($witnesses_)
		->setData($data)->add();
	if($ass != null){
		exit('success:Data saved');
	}
	exit('error:Failed to save data');
}
?>
<section style="width: 600px;">
	<div class="well">New Transfer Record</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: _sdkjfsdsdfl, onComplete: __sdlkfiuwe_nmzc})">
		<div class="row-fluid">
			<label class="span8">Day <select name="day_no">
					<?php for ($i = 1; $i <= $maxDays; $i++) { ?>
						<option value="<?= $i ?>"><?= $i ?></option>
					<?php } ?>
				</select> </label>
		</div>
		<div class="paper-card">
			<div class="card-content">
				<div class="row-fluid">
					<!--<label class="span4 hide">Cell # <input name="cell_no[]" type="number" min="1" max="17" step="1"> </label>-->
					<label class="span4"># of embryos transferred <input type="number" step="1" min="1" name="total_embryos[]"> </label>
					<label class="span4">Grade/Quality
						<select name="grade_quality[]" data-placeholder="select grade quality">
							<option></option>
							<?php foreach ($grade_qty as $gq){ ?>
								<option value="<?= $gq->getName() ?>"><?= $gq->getName() ?></option>
							<?php } ?>
						</select>
					</label>
					
				</div>
				<div class="row-fluid">
					<label class="span4">
						Embrayo Stage
						<select name="embrayo_stage[]" data-placeholder="select stage">
							<option></option>
							<?php foreach ($emb_stage as $e){ ?>
								<option value="<?= $e->getName() ?>"><?= $e->getName() ?></option>
							<?php } ?>

						</select>
					</label>
					<label class="span4">
						Transfer Type <select name="transfer_method_id[]" data-placeholder="-- select --"><?php foreach ($transfers as $transfer) { ?>
								<option value="<?= $transfer->getId() ?>"><?= $transfer->getName() ?></option><?php } ?></select>
					</label>
				</div>
			</div>
		</div>
		<div class="btn-block"><button id="addTrxEntry" type="button" class="drop-btn"><i class="icon-plus-sign"></i></button></div>


		<label>Comment <textarea name="comment"></textarea> </label>
		<label>Witness(es) <input data-witnesses type="hidden" name="witness_ids"> </label>
		<!-- witness is a m2m -->
		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
	</form>
</section>
<template id="new-trx-content">
	<div class="paper-card">
		<div class="heading">
			&nbsp;<div class="pull-right"><button data-action="remove" type="button" class="btn-danger"><i class="icon-minus-sign"></i></button></div>
		</div>
		<div class="card-content">
			<div class="row-fluid">
				<!--<label class="span4">Cell # <input name="cell_no[]" type="number" min="1" max="17" step="1"> </label>-->
				<label class="span4"># of Embryos Transferred <input type="number" step="1" min="1" name="total_embryos[]"> </label>
				<label class="span4">Grade/Quality
					<select name="grade_quality[]" data-placeholder="select grade quality">
						<option></option>
						<?php foreach ($grade_qty as $gq){ ?>
							<option value="<?= $gq->getName() ?>"><?= $gq->getName() ?></option>
						<?php } ?>
					</select>
				</label>
			</div>
			<div class="row-fluid">
				<label class="span4">
					Embrayo Stage
					<select name="embrayo_stage[]" data-placeholder="select stage">
						<option></option>
						<?php foreach ($emb_stage as $e){ ?>
							<option value="<?= $e->getName() ?>"><?= $e->getName() ?></option>
						<?php } ?>

					</select>
				</label>
				<label class="span4">Transfer Type <select name="transfer_method_id[]" data-placeholder="-- select --"><?php foreach ($transfers as $transfer) { ?>
							<option value="<?= $transfer->getId() ?>"><?= $transfer->getName() ?></option><?php } ?></select> </label>
			</div>
		</div>
	</div>
</template>
<script type="text/javascript">
	_sdkjfsdsdfl = function () {
		$(document).trigger('ajaxSend');
	};
	__sdlkfiuwe_nmzc = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
			setTimeout(function () {
				$('a[data-href^="transfers.php"]')[0].click();
			}, 50);
		}
	};

	$(document).ready(function () {
		$('#addTrxEntry').on('click', function (e) {
			if(!e.handled){
				$('.paper-card:last').after($('#new-trx-content').html());
				$(document).trigger('ajaxStop');
				$('.paper-card:last select').select2({width:'100%'});
				e.handled = true;
			}
		});
		$('[data-action="remove"]').live('click', function (e) {
			if(!e.handled){
				$(this).parentsUntil('.paper-card').parent('.paper-card').remove();
				e.handled = true;
			}
		});
		$('[name="witness_ids"]').select2({
			placeholder: "Staff Name (Specialization [Staff ID])",
			allowClear: true,
			multiple: true,
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
		});

		//e.preventDefault();
	});//End Staff Select2
</script>
