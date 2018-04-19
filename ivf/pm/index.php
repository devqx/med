
<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 2:54 PM
 */

include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
?>
<section>
	<div class="mini-tab">
		<a class="tab" href="javascript:;" data-href="pgd_labs.php">PGD Labs</a>
		<a class="tab" href="javascript:;" data-href="templates.php">Templates</a>
		<a class="tab" href="javascript:;" data-href="reagents.php">Reagents</a>
		<a class="tab" href="javascript:;" data-href="specimen_types.php">Specimen Types</a>
		<a class="tab on" href="javascript:;" data-href="quality_control.php">Quality Control Parameters</a>
		<a class="tab on" href="javascript:;" data-href="treatment/package.php">Packages</a>
		<a class="tab on" href="javascript:;" data-href="analysis_template.php">Analysis Templates</a>
		<a class="tab on" href="javascript:;" data-href="ivf_drugs.php">Drugs</a>
	</div>
	<div class="content"></div>
</section>
<script type="text/javascript">
	$(document).on('click', '.mini-tab > .tab', function (e) {
		if (!e.handled) {
			$('.mini-tab > .tab').removeClass('on');
			$(e.target).addClass('on');
			$('.mini-tab + .content').load('/ivf/pm/' + $(e.target).data('href'), function (response, status) {
				if (status == "error") { // already throws ajax error
				}
			});
			e.handled = true;
		}
	}).on('click', '.action.newBtn', function (e) {
		if (!e.handled) {
			$('.mini-tab + .content').load('/ivf/pm/' + $(e.target).data('href'), function (response, status) {
				if (status == "error") { // already throws ajax error
				}
			});
			e.handled = true;
		}
	}).on('click', 'button[reset]', function (e) {
		if (!e.handled) {
			var element = e.target;
			$(element).parent('form').get(0).reset();
			$('.mini-tab > .tab.on').click();
			e.handled = true;
		}
	}).on('click', '.edit', function (e) {
		if (!e.handled) {
			$('.mini-tab + .content').load( $(e.target).data('href'), function (response, status) {
				if (status == "error") { // already throws ajax error
				}
			});
			e.handled = true;
		}
	}).ready(function () {
		$('.mini-tab > .tab:first').click();
	});
</script>

