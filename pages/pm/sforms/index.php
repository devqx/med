<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/28/17
 * Time: 9:19 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormDAO.php';
$data = (new SFormDAO())->all();
?>
<h5>Available SForms <span class="pull-right"><a id="new_s_form" class="action" href="javascript:">Create</a></span></h5>
<ul class="list-blocks sforms" style="margin-left: 15px;">
	<?php foreach ($data as $datum){?>
	<li class="tag">
		<?=$datum->getName()?>
		<div class="pull-right">
			<a href="javascript:" class="edit_sform" data-href="/pages/pm/sforms/sform.view.php?id=<?=$datum->getId()?>"><i class="icon-eye-open"></i></a> |
			<a href="javascript:" class="edit_sform" data-href="/pages/pm/sforms/sform.edit.php?id=<?=$datum->getId()?>"><i class="icon-pencil"></i></a>
		</div>
	</li>
	<?php }?>
</ul>

<script type="text/javascript">
	$(document).ready(function () {
	}).on('click', '#new_s_form', function (e) {
		if(!e.handled){
			Boxy.load('/pages/pm/sforms/sform.new.php');
			e.handled = true;
		}
	}).on('click', '.edit_sform', function (e) {
		if(!e.handled){
			Boxy.load($(e.target).closest('.edit_sform').data('href'));
			e.handled = true;
		}
	});
</script>
