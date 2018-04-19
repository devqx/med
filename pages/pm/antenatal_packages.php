<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php'; ?>
<script type="text/javascript">
	$(document).ready(function () {
		showTabs(1);
		var ntot = $("#tabbedPane a").length;
		$("#tabbedPane a").css({'min-width': ($("#tabbedPane").width() / ntot) - 30});
	});
	function loading() {
		$("#contentPane").html('<table align="center" width="100%" height="200"><tr><td valign="middle" align="center"><img src="/img/loading_large.gif" class="preloader" /></td></tr></table>');
	}
	function activateTab(t, urn, linkText, linkURL) {
		$(".mini-tab a").each(function () {
			$(this).removeClass("on");
		});
		$('#tab-' + t).addClass('on');
		if (linkText != null && linkURL !== null) {
			t1Link = '<div style="float:right;margin-top:15px" class="action"><i class="icon-plus"></i><a href="javascript:void(0)" onclick="Boxy.load(\'' + linkURL + '\',{title:\'' + linkText + '\', afterShow: function(){$(\'#file\').hide(\'fast\');}})">' + linkText + '</a></div></div>';
		} else {
			t1Link = '';
		}

		setTimeout(function () {
			$('#newLink').html(t1Link);
		}, 0);
		$.ajax({
			url: urn,
			cache: true,
			success: function (s) {
				$("#contentPane").html(s);
				setTimeout(function () {
					$("#contentPane table").dataTable();
				}, 5);

			}, beforeSend: function () {
//            loading();
			}, error: function () {
				$("#contentPane").html('<div class="warning-bar">Failed to load document</div>');
			}
		});
	}
	function showTabs(t) {
		if (t == 1) {
			activateTab(t, '/pm/antenatal/packages.php', 'Add New Package', '/pages/pm/antenatal/boxy.addpackage.php');
		}
		else {
		}
	}
	
	//-->
</script>
<div>
	<div id="tabbedPane" style="margin-bottom: -6px;" class="mini-tab">
		<a href="javascript:void(0)" class="tab on" id="tab-1" onClick="showTabs(1)"><span></span> Antenatal Packages</a>
	</div>
	<div id="contentPane__">
		<div><span id="newLink"></span></div>
		<span id="contentPane"></span>
	</div>
	<br clear="left"/>
</div>
