<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php'; ?>
<script>
	function start() {
		$('.loader').html('<img src="/img/loading.gif"> please wait...');
	}

	function loadScans() {
		setTab(1);
		$('#creator').load('/pages/pm/imaging/scans.php').attr('data', "scan");
		$('span.error').html("");
		$("#category_id").select2();
	}
	function loadScanTemplates() {
		setTab(2);
		$('#creator').load('/pages/pm/imaging/scanTemplate.php').attr('data', "scanTemplate");
		$('span.error').html("");
	}

	function setTab(i) {
		$('a.tab').each(function () {
			$(this).removeClass('on');
		});
		$('a.tab:nth-child(' + i + ')').addClass('on');
	}
</script>
<div id="imagerymgtmain">
	<div class="mini-tab">
		<a href="javascript:void(0)" class="tab" onclick="loadScans()">Add/Edit Scans</a>
		<a href="javascript:void(0)" class="tab" onclick="loadScanTemplates()">Load Imaging Templates</a>
	</div>
	<span class="error"></span>
	<div id="creator" data="none"></div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		loadScans();
	});
</script>
