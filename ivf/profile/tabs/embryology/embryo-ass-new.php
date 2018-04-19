<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:42 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GradeQualityDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/EmbrayoStageDAO.php';
 $grade_qty = (new GradeQualityDAO())->all();
 $emb_stage = (new EmbrayoStageDAO())->all();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/EmbryoAssessment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/EmbryoAssessmentData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	@session_start();
	if (!isset($_SESSION['staffID'])) {
		exit('error:Session expired, please log in again');
	}
	//error_log(json_encode($_POST['witness_ids']));
	$data = [];
	$witnesses = array_filter(explode(",", $_POST['witness_ids']));
	$witnesses_ = [];
	foreach ($witnesses as $witness) {
		$witnesses_[] = new StaffDirectory($witness);
	}
	if (is_blank($_POST['day'])) {
		exit('error:Please select the Day');
	}
	foreach ($_POST['embryo_no'] as $i => $item) {
		if (is_blank($_POST['embryo_no'][$i])) {
			exit('error:All inputs for Embryo # are required');
		}
		//if (is_blank($_POST['cell_no'][$i])) {
		//	exit('error:All inputs for Cell # are required');
		//}
		if (is_blank($_POST['grade_quality'][$i])) {
			exit('error:All inputs for Grade are required');
		}
		//if (is_blank($_POST['morula'][$i])) {
		//	exit('error:All inputs for Morula are required');
		//}
		
		if (is_blank($_POST['embrayo_stage'][$i])) {
			exit('error:All inputs for Embrayo stage are required');
		}
		
		//if (is_blank($_POST['blastocyst'][$i])) {
		//	exit('error:All inputs for Blastocyst are required');
		//}
		//if (is_blank($_POST['embryo_state'][$i])) {
		//	exit('error:All inputs for Embryo state are required');
		//}
		
		$data[] = (new EmbryoAssessmentData())->setEmbryoNumber($_POST['embryo_no'][$i])->setCellNumber($_POST['cell_no'][$i])->setQuality($_POST['grade_quality'][$i])->setStage($_POST['embrayo_stage'][$i]);
	}
	$ass = (new EmbryoAssessment())->setInstance(new IVFEnrollment($_POST['aid']))->setCreateDate(date(MainConfig::$mysqlDateTimeFormat))->setCreateUser(new StaffDirectory($_SESSION['staffID']))->setDay($_POST['day'])->setComment($_POST['comment'])->setWitnesses($witnesses_)->setData($data)->add();
	if ($ass != null) {
		exit('success:Data saved');
	}
	exit('error:Failed to save data');
}
?>
<section style="width: 800px;">
	<div class="well">Embryo Assessment</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: __09s__34rujdfl, onComplete: _dkjfi__wefse})">
		<div class="row-fluid">
			<label class="span12">Day <select name="day">
					<?php for ($i = 1; $i <= 31; $i++) { ?>
						<option value="<?= $i ?>"><?= $i ?></option>
					<?php } ?>
				</select> </label>
		</div>
		<div class="paper-card">
			<div class="heading">
				&nbsp;
			</div>
			<div class="card-content">
				<div class="row-fluid">
					<label class="span4">Embryo # <input data-decimals="0" type="number" min="1" max="7" step="1" name="embryo_no[]"> </label>
					<label class="span4 hide">Cell # <input data-decimals="0" type="number" min="1" max="17" step="1" name="cell_no[]"> </label>
					<label class="span4">Grade/Quality
						<select name="grade_quality[]" data-placeholder="select grade quality">
							<option></option>
							<?php foreach ($grade_qty as $gq){ ?>
							  <option value="<?= $gq->getName() ?>"><?= $gq->getName() ?></option>
              <?php } ?>
						</select>
					</label>
					<label class="span4">
						Embrayo Stage
						<select name="embrayo_stage[]" data-placeholder="select stage">
							<option></option>
							<?php foreach ($emb_stage as $e){ ?>
								<option value="<?= $e->getName() ?>"><?= $e->getName() ?></option>
							<?php } ?>

						</select>
					</label>
				</div>
				<div class="row-fluid">
					<label class="span4 hide">Morula <input class="hide" data-decimals="0" type="number" name="morula[]"> </label>
					<label class="span4 hide">Blastocyst <input  data-decimals="0" type="number" name="blastocyst[]"> </label>
					
					<label class="span4 hide">Status
						<select name="embryo_state[]" class="hide">
							<option value="transferred">Transferred</option>
							<option value="vitrified">Vitrified (Cryo-preserved)</option>
							<option value="discarded">Discarded</option>
							<option value="observation">Observation</option>
						</select>
					</label>
				</div>
				<div class="row-fluid">
					
					<!--<label class="span3"><input class="check_state" type="radio" name="embryo_state[0]" value="transferred"> Transferred </label>
					<label class="span3"><input class="check_state" type="radio" name="embryo_state[0]" value="vitrified"> Vitrified/Cryo-preserved) </label>
					<label class="span3"><input class="check_state" type="radio" name="embryo_state[0]" value="discarded"> Discarded </label>
					<label class="span3"><input class="check_state" type="radio" name="embryo_state[0]" value="observation"> Observation </label>-->
				</div>
			</div>
		</div>

		<div class="btn-block">
			<button id="addAssEntry" type="button" class="drop-btn"><i class="icon-plus-sign"></i></button>
		</div>

		<label>Comment <textarea name="comment"></textarea> </label>
		<label>Witness <input data-witnesses type="hidden" name="witness_ids"> </label>
		<!-- witness is a m2m -->
		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
	</form>
