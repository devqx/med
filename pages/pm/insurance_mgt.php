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
		if (linkText !== undefined && linkURL !== null) {
			t1Link = '<div style="float:right;margin-top:15px; margin-bottom:5px"><button class="action drop-btn" href="javascript:void(0)" onclick="Boxy.load(\'' + linkURL + '\',{title:\'' + linkText + '\', afterShow: function(){$(\'#file\').hide(\'fast\');}})">' + linkText + '</button></div></div>';
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

			}, beforeSend: function () {//loading();
			}, error: function () {
				$("#contentPane").html('<div class="warning-bar">Failed to load document</div>');
			}
		});
	}

	function showTabs(t) {
		if (t === 1) {
			activateTab(t, '/pm/insurance/insuranceprofiles.php', 'Add New Profile', '/pages/pm/insurance/boxy.addprofile.php');
		} else if (t === 2) {
			activateTab(t, '/pm/insurance/insuranceschemes.php', 'New Insurance Scheme', '/pages/pm/insurance/boxy.addscheme.php');
		} else if (t === 3) {
			activateTab(t, '/pm/insurance/billableitems.php', null, null);
		} else if (t === 4) {
			activateTab(t, '/pm/accounts/payment_methods.php', 'Add Payment Method', '/pm/accounts/boxy.add_payment_method.php');
		} else if (t === 5) {
			activateTab(t, '/pages/pm/costCentres.php', null, null);
		} else if (t === 6) {
			activateTab(t, '/pages/pm/drtServices.php', 'New D. R. G', '/pm/accounts/boxy.add_drt.php');
		} else if (t === 7) {
			activateTab(t, '/pages/pm/currencies.php', 'New Currency', '/pm/accounts/currency.add.php');
		} else {
		}
	}

	//-->
</script>
<div>
	<div id="tabbedPane" style="margin-bottom: -6px;" class="mini-tab">
		<a href="javascript:void(0)" class="tab on" id="tab-1" onClick="showTabs(1)"><span></span> Insurance Providers</a>
		<a href="javascript:void(0)" class="tab" id="tab-2" onClick="showTabs(2)"><span></span> Insurance Programs/Schemes</a>
		<a href="javascript:void(0)" class="tab" id="tab-3" onClick="showTabs(3)"><span></span> Billable Items</a>
		<a href="javascript:void(0)" class="tab" id="tab-4" onClick="showTabs(4)"><span></span> Payment Methods</a>
		<a href="javascript:void(0)" class="tab" id="tab-5" onClick="showTabs(5)"><span></span> Cost Centres</a>
		<a href="javascript:void(0)" class="tab" id="tab-6" onClick="showTabs(6)"><span></span> Configure D.R.G</a>
		<a href="javascript:void(0)" class="tab" id="tab-7" onClick="showTabs(7)"><span></span> Currencies</a>
	</div>
	<div id="contentPane__">
		<div><span id="newLink"></span></div>
		<span id="contentPane"></span>
	</div>
	<br clear="left"/>
</div>