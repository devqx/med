<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/16/17
 * Time: 2:40 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SForm.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SFormAnswer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SFormQuestion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SFormAnswerOption.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormQuestionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormOptionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$SFormCategories = (new SFormCategoryDAO())->all();
$options = SForm::$options;
$ids = explode(',', $_GET['id']);
//sleep(4);
//todo if this form has been filled before
if($_POST){
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$questionAnswers = $_POST['question'];
	$encounter = new Encounter($_POST['encounter_id']);
	foreach ($questionAnswers as $questionId=>$_answer__){
		$question = (new SFormQuestionDAO())->get($questionId, $pdo);
		$answers = [];
		
		if(is_array($_answer__)) {
			foreach ($_answer__ as $value){
				$answers[] = (new SFormAnswerOption())->setText(in_array($question->getType(), ['radio','checkbox']) ? (new SFormOptionDAO())->get($value, $pdo)->getText(): null)->setOption(in_array($question->getType(), ['radio','checkbox']) ? (new SFormOptionDAO())->get($value, $pdo): null) ;
			}
		} else {
			$answers[] = (new SFormAnswerOption())->setText($_answer__)->setOption(null);
		}
		(new SFormAnswer())->setPatient(new PatientDemograph($_POST['pid']))->setEncounter($encounter)->setQuestion( $question )->setAnswers($answers)->add($pdo);
	}
	$pdo->commit();
	exit('success:Form Data saved');
}

?>
<section style="width: 700px;">
<?php if (!isset($_GET['readonly'])){ ?>
<form method="post" id="_formForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, sFormHandler)">
	<?php } ?>
	<?php
	foreach ($ids as $id) {
		$form = (new SFormDAO())->get($id);
		$Questions = $form->getQuestions();
		
		$sortedQuestions = [];
		foreach ($Questions as $i=>$_){
			//$_ = new SFormQuestion();
			$sortedQuestions[$_->getPage()-1][] = $_;
		}
		?>
		<section class="form">
			<!--<h6 class="fadedText">Title: <?= $form->getName() ?></h6>
			<h6 class="fadedText">Category: <?= $form->getCategory()->getName() ?></h6>-->
			<!--<div class="clearfix "><h4 class="pull-left">Questions:</h4><div class="pull-right"></div></div>-->
			<?php foreach ($sortedQuestions as $i=>$q){?>
				<fieldset>
					<legend>Page <?= ++$i?></legend>
					<?php foreach ($q as $j => $question) {//$question=new SFormQuestion();
						$answer_ = $question->getAnswer($_GET['encounter_id']);
						$answer = !in_array($question->getType(), ['radio', 'checkbox']) ? $answer_[0] : $answer_;
						
						//$answer = new SFormAnswerOption();
						?>
						<div class="data">
							<div class="row-fluid">
								<label class="span9">
									<?= $question->getText() ?>
								</label>
								<label class="span3"></label>
							</div>
							<?php if (in_array($question->getType(), ['number', 'text', 'date'])) { ?>
								<div class="row-fluid">
									<label class="span12">
										<input<?=isset($_GET['readonly']) ? ' disabled':'' ?> type="<?= $question->getType() ?>" name="question[<?= $question->getId() ?>]" required value="<?= $answer ? $answer->getText() : ''?>">
									</label>
								</div>
							<?php } ?><?php if (in_array($question->getType(), ['longtext'])) { ?>
								<div class="row-fluid">
									<label class="span12">
										<textarea<?=isset($_GET['readonly']) ? ' disabled':'' ?> name="question[<?= $question->getId() ?>]"><?= $answer ? $answer->getText() : ''?></textarea>
									</label>
								</div>
							<?php } ?>
							<?php foreach ($question->getOptions() as $i => $option) {//$option=new SFormOption();?>
								<div class="data-options ">
									<div class="row-fluid">
										<label class="span11">
												<input<?=isset($_GET['readonly']) ? ' disabled':'' ?> name="question[<?= $question->getId() ?>][]" type="<?= $question->getType() ?>" value="<?= $option->getId() ?>"<?php foreach($answer as $_){?><?= $_ && $_->getOption() && $_->getOption()->getId()==$option->getId() ? ' checked' : ''?><?php }?>> <?= $option->getText() ?></label>
									</div>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
				</fieldset>
			<?php }?>
		</section>
	<?php } ?>
	<?php if (!isset($_GET['readonly'])){ ?>
	<div class="btn-block">
		<button class="btn" id="submitForm" type="submit">Save</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		<input type="hidden" name="encounter_id" value="<?=$_GET['encounter_id']?>">
		<input type="hidden" name="pid" value="<?=$_GET['pid']?>">
	</div>
</form>
<?php } ?>
</section>
<script type="text/javascript">
	var $Form = $('#_formForm');
	$Form.formToWizard({
		submitButton: 'submitForm',
		showProgress: true, //default value for showProgress is also true
		nextBtnName: 'Next',
		prevBtnName: 'Previous',
		showStepNo: false,
		postStepFn: function () {
		}
	});
	$('input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
		$(event.currentTarget).trigger('change');
	});
	var sFormHandler = {
		onStart: function () {
			$(document).trigger('ajaxSend');
		}, onComplete: function (s) {
			$(document).trigger('ajaxStop');
			var data = s.split(':');
			if(data[0]==='error'){
				Boxy.warn(data[1]);
			} else if(data[0]==='success'){
				Boxy.info(data[1], function () {
					Boxy.get($('.close')).hideAndUnload();
				});
			}
		}
	};
</script>
<style type="text/css" style="display:none">
	.data {
	display: block;
	background: #FEFEFE;
	border: 2px solid #FAFAFA;
	box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
	margin: 5px 0 10px 0 !important;
	/*-webkit-column-break-inside: avoid;*/
	/*-moz-column-break-inside: avoid;*/
	/*column-break-inside: avoid;*/
	padding: 5px;
	background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
	opacity: 1;

	-webkit-transition: all .2s ease;
	-moz-transition: all .2s ease;
	-o-transition: all .2s ease;
	transition: all .2s ease;
	}

	.svgcloser, .add-icon {
	cursor: pointer;
	opacity: 0.5;
	}
	.svgcloser:hover, .add-icon:hover {
	opacity: 1;
	}
	.add-icon {
	vertical-align: -10px;margin-right: 10px;cursor: pointer;
	}
	.form{border-top:5px solid #ddd;}
</style>