</section>
<template id="new-content">
	<div class="paper-card">
		<div class="heading">
			&nbsp;<div class="pull-right">
				<button data-action="remove" type="button" class="btn-danger"><i class="icon-minus-sign"></i></button>
			</div>
		</div>
		<div class="card-content">
			<div class="row-fluid">
				<label class="span4">Embryo # <input data-decimals="0" type="number" min="1" max="7" step="1" name="embryo_no[]"> </label>
				<label class="span4 hide">Cell # <input data-decimals="0" type="number" min="1" max="17" step="1" name="cell_no[]"> </label>
				<label class="span4">Grade/Quality
					<select name="grade_quality[]" data-placeholder="select grade quality">
						<option></option>
						<?php foreach ($grade_qty as $gq){ ?>
							<option value="<?= $gq->getName() ?>"><?= $gq->getName() ?></option>
						<?php } ?>
					</select>
				</label>
				<label class="span4">
					Embrayo Stage
					<select name="embrayo_stage[]" data-placeholder="select stage">
						<option></option>
						<?php foreach ($emb_stage as $e){ ?>
							<option value="<?= $e->getName() ?>"><?= $e->getName() ?></option>
						<?php } ?>

					</select>
				</label>
			</div>
			<div class="row-fluid">
				<label class="span4 hide">Morula <input data-decimals="0" type="number" name="morula[]"> </label>
				<label class="span4 hide">Blastocyst <input data-decimals="0" type="number" name="blastocyst[]"> </label>
				<label class="span4 hide">Status
					<select name="embryo_state[]" class="hide">
						<option value="transferred">Transferred</option>
						<option value="vitrified">Vitrified (Cryo-preserved)</option>
						<option value="discarded">Discarded</option>
						<option value="observation">Observation</option>
					</select>
				</label>
			</div>
			<div class="row-fluid">
				
				<!--<label class="span3"><input class="check_state" type="radio" name="embryo_state[x]" data-key="1" value="transferred"> Transferred </label>
				<label class="span3"><input class="check_state" type="radio" name="embryo_state[x]" data-key="1" value="vitrified"> Vitrified (Cryo-preserved) </label>
				<label class="span3"><input class="check_state" type="radio" name="embryo_state[x]" data-key="1" value="discarded"> Discarded </label>
				<label class="span3"><input class="check_state" type="radio" name="embryo_state[x]" data-key="1" value="observation"> Observation </label>-->
			</div>
		</div>
	</div>
</template>

<script type="text/javascript">
	__09s__34rujdfl = function () {
		$(document).trigger('ajaxSend');
	};

	_dkjfi__wefse = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
			setTimeout(function () {
				$('a[data-href^="embryo-ass.php"]')[0].click();
			}, 50);
		}
	};

	$(document).ready(function () {
		$('#addAssEntry').on('click', function (e) {
			if (!e.handled) {
				$('.paper-card:last').after($('#new-content').html());
				$('select[name="embryo_state[]"]:last').select2({width:'100%'});
				$(document).trigger('ajaxStop');
				$('.paper-card:last .check_state').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
					$(event.currentTarget).trigger('change');
				});
				e.handled = true;
			}
		});
		$('[data-action="remove"]').live('click', function (e) {
			if (!e.handled) {
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
		});//End Staff Select2
		$('.check_state').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});
	})
</script>
