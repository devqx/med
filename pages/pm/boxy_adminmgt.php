<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.sip.php';
?>
<script>
	function start() {
		$('.loader').html('<img src="/img/loading.gif"> please wait...');
	}

	function finishedConfig(s) {
		var str = s.split(":");
		if (str[0] === "error") {
			$('.loader').html('<span class="alert-error">' + str[1] + '</span>');
		} else {
			$('.loader').html('<span class="alert-info">' + str[1] + '</span>');
			$('#stafftype').val('');
		}
	}

	function end(s) {
		$('.loader').html('');
		if (s.indexOf("success") !== -1) {
			$('span.error').html("");
			$('.loader').html('<span class="alert-success">update successful</span>').attr('data', "staffCharges");
		} else {
			s1 = s.split(":");
			if (s1[0] === "error") {
				$('span.error').html('<img src="/img/warning.png" align="absmiddle" /><span class="alert alert-error">' + s1[1] + '</span>');
			}
		}
	}
	function finish(s) {
		if (s.indexOf("success") !== -1) {
			$('span.error').html("");
			$('#creator').html("Operation Completed Successfully").attr('data', "none");
			$('#existingUsers').load("/pages/pm/getUserList.php");
		} else {
			s1 = s.split(":");
			if (s1[0] === "error") {
				$('span.error').html('<img src="/img/warning.png" align="absmiddle" /><span class="alert alert-error">' + s1[1] + '</span>');
			}
		}
	}
	function loadLogo() {
		setTab(1);
		$('#creator').load('/pages/pm/logo.php').attr('data', "logo");
		$('span.error').html("");
	}
	function loadCfgOprType() {
		setTab(2);
		$('#creator').load('/pages/pm/cfgOprType.php').attr('data', "cfgOprType");
		$('span.error').html("");
	}
	function loadStaffCharges() {
		setTab(3);
		$('#creator').load('/pages/pm/staffCharges.php').attr('data', "staffCharges");
		$('span.error').html("");
	}
	function loadCfgXamRoom() {
		setTab(4);
		$('#creator').load('/pages/pm/cfgXamRoom.php', function () {
			$(this).find('table').dataTable();
		}).attr('data', "cfgXamRoom");
		$('span.error').html("");
	}

	function loadCompanies() {
		setTab(5);
		$('#creator').load('/pages/pm/companies.php', function () {
			$(this).find('table').dataTable();
		});
		$('span.error').html("");
	}
	function loadDocTypes() {
		setTab(6);
		$('#creator').load('/pages/pm/document_types.php', function () {
			$(this).find('table').dataTable();
		});
		$('span.error').html("");
	}
	function loadSipDomain() {
		$('#creator').load('/pages/pm/sip_domain.php', function () {
			$(this).find('table').dataTable();
		});
		$('span.error').html("");
	}


	function setTab(i) {
		$('a.tab').removeClass('on');
		$('a.tab:nth-child(' + i + ')').addClass('on');
	}
</script>
<div id="adminmgtmain">
	<div class="mini-tab">
		<a href="javascript:void(0)" class="tab" onclick="loadLogo()">Add/Edit Logo</a>
		<a href="javascript:void(0)" class="tab" onclick="loadCfgOprType()">Configure Operator Types</a>
		<a href="javascript:void(0)" class="tab" onclick="loadStaffCharges()">Staff & Charges</a>
		<a href="javascript:void(0)" class="tab" onclick="loadCfgXamRoom()">Configure Exam Rooms</a>
		<a href="javascript:;" class="tab" onclick="loadCompanies()">Companies</a>
		<a href="javascript:;" class="tab" onclick="loadDocTypes()">Document Types</a>
		<?php if (SipConfig::$enabled) { ?>
			<a href="javascript:void(0)" onclick="loadSipDomain();">Create Sip Domain</a> <?php } ?>

	</div>
	<span class="error"></span>
	<div id="creator" data="none"></div>
	<div id="existingUsers"></div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		loadLogo();
	});
</script>
