<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/29/16
 * Time: 2:18 PM
 */

?>
<section style="width: 1000px;">
	<div class="mini-tab">
		<a href="javascript:;" class="tab" data-href="/pages/pm/pharmacy/requisitions/open.php">Open Requisitions</a>
		<a href="javascript:;" class="tab" data-href="/pages/pm/pharmacy/requisitions/find.php">Find Requisitions</a>
		<a href="javascript:;" class="tab pull-right" data-href="/pages/pm/pharmacy/requisitions/new.php">New Requisition</a>
	</div>
	<div class="clear"></div>
	<div class="content" style="margin-left:0">
	</div>
</section>
<script type="text/javascript">
	$(document).on('click', '.mini-tab > a.tab', function (e) {
		if(!e.handled){
			$('.mini-tab > .tab').removeClass('on');
			$(e.target).addClass('on');
			$('.mini-tab + .clear + .content').load($(e.target).data("href"));
			e.handled = true;
		}
	}).ready(function (e) {
		$('.mini-tab > a.tab:first').click();
	})
</script>