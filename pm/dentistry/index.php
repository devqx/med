<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php'; ?>
<script>
	function start() {
		$('.loader').html('<img src="/img/loading.gif"> please wait...');
	}

	function loadServices() {
		setTab(1);
		$('#creator').load('/pages/pm/dentistry/services.php');
		$('span.error').html("");
		$("#category_id").select2();
	}
	function loadTemplates() {
		setTab(2);
		$('#creator').load('/pages/pm/dentistry/templates.php');
		$('span.error').html("");
	}
	function loadServiceCenter() {
		setTab(3);
		$('#creator').load('/pages/pm/dentistry/service_center.php');
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
		<a href="javascript:void(0)" class="tab" onclick="loadServices()">Add/Edit Services</a>
		<a href="javascript:void(0)" class="tab" onclick="loadTemplates()">Load Templates</a>
		<a href="javascript:void(0)" class="tab" onclick="loadServiceCenter()">Load Service Centers</a>
	</div>
	<span class="error"></span>
	<div id="creator"></div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		loadServices();
	});
</script>
