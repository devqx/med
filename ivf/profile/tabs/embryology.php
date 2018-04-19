<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/4/16
 * Time: 9:41 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$instance = escape($_GET['aid']);
?>
<div id="embryoTab">
	<div class="mini-tab">
		<a class="tab" href="javascript:" data-href="sperm-coll.php?aid=<?= $_GET['aid']?>">Sperm Preparation/Analysis</a>
		<a class="tab" href="javascript:" data-href="egg.php?aid=<?= $_GET['aid']?>">Egg Collection</a>
		<a class="tab" href="javascript:" data-href="fertilization.php?aid=<?= $_GET['aid']?>">Fertilization</a>
		<a class="tab" href="javascript:" data-href="embryo-ass.php?aid=<?= $_GET['aid']?>">Embryo Assessment</a>
		<a class="tab" href="javascript:" data-href="transfers.php?aid=<?= $_GET['aid']?>">Embryo Transfers</a>
		<a class="tab" href="javascript:" data-href="outcomes.php?aid=<?= $_GET['aid']?>">Outcomes</a>
	</div>
</div>

<div id="area1">
</div>
<script type="text/javascript">
	$(document).ready(function () {
		setTimeout(function(){$('a[data-href^="sperm-coll.php"]')[0].click();}, 50);

		$('#embryoTab > .mini-tab > a.tab').on('click', function (event) {
			if(!event.handled){
				$('#embryoTab > .mini-tab > a.tab').removeClass('on');
				$(event.target).addClass('on');
				$('#area1').load('/ivf/profile/tabs/embryology/'+$(event.target).data('href'));
				event.handled = true;
			}
		});

	});


</script>

