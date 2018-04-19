<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/28/17
 * Time: 9:54 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SFormOption.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SFormCategory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SFormQuestion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SForm.php';
$SFormCategories = (new SFormCategoryDAO())->all();
$options = SForm::$options;
if ($_POST) {
	$formRaw =  json_decode(json_encode((object)$_POST)); //to convert it to a stdClass deeply affected
	$questions = [];
	foreach ($formRaw->question as $questionRaw){
		$qOptions = [];
		if(isset($questionRaw->option)) {
			foreach ($questionRaw->option as $item) {
				$qOptions[] = (new SFormOption())->setText($item);
			}
			unset($item);
		}
		$questions[] = (new SFormQuestion())->setText($questionRaw->text)->setType($questionRaw->type)->setOptions($qOptions)->setPage($questionRaw->page);
	}
	unset($questionRaw);
	$form = (new SForm)->setName($formRaw->name)->setCategory( new SFormCategory($formRaw->category_id) )->setQuestions($questions);
	
	if($form->add()){
		exit('success:Form Saved');
	}
	exit('error:Form failed to save');
}
?>
<section style="width:950px">
	<form name="myform" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: newSFormHandler.start, onComplete:newSFormHandler.complete})">
		<label>
			Title <input type="text" name="name" required>
		</label>
		<label>Category <span class="pull-right">
				<a href="javascript:" class="add_sform_category_">Add</a>
			</span> <select name="category_id" data-placeholder="Select Form Category" required>
				<option></option>
				<?php foreach ($SFormCategories as $c) { ?>
					<option value="<?= $c->getId() ?>"><?= $c->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<div class="clearfix ">
			<h4 class="pull-left">Questions:</h4>
			<div class="pull-right"><i class="icon-plus-sign icon-2x add-icon" onclick="addMoreQuestion(this)"></i></div>
		</div>

		<div class="data">
			<div class="row-fluid">
				<label class="span8">
					<input type="text" name="question[0][text]" placeholder="Question Text" required>
				</label>
				<label class="span3"><select name="question[0][type]" data-placeholder="-- Option Type --">
						<?php foreach ($options as $option) { ?>
							<option value="<?= $option ?>"><?= $option ?></option><?php } ?>
					</select> </label>
				<label class="span1" title="Section/Page #"><input type="number" name="question[0][page]" data-decimals="0" value="1" style="width: 40px;" required> </label>
			</div>

			<div class="data-options ">
				<div class="row-fluid">
					<label class="span11">
						<input type="text" name="question[0][option][]" placeholder="Option Text" required="required">
					</label>
					<label class="span1">
						<i class="icon-plus-sign icon-2x add-icon" data-index="0" onclick="addMoreOption(this, '.row-fluid')"></i>
					</label>
				</div>
			</div>
		</div>

		<div class="btn-block">
			<button type="submit" class="btn" onclick="invalidate()">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>

	</form>
</section>

<template id="more-options">
	<div class="row-fluid">
		<label class="span11">
			<input type="text" name="question[index0][option][]" placeholder="Option Text" required="required">
		</label>
		<label class="span1">
		<span class="">
			<svg class="svgcloser" onclick="remove(this, '.row-fluid')" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 10 10" focusable="false">
				<polygon fill="#000000" points="10,1.01 8.99,0 5,3.99 1.01,0 0,1.01 3.99,5 0,8.99 1.01,10 5,6.01 8.99,10 10,8.99 6.01,5"></polygon>
			</svg>
		</span>
		</label>
	</div>
</template>

<template id="more-question">
	<div class="data">
		<span class="pull-right">
			<svg class="svgcloser" onclick="remove(this, '.data')" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 10 10" focusable="false">
				<polygon fill="#000000" points="10,1.01 8.99,0 5,3.99 1.01,0 0,1.01 3.99,5 0,8.99 1.01,10 5,6.01 8.99,10 10,8.99 6.01,5"></polygon>
			</svg>
		</span>
		
		<div class="row-fluid">
			<label class="span8 no-label">
				<input type="text" name="question[index0][text]" placeholder="Question Text" required>
			</label>
			<label class="span3 no-label"><select name="question[index0][type]" data-placeholder="-- Option Type --">
					<?php foreach ($options as $option) { ?>
						<option value="<?= $option ?>"><?= $option ?></option><?php } ?>
				</select> </label>
			<label class="span1" title="Section/Page #"><input type="number" name="question[index0][page]" data-decimals="0" value="1" style="width: 40px;" required> </label>

		</div>

		<div class="data-options ">
			<div class="row-fluid">
				<label class="span11">
					<input type="text" name="question[index0][option][]" placeholder="Option Text" required="required">
				</label>
				<label class="span1">
					<i class="icon-plus-sign icon-2x add-icon" data-index="index0" onclick="addMoreOption(this, '.row-fluid')"></i>
				</label>
			</div>
		</div>
	</div>
</template>

<script type="text/javascript">
	$('.boxy-content form').on('change', 'select[name^="question"]', function (e) {
		var $this = $(this);
		if (!e.handled) {
			if ( !_.includes(['radio', 'checkbox'], $this.val())) {
				$this.parent().parent().next('.data-options').hide();
			} else {
				$this.parent().parent().next('.data-options').show();
			}
			e.handled = true;
		}
	}).on('click', '.add_sform_category_', function (e) {
		var $this = $(this);
		if (!e.handled) {
			Boxy.load('/pages/pm/sforms/sform.category.new.php');
			e.handled = true;
		}
	}).ready(function () {
		$('select[name^="question"]').trigger('change');
	});
	
	var newSFormHandler = {
		start: function () {
			$(document).trigger('ajaxSend');
		}, complete: function (s) {
			$(document).trigger('ajaxStop');
			var data = s.split(':');
			if (data[0] === 'error') {
				Boxy.warn(data[1]);
			} else if (data[0] === 'success') {
				$('.close').click();//use this to close ALL open Boxy`s
				setTimeout(function () {
					hideAll(23);
				}, 100);
			}
		}
	};
	
	var invalidate = function () {
		$('[name*="[option]"]:hidden').removeAttr('required').prop('disabled', true);
		$('[name*="[option]"]:visible').prop('required', true).prop('disabled', false);
	};

	var addMoreOption = function (e, className) {
		//fixme items added are not sequential
		$(e).closest(className).after($('#more-options').html().replace(/index0/g, $(e).data('index')));
	};

	var i = 0;
	var addMoreQuestion = function (e) {
		$('.data:last').after($('#more-question').html().replace(/index0/g, ++i));
		$('select[name^="question"]:last').select2({width: '100%'}).trigger('change');
		$('input[name^="question"]:last').number(true, 1);
		$("section.boxy-content:last").animate({scrollTop: $('section.boxy-content:last').get(0).scrollHeight}, "slow");
	};

	var remove = function (e, className) {
		//if(".data"===className){--i;} ////no need: just keep incrementing;
		// it doesn't matter coming back because we can't re-index
		$(e).closest(className).remove();
	};
</script>
<style type="text/css" style="display:none">
	.add-icon {zoom:75%}
	form > .data {
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
</style>
