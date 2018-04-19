<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/1/17
 * Time: 11:51 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SForm.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormCategoryDAO.php';
$SFormCategories = (new SFormCategoryDAO())->all();
$options = SForm::$options;
$ids = explode(',', $_GET['id']);
foreach($ids as $id){
$form = (new SFormDAO())->get($id);
?>
<section style="width:750px">
	<h2 class="fadedText">
		Title: <?= $form->getName() ?>
	</h2>
	<h3 class="fadedText">Category: <?= $form->getCategory()->getName() ?></h3>
	<div class="clearfix ">
		<h4 class="pull-left">Questions:</h4>
		<div class="pull-right"></div>
	</div>
	<?php foreach ($form->getQuestions() as $j => $question) {//$question=new SFormQuestion();?>
		<div class="data">
			<div class="row-fluid">
				<label class="span9">
					<?= $question->getText() ?>
				</label>
				<label class="span3"></label>
			</div>
			<?php if(in_array($question->getType(), ['number', 'text', 'date'])){?>
				<div class="row-fluid">
					<label class="span12">
						<input type="<?=$question->getType()?>" name="question_<?=$question->getId()?>">
					</label>
				</div>
			<?php }?><?php if( in_array($question->getType(), ['longtext'])){?>
				<div class="row-fluid">
					<label class="span12">
						<textarea name="question_<?=$question->getId()?>"></textarea>
					</label>
				</div>
			<?php }?>
			<?php foreach ($question->getOptions() as $i => $option) {//$option=new SFormOption();?>
				<div class="data-options ">
					<div class="row-fluid">
						<label class="span11"><input name="question_<?=$question->getId()?>" type="<?= $question->getType() ?>"> <?= $option->getText() ?></label>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
</section>
<?php }?>


<script type="text/javascript">
	$('input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
		$(event.currentTarget).trigger('change');
	});
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
.form{border-bottom:2px solid #ddd;}
</style>