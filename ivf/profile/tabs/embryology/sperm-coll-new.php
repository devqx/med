<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:43 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SpermProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SampleSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SampleStateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFAnalysisTemplatesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SpermPreparationMethodDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/SpermPreparation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';

$sperm_pre = (new IVFAnalysisTemplatesDAO())->all('Pre Sperm Analysis');
$sperm_post = (new IVFAnalysisTemplatesDAO())->all('Post Sperm Analysis');

$procedures = (new SpermProcedureDAO())->all();
$sources = (new SampleSourceDAO())->all();
$states = (new SampleStateDAO())->all();
$sperm_pre_methods = (new SpermPreparationMethodDAO())->all();

if($_POST){
	if(is_blank($_POST['source_id'])){exit('error:Sample Source is required');}
	if(is_blank($_POST['state_id'])){exit('error:Sample State is required');}
	if(is_blank($_POST['donor_code'])){exit('error:Donor Code is required');}
	if(is_blank($_POST['abstinence_days'])){exit('error:Days of abstinence is required');}
	if(is_blank($_POST['collection_time'])){exit('error:Collection time is required');}
	if(is_blank($_POST['production_time'])){exit('error:Production time is required');}
	if(is_blank($_POST['procedure_id'])){exit('error:Procedure is required');}
	if(is_blank($_POST['analysis_pre'])){exit('error:Pre-analysis is required');}
	if(is_blank($_POST['analysis_post'])){exit('error:Post-analysis is required');}
	if(is_blank($_POST['aid'])){exit('error:A general id error');}

	$witnesses = [];
	$post = json_encode(explode(',', @$_POST['witness_ids'][0]));
	foreach (json_decode($post) as $wit) {
		if ((int)$wit !== 0) {
			$witnesses[] = (int)$wit;
		}
	}

	$save = (new SpermPreparation())->setInstance(new IVFEnrollment($_POST['aid']))->setTimeEntered(date(MainConfig::$mysqlDateTimeFormat))->setUser(new StaffDirectory($_SESSION['staffID']))->setSource(new SampleSource($_POST['source_id']))->setState(new SampleState($_POST['state_id']))->setDonorCode($_POST['donor_code'])->setProcedure(new SpermProcedure($_POST['procedure_id']))->setAbstinenceDays($_POST['abstinence_days'])->setCollectionDate($_POST['collection_time'])->setWitnesses($witnesses)->setPreAnalysisReport($_POST['analysis_pre'])->setPostAnalysisReport($_POST['analysis_post'])->setProductionTime($_POST['production_time'])->setAnalysisTime($_POST['analysis_time'])->setPreparationMethod($_POST['preparation_method'])->add();

	if($save !== null){
		exit('success:Data saved!');
	}
	exit('error:Failed to save data');
}
?>
<section style="width: 650px;">
	<div class="well">Sperm Preparation</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: __039__45sdfl, onComplete: __ksd__4rjwe})">
		<div class="row-fluid">
			<label class="span4">Source <select name="source_id" data-placeholder="-- select --"><?php foreach ($sources as $source){?><option value="<?= $source->getId()?>"><?= $source->getName() ?></option><?php }?></select> </label>
			<label class="span4">State <select name="state_id" data-placeholder="-- select --"><?php foreach ($states as $state){?><option value="<?= $state->getId()?>"><?= $state->getName()?></option><?php }?></select> </label>
			<label class="span4">Donor Code <input type="text" name="donor_code"> </label>
		</div>
		<div class="row-fluid">
			<label class="span6">Abstinence <i class="required icon icon-question-sign" title="# of days without sexual activity"></i> <input data-decimals="0" type="number" name="abstinence_days"> </label>
			<label class="span6">Sample Collection Date <input type="text" name="collection_time"> </label>
		</div>
		<div class="row-fluid">
			<label class="span6">Production Date/Time <input type="text" name="production_time"></label>
			<label class="span6">Method of production <select name="procedure_id" data-placeholder="-- select --"><?php foreach ($procedures as $procedure){?><option value="<?= $procedure->getId()?>"><?= $procedure->getName()?></option><?php }?></select></label>
		</div>
		<div class="row-fluid">
			<label class="span6">Analysis Date/Time <input type="text" name="analysis_time"> </label>
			<label class="span6">Method of Preparation
				<select name="preparation_method" placeholder="--select method of preparation--">
					<option></option>
					<?php foreach ($sperm_pre_methods as $spm){ ?>
						<option value="<?= $spm->getName() ?>"><?= $spm->getName() ?></option>
					 <?php } ?>
				</select>
			</label>
		</div>
		<label>Pre-analysis Report<textarea name="analysis_pre">
			  <?= $sperm_pre ? $sperm_pre[0]->getNote() : '' ?>
			</textarea> </label>
		<label>Post-analysis Report <textarea name="analysis_post">
				<?= $sperm_post ? $sperm_post[0]->getNote() : '' ?>
			</textarea> </label>
		<label>Witness(es) <input data-witnesses type="hidden" name="witness_ids[]"> </label>
		<!-- witness is a m2m -->
		<p></p>
		<input type="hidden" name="aid" value="<?=$_GET['aid']?>">
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	__039__45sdfl = function () {
		$(document).trigger('ajaxSend');
	};

	__ksd__4rjwe = function (s) {
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
		$('[name="analysis_pre"]').summernote(SUMMERNOTE_CONFIG);
		$('[name="analysis_post"]').summernote(SUMMERNOTE_CONFIG);

		$('[name="collection_time"]').datetimepicker({
			onShow: function (ct) {
				this.setOptions({maxDate: new Date().toISOString().split('T')[0]});
			}
		});
		$('[name="production_time"]').datetimepicker({
			onShow: function (ct) {
				this.setOptions({maxDate: new Date().toISOString().split('T')[0]});
			}
		});
		$('[name="analysis_time"]').datetimepicker({
			onShow: function (ct) {
				this.setOptions({maxDate: new Date().toISOString().split('T')[0]});
			}
		});

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

