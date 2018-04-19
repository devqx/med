<?php
if(!isset($_SESSION)){session_start();}

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->accounts) && !$this_user->hasRole($protect->nurse)) {
	exit($protect->ACCESS_DENIED);
}
$mode = 'insurance';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
sessionExpired();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
$bills = new Bills();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
$staff = new StaffManager();

$sources = (new BillSourceDAO())->getBillSources();



?>
<script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
<link href="/style/def.css" rel="stylesheet" type="text/css"/>
<link href="/style/google-font.css" rel="stylesheet" type="text/css"/>

<style media="print">
	.table-condensed > thead > tr > th, .table-condensed > tbody > tr > th, .table-condensed > tfoot > tr > th, .table-condensed > thead > tr > td, .table-condensed > tbody > tr > td, .table-condensed > tfoot > tr > td {
		padding: 2px !important;
	}

	.table {
		color: #f00 !important;
	}
</style>

<script type="text/javascript">
	var provider_id = '<?= $_GET['provider_id']; ?>';
	var schemeid = '<?= $_GET['schemeid']; ?>';
	if (provider_id === "") {
		<?php $previousBalance__ = number_format(-$bills->_getPatientPaymentsTotals($_GET['sid'], $mode), 2, '.', '');?>
		<?php $debits = number_format(-$bills->_getPatientPaymentsTotals($_GET['sid'], $mode), 2, '.', '');?>
		<?php $credits = number_format($bills->_getPatientCreditTotals($_GET['sid'], $mode), 2, '.', '');?>
		var payments = <?=$bills->_getPatientPaymentsTotals($_GET['sid'], $mode);?>;
		//var sid = '<?//= $_GET['sid'];?>//';
		var credits = <?=$credits ?>;
		var prevBal = <?= $debits ?>;
	}
	var isContinued = false;//if coming to the payment page from the invoice page
 
	function showLoader(x) {
		$(x).parent().find("span.loader").html('<img src="/img/loading.gif">');
	}

	function hideLoader(x) {
		$(x).parent().find("span.loader").html('');
	}

	function errr(x, msg) {
		var str = msg.split(":");
		$(x).parent().find("span.loader").html(str[1]).css({"color": "red", "margin-right": "5px"});
	}

	function ok(x) {
		$(x).parent().find("span.loader").html('Saved!').css({"color": "#09c", "margin-right": "5px"});
	}

	function updateBill() {
		var x = credits - $("#discount_val").val();
		if (x >= 0) {
			$("#totalMinusDiscount").html(parseFloat(x).toLocaleString());
			$("#cbal").html(parseFloat(parseFloat($("#totalMinusDiscount").html().replace(/,/g, '')) - parseFloat(prevBal)).toLocaleString());
			if ($("#paid_value").val() === "") {
				$("#paid_value").attr("defaultValue", 0);
				$("#paid_value").val(0);
			}
			$("#presbal").html(parseFloat(parseFloat($("#cbal").html().replace(/,/g, '')) - parseFloat($("#paid_value").val())).toLocaleString());
		} else {
			$("#discount_val").attr("defaultValue", 0);
			$("#discount_val").val(0);
		}
	}

	function save(button, TYPE, amount) {
		var ref = $('input[name="payment_reference"]');
		var auth_code = $('input[name="voucher_auth_code"]');

		if (amount && amount.val() <= 0 && TYPE !== "discount" && TYPE !== "refund") {
			Boxy.alert("Invalid amount", function () {
				$("#paid_value").focus();
			});
		} else if (TYPE === "payment" && ref.val().trim() === "") {
			Boxy.alert("Reference Required?", function () {
				ref.focus();
			});
		} else if (TYPE === "voucher" && auth_code.val().trim() === "") {
			Boxy.alert("Voucher Code Required?", function () {
				auth_code.focus();
			});
		} else {
			
			if (TYPE === "payment" && ref.val().trim() !== "" && confirm("Are you sure you want to make a payment of \u20a6" + amount.val() + "?")) {
				$.ajax({
					url: '/billing/insurance/modifybill.php',
					type: 'POST',
					data: 'type=' + TYPE + '&sid=' + schemeid + '&amount=' + amount.val() + '&method=' + $("#pay_method").val() + '&payment_reference=' + ref.val(),
					beforeSend: function () {
						showLoader(button);
					},
					complete: function (a, b, c) {
						var ret = a.responseText.split(":");//;

						if (b === "success" && ret[0].trim() === "success") {
							$("#pay_method,#paid_value").attr("disabled", "disabled");
							hideLoader(button);
							if (isContinued) {
								Print("inv+pay", ret[1]);
							} else {
								Print("pay", ret[1]);
							}
							isContinued = false;
							button.attr("disabled", "disabled");
							ok(button);
							updateBill();
						} else {
							errr(button, a.responseText);
						}
					}
				});
			} else if (TYPE === "discount") {
				$.ajax({
					url: '/billing/insurance/modifybill.php',
					type: 'POST',
					data: 'type=' + TYPE + '&sid=' + schemeid + '&amount=' + amount.val() + '&method=' + $("#pay_method").val() + '&payment_reference=' + ref.val(),
					beforeSend: function () {
						showLoader(button);
					},
					complete: function (a, b, c) {
						if (b === "success" && a.responseText.trim() === "success") {
							$("#discount_val").attr("defaultValue", amount.val());
							$("#discount_val").attr("disabled", "disabled");
							button.attr("disabled", "disabled");
							ok(button);
							updateBill();
							setTimeout(function () {
								$('#billDoc').load('/billing/insurance/insuranceaccount.php?id=<?=$_GET['sid']?>&action=bill_summary');
							}, 1000);
						} else {
							errr(button, a.responseText);
						}
					}
				});
			} else if (TYPE === "voucher") {
				$.ajax({
					url: '/billing/insurance/modifybill.php',
					type: 'POST',
					data: 'type=' + TYPE + '&sid=' + schemeid + '&method=' + $("#voucher_method_id").val() + '&voucher_code=' + auth_code.val(),
					beforeSend: function () {
						showLoader(button);
					},
					complete: function (a, b, c) {
						if (b === "success" && a.responseText.trim().indexOf("success") !== -1) {
							button.attr("disabled", "disabled");
							Boxy.info(a.responseText.split(":")[2]);
							ok(button);
							updateBill();
							Print("voucher", a.responseText.split(":")[1]);
							setTimeout(function () {
								$('#billDoc').load('/billing/insurance/insuranceaccount.php?id=<?=$_GET['sid']?>&action=bill_summary');
							}, 1000);
						} else {
							errr(button, a.responseText);
						}
					}
				});
			}
		}
	}

	function convertInputs(st) {
		if (st) {
			$("input[type='number']").each(function () {
				$(this).replaceWith('<span id="' + $(this).attr('id') + '" data-type="number" name="' + $(this).attr('name') + '" value="' + $(this).val() + '" min="' + $(this).attr('min') + '" max="' + $(this).attr('max') + '">' + $(this).val() + '</span>');
			});

			$('#pay_method').each(function () {
				var OPTs = [], VALUEs = [];
				$('#pay_method option').each(function () {
					VALUEs.push($(this).val());
					OPTs.push($(this).text());
				});
				var OPTIONS = OPTs.join("|");
				var VALUES = VALUEs.join("|");

				$(this).replaceWith('<span data-type="select" data-options="' + VALUES + '" data-values="' + OPTIONS + '" id="' + $(this).attr('id') + '" name="' + $(this).attr('name') + '">' + $('#pay_method option:selected').text() + '</span>');
			});
			$("input[name='payment_reference']").each(function () {
				$(this).replaceWith('<span name="' + $(this).attr('name') + '">' + $(this).val() + '</span>');
			})
		} else {
			$("span[data-type='number']").each(function () {
				$(this).replaceWith('<input type="number" id="' + $(this).attr('id') + '" value="' + $(this).html() + '" name="' + $(this).attr('name') + '" min="' + $(this).attr('min') + '" max="' + $(this).attr('max') + '">');
			});
			$('span[data-type="select"]').each(function () {
				var OPTs = $(this).attr('data-options').split("|");
				var VALUEs = $(this).attr('data-values').split("|");
				var selected = $(this).text();
				var OPTIONS = '';
				for (var i = 0; i < OPTs.length; i++) {
					OPTIONS += '<option value="' + OPTs[i] + '" ' + (selected === VALUEs[i] ? ' selected' : '') + '>' + VALUEs[i] + '</option>';
				}
				$(this).replaceWith('<select disabled="disabled" name="' + $(this).attr('name') + '" id="' + $(this).attr('id') + '">' + OPTIONS + '</select>');
			});
			$('span[name="payment_reference"]').each(function () {
				$(this).replaceWith('<input type="text" name="' + $(this).attr('name') + '" value="' + $(this).text() + '" disabled="disabled"> ');
			});
		}
	}

	var rows;

	function Print(what, billId, reprint) {
		$("button").hide();
		$(".blockElem.demograph").show();
		$("span.iTabs").parent().hide();
		convertInputs(true);
		switch (what) {
			case "voucher":
			case "ireceipt":
			case "receipt":
			case "pay":
				Boxy.load('/billing/boxy.select_printer.php?type=' + what + '&bid=' + billId + '&mode=<?=$mode?>&reprint=' + reprint, {title: 'Select Printer'});
				break;
				setTimeout(function () {
					$('#billDoc').load('/billing/insurance/insuranceaccount.php?id=<?=$_GET['sid']?>&action=bill_summary');
				}, 1000);
				break;
			case "inv+pay":
				var params_ = "", bills_to_invoice_ = [];
				$.each($('input[name="bills[]"]:checked'), function (i, obj) {
					bills_to_invoice_.push($(obj).val());
				});
				if (bills_to_invoice_.length > 0) {
					params_ = "&bills=" + bills_to_invoice_.join(",");
				}
				$.get('/billing/generate_invoice.php?type=invpay&mode=insurance&sid=<?=$_GET['sid']?>' + params_, function (vid) {
					var id = vid.split(":");
					if (id[0] === 'ok') {
						Print('invpay', billId + ',' + id[1]);
					}
					else {
						
						Boxy.alert(id[1]);
					}
				});
				setTimeout(function () {
					$('#billDoc').load('/billing/insurance/insuranceaccount.php?id=<?=$_GET['sid']?>&action=bill_summary');
				}, 1000);
				break;
			case "stmnt":
				var address = '/billing/statement.php?mode=insurance&sid=<?=$_GET['schemeid']?>&provider_id=<?= $_GET['provider_id'] ?>&date_from=' + $('#date_from').val() + '&date_to=' + $('#date_to').val() + '&type=' + $('#tType').val() + '&patient_id=' + $('[name="patient_id_"]').val() + '&bill_source_ids=' + $('#bill_source_ids').val() + '&claimed_state=' +$('#claimedBillsState').val();
				window.open(address, "", "scrollbars=1,width=1600,height=700");
				setTimeout(function () {
					$('#billDoc').load('/billing/insurance/insuranceaccount.php?id=<?=$_GET['sid']?>&action=bill_summary');
				}, 1000);
				break;
			default:
				break;
		}

	}

	$(document).ready(function (e) {
		$('select[name="bill_source_ids"]').select2();
		$('select[name="claimedBillsState"]').select2({width:'100%',allowClear:true, placeholder: '--Filter bills by claimed status'});
  
		$('select[id="tType"]').select2({width: '100%'});
		setTimeout(function () {
			$.ajax({
				url: '/billing/ajax.get_payment_methods.php',
				dataType: 'json',
				beforeSend: function () {
				},
				success: function (s) {
					var html1 = '', html2 = '';
					for (var i = 0; i < s.length; i++) {
						if (jQuery.inArray(s[i].type, ["refund", "discount", "voucher"]) === -1) {
							html1 += '<option value="' + s[i].id + '">' + s[i].name + '</option>';
						} else {
							html2 += '<option value="' + s[i].id + '">' + s[i].name + '</option>';
						}
					}
					$('#pay_method').html(html1);
					$('#voucher_method_id').html(html2);
					$('#pay_method, #voucher_method_id').select2({width: '100%'});
				},
				error: function () {
					$.growlUI('<img src="/img/warning.png" >Failed to list payment methods');
				}
			});
		}, 10);
		//$("#discount_").attr('defaultValue',0);
		$("#presbal").html(parseFloat($("#cbal").html()).toFixed(2));
		$("#button").click(function (e) {
			//save discount
			$.Event($("#discount_val").change());
			<?php if($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->accounts)){?>
			save($("#button"), "discount", $("#discount_val"));
			<?php }else{?>
			Boxy.alert("You do not have the privilege to give discounts");
			<?php }?>
		});
		$("#voucher_button").click(function (e) {
			$.Event($("#refund_val").change());
			<?php if($this_user->hasRole($protect->voucher) || $this_user->hasRole($protect->cashier)){?>
			save($("#voucher_button"), "voucher", null);
			<?php }else{?>
			Boxy.alert("You do not have the privilege to give refunds");
			<?php }?>
		});

		$("#button2").click(function (e) {
			//save payment
			save($("#button2"), "payment", $("#paid_value"));
			e.preventDefault();
		});
		$("#paid_value").change(function (e) {
			$("#presbal").html(parseFloat(Math.abs(prevBal) - parseFloat($(this).val())).toFixed(2));
		});

		$(".iTabs a").click(function (e) {
			$(".iTabs a").removeClass("actif");
			var parts = decodeURI(e.target).split('#');
			showDoc(parts[1]);
			$(".iTabs a[href='#" + parts[1] + "']").addClass("actif");
			e.preventDefault();
		});
		$("#discount_val").change(function (e) {
			$(this).attr("defaultValue", $(this).val());
			updateBill();
		});
		$("#pay_method").change(function () {
			for (var io = 0; io < this.options.length; io++) {
				this.options[io].removeAttribute("selected");
			}
			this.options[this.selectedIndex].setAttribute("selected", "selected");
		});

    $("#resetFilter").click(function(e){e.preventDefault();$("#filterForm")[0].reset();$(".filters button:first").click();$("#bill_source_ids").select2("val", "");$("#tType").select2("val", "");$("#claimedBillsState").select2("val", "")});
		
		showDoc("statement");
		$(".filters button").click(function (e) {
			e.preventDefault();
			$.ajax({
				url: '/billing/insurance/filter_statement.php',
				data: {
					sid:<?= $_GET['sid']?>,
					from: $("#date_from").val(),
					to: $("#date_to").val(),
					tType: $("#tType").val(),
					claimed_state: $("#claimedBillsState").val(),
					bill_source_ids: $('#bill_source_ids').val(),
					patient_id: $('[name="patient_id_"]').val(),
					provider_id: provider_id,
					schemeid: schemeid,
					PageSize: $('#PageSize').val()
				},
				//data: 'sid=<?= $_GET['id']?>&from=' + $("#date_from").val() + '&to=' + $("#date_to").val() + '&tType=' + $("#tType").val(),
				type: 'POST',
				beforeSend: function () {
					$(".blockElem.statements").html('<div class="ball"><div>').show();
				},
				complete: function (a, b) {
					if (b === "success") {
						t = $(".blockElem.statements");
						t.html(a.responseText);

						var rows_ = $(a.responseText).find('td.amount');
						var ROWS = [];
						$.each(rows_, function (i) {
							ROWS.push(rows_[i]);
						});
						var CREDITS = 0, DEBITS = 0;
						$.each(ROWS, function (index) {
							if ($(ROWS[index]).prev().html().toLowerCase().indexOf("credit") !== -1) {
								CREDITS += parseFloat($(ROWS[index]).html().replace(/,/g, ''));
							} else {
								DEBITS += parseFloat($(ROWS[index]).html().replace(/,/g, ''));
							}
						});
						$("#totalMinusDiscount").html(parseFloat(CREDITS).toLocaleString());
						var pre = DEBITS < 0 ? '<span style="color:#b70000">[CR]</span> ' : '<span style="color:green">[DR]</span> ';
						$("#prevbal").html(pre + parseFloat(DEBITS).toLocaleString());
					}
					else {
						$(".blockElem.statements table").html('<tr><td><span class="error">Document failed to load. Please check your network</span></td><td>&nbsp;</td></tr>');
					}
				}
			});
		});

		
		$(".Filters2 button").click(function (e) {
			e.preventDefault();
			$.ajax({
				url: '/billing/insurance/filter_unclaimed_bills.php',
				data: {
					from: $("#date_from2").val(),
					to: $("#date_to2").val(),
					//tType: $("#tType").val(),
					//bill_source_ids: $('#bill_source_ids').val(),
					patient_id: $('#patient_id2').val(),
					provider_id: provider_id,
					schemeId: schemeid,
					pageSize: $('#pageSize2').val()
				},
				type: 'POST',
				beforeSend: function () {
					$(".blockElem.unclaimedBills").html('<div class="ball"><div>').show();
				},
				complete: function (a, b) {
					if (b === "success") {
						t = $(".blockElem.unclaimedBills");
						t.html(a.responseText);
						
					}
					else {
						$(".blockElem.unclaimedBills table").html('<tr><td><span class="error">Document failed to load. Please check your network</span></td><td>&nbsp;</td></tr>');
					}
				}
			});
		});
		$(".Filters3 button").click(function (e) {
			e.preventDefault();
			$.ajax({
				url: '/billing/insurance/claim_reports.php',
				data: {
					from: $("#date_from3").val(),
					to: $("#date_to3").val(),
					patient_id: $('#patient_id3').val(),
					provider_id: provider_id,
					schemeId: schemeid,
					pageSize: $('#pageSize3').val()
				},
				type: 'POST',
				beforeSend: function () {
					$(".blockElem.claimsReport").html('<div class="ball"><div>').show();
				},
				complete: function (a, b) {
					if (b === "success") {
						t = $(".blockElem.claimsReport");
						t.html(a.responseText);

					}
					else {
						$(".blockElem.claimsReport table").html('<tr><td><span class="error">Document failed to load. Please check your network</span></td><td>&nbsp;</td></tr>');
					}
				}
			});
		});

		
		$("#paid_value").change(function () {
			$(this).attr("defaultValue", $(this).val());
			updateBill();
		});
		var now = new Date().toISOString().split('T')[0];
		$(function () {
			$('#date_from').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now
					});
				},
				onChangeDateTime: function () {
					$("#date_to").val("")
				}
			});
			
			$('#date_to').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now,
						minDate: $("#date_from").val() ? $("#date_from").val() : false
					});
				}
			});

			$('#date_from2').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now
					});
				},
				onChangeDateTime: function () {
					$("#date_to2").val("")
				}
			});

			$('#date_to2').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now,
						minDate: $("#date_from2").val() ? $("#date_from2").val() : false
					});
				}
			});
			
			$('#date_from3').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now
					});
				},
				onChangeDateTime: function () {
					$("#date_to3").val("")
				}
			});

			$('#date_to3').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({
						maxDate: now,
						minDate: $("#date_from3").val() ? $("#date_from3").val() : false
					});
				}
			});
			
		});
		

	});

	function showDoc(xg) {
		$(".blockElem.demograph").hide();
		if (xg === "statement") {
			$(".blockElem.claimsReport").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").show();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").show();
			$(".blockElem .filters").show();
			$(".blockElem.unclaimedencountershmo").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.claims").hide();
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem .Filters2").hide();
			$(".blockElem .Filters3").hide();


		}
		else if (xg === "payment") {
			if (!provider_id) {
				$(".blockElem.claimsReport").hide();
				$(".blockElem.discount").hide();
				$(".blockElem.total").hide();
				$(".blockElem.payment").show();
				$(".blockElem.balance").show();
				$(".blockElem.voucher").show();
				$(".blockElem.unclaimedencountershmo").hide();
				$(".blockElem .filters").hide();
				$(".blockElem.statements").hide();
				$(".blockElem.claims").hide();
				$(".blockElem .Filters2").hide();
				$(".blockElem.unclaimedBills").hide();
				$(".blockElem .Filters3").hide();

			}
		}
 else if (xg === "claimReport") {
			$(".blockElem.claimReport").show();
			$(".blockElem.discount").hide();
			$(".blockElem.total").hide();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").hide();
			$(".blockElem .filters").hide();
			$(".blockElem .Filters2").hide();
			$(".blockElem .Filters3").show();
			$(".blockElem.statements").hide();
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem.unclaimedencountershmo").hide();
			$(".blockElem.claims").hide();
			
		} else if (xg === "claims") {
			$(".blockElem.claimsReport").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").hide();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").hide();
			$(".blockElem .filters").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem.unclaimedencountershmo").hide();
			$(".blockElem .Filters2").hide();
			$(".blockElem .Filters3").hide();
			$(".blockElem.claims").show();
			$.get('/billing/claims.php?sid=<?= $_GET['schemeid']?>&provider_id=<?= $_GET['provider_id']?>&mode=insurance', function (data) {
				$('.blockElem.claims').html(data);
			});
		} else if (xg === "unclaimedencountershmo") {
			$(".blockElem.claimsReport").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").hide();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").hide();
			$(".blockElem .filters").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.claims").hide();
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem .Filters2").hide();
			$(".blockElem .Filters3").hide();
			$(".blockElem.unclaimedencountershmo").show();
			$.get('/billing/unclaimed_encounters_hmo.php?sid=<?= $_GET['schemeid']?>&provider_id=<?= $_GET['provider_id']?>&mode=insurance', function (data) {
				$('.blockElem.unclaimedencountershmo').html(data);
			});
		}
		else if (xg === "unclaimedBills") {
			$(".blockElem.claimsReport").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").hide();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").hide();
			$(".blockElem .filters").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.claims").hide();
			$(".blockElem.Filters2").hide();
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem .Filters2").show();
			
		}
		
		
	}
</script>

<link href="/style/patient.bill.css" rel="stylesheet">
<div class="blockElem">
    <span class="iTabs">
        <?php if(!$_GET['provider_id']){ ?>  <a href="#payment">Payments/Vouchers</a><?php } ?>
        <a href="#statement" class="actif">Statements</a>
        <a href="#claims">Claims</a>
        <a href="#unclaimedBills">Unclaimed Bills</a>
        <a href="#unclaimedencountershmo">Unclaimed Encounters</a>
	      <a href="#claimReport">Claims Report</a>
    </span>

	<!--<div class="pull-right">-->
	<!--	<button class="btn small" onclick="refreshDoc()" title="Refresh Document"><i class="icon-refresh"></i></button>-->
	<!--</div>-->
	<form id="filterForm">
		<div class="filters">
			<div class="row-fluid">
				<label class="span3">Filter by Patient<input type="text" name="patient_id_" id="patient_id"></label>
				<div class="span3">
					Bill Source:
					<label><select name="bill_source_ids" id="bill_source_ids" multiple="multiple" class="wide">
							<?php foreach ($sources as $source) { ?>
								<option value="<?= $source->getId() ?>"><?= ucwords(str_replace('_', ' ', $source->getName())) ?></option><?php } ?>
						</select></label>
				</div>
				<div class="span3">
					Filter Bill By claimed State :
					<label><select name="claimedBillsState" id="claimedBillsState" >
							<option value=""></option>
							<option value="unclaimed">Unclaimed Bills</option>
						</select>
					</label>
				</div>
				<div class="span3">
					<button class="btn small" id="exportStatement">Export</button>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					Transaction Date:
					<div class="input-prepend">
						<span class="add-on">From</span>
						<input class="span5" type="text" name="date_from" id="date_from" placeholder="Start Date">
						<span class="add-on">To</span>
						<input class="span5" type="text" name="date_to" id="date_to" placeholder="Stop Date">
					</div>
				</div>
				<div class="span4" style="/*margin:0 -10px 0 30px*/">
					<label>Transaction Type:<select name="tType" id="tType" data-placeholder="Select" multiple="multiple">
							<option></option>
							<option value="credit">CHARGE</option>
							<option value="debit">PAYMENT</option>
							<option value="discount">DISCOUNT</option>
							<option value="reversal">REVERSAL</option>
							<option value="refund">REFUND</option>
							<option value="write-off">WRITE-OFF</option>
							<option value="transfer-credit">CREDIT-TRANSFER</option>
							<option value="transfer-debit">DEBIT-TRANSFER</option>
						</select></label>
				</div>
				<div class="span2">
					Page Size:
					<label>
						<select name="PageSize" id="PageSize">
							<option value="20">10-20</option>
							<option value="40">20-40</option>
							<option value="60">40-60</option>
							<option value="80">60-80</option>
							<option value="100">60-100</option>
							<option value="5000">Infinite</option>
						</select>
					</label>
				</div>

				<div class="span1">
					<button type="button" class="btn wide" style="margin-top:24px">Apply</button>
				</div>
				<div class="span1">
					<button type="button" class="btn-link wide" id="resetFilter" style="margin-top: 24px">Reset</button>
				</div>

			</div>
		</div>
	</form>

	<form id="FilterForm2">
		<div class="Filters2">
			<div class="row-fluid">
				<label class="span3">
					Filter by Patient<input type="text" name="patient_id" id="patient_id2">
				</label>
				<label class="span4">
					Transaction Date:
					<div class="input-prepend">
						<span class="add-on">From</span>
						<input class="span5" type="text" name="date_from" id="date_from2" placeholder="Start Date">
						<span class="add-on">To</span>
						<input class="span5" type="text" name="date_to" id="date_to2" placeholder="Stop Date">
					</div>
				</label>
					<label class="span3">
						Page size
						 <label>
							<select name="PageSize" id="pageSize2">
								<option value="20">10-20</option>
								<option value="40">20-40</option>
								<option value="60">40-60</option>
								<option value="80">60-80</option>
								<option value="100">60-100</option>
								<option value="5000">Infinite</option>
							</select>
						 </label>
					</label>
				<label class="span1">
					<button type="button" class="btn wide" style="margin-top:24px">Apply</button>
				</label>
				<label class="span1">
					<button class="btn wide" id="exportStatement2">Export</button>
				</label>
			</div>
		</div>
	</form>
	
	<form id="FilterForm3">
		<div class="Filters3">
			<div class="row-fluid">
				<div class="span5">
					Transaction Date:
					<div class="input-prepend">
						<span class="add-on">From</span>
						<input class="span5" type="text" name="date_from" id="date_from3" placeholder="Start Date">
						<span class="add-on">To</span>
						<input class="span5" type="text" name="date_to" id="date_to3" placeholder="Stop Date">
					</div>
				</div>
					<div class="span3">
						Page Size:
						<label>
							<select name="pageSize" id="pageSize3">
								<option value="10">10-20</option>
								<option value="40">20-40</option>
								<option value="60">40-60</option>
								<option value="80">60-80</option>
								<option value="100">60-100</option>
								<option value="5000">Infinite</option>
							</select>
						</label>
					</div>
				<div class="span2">
					<button type="button" class="btn wide" style="margin-top:24px">Apply</button>
				</div>
				<label class="span2">
					<button class="btn " id="exportStatement3">Export</button>
				</label>
			</div>
		</div>
	</form>

	
</div>



<!--/if action == invoice-->

<div class="blockElem statements">Statements<br/>
	<div class="ball"></div>
</div>
<div class="blockElem claims">Claims<br/>
	<div class="ball"></div>
</div>
<div class="blockElem unclaimedBills">Unclaimed Bills<br/>
	<div class="ball"></div>
</div>
<div class="blockElem unclaimedencountershmo">Unclaimed Encounters<br/>
	<div class="ball"></div>
</div>
<div class="blockElem claimsReport">Claims Report<br/>
	<div class="ball"></div>
</div>

<?php if ($this_user->hasRole($protect->cashier)) { ?>
	<div class="blockElem voucher">Vouchers: <br/>
		<table class="data wide" border="0">
			<tr>
				<td valign="top">
					<div class="row-fluid">
						<label class="span6">Method<select id="voucher_method_id" name="voucher_method_id"></select></label>
						<label class="span4">Code
							<input type="text" name="voucher_auth_code" onkeyup="this.value=this.value.toUpperCase()"> </label>
						<div class="span2">
							<label>&nbsp;<span class="loader" id="voucher_loader"></span><br>
								<button type="button" name="button_voucher" id="voucher_button" class="btn pull-right wide">Apply
								</button>
							</label>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<div class="blockElem discount">Discounts:<br/>
		<table class="data table">
			<tr>
				<td valign="top">Total b/d</td>
				<td valign="top" class="amount"><?= number_format($outstanding_total, 2); ?></td>
			</tr>
		</table>
	</div>
<?php } ?>
<div class="blockElem total">Totals
	<table width="100%" border="0" class="data">
		<tr>
			<td valign="top"><h5>Total Charges <em class="fadedText">[Includes Refunds]</em></h5></td>
			<td valign="top" id="totalMinusDiscount" class="amount"><?= number_format($credits, 2) ?></td>
		</tr>
		<tr>
			<td valign="top"><h5>Total Payments <em class="fadedText">[Includes Discounts, Reversals]</em></h5></td>
			<td valign="top" id="prevbal" class="amount"><?= (($debits < 0) ? '<span style="font-weight:bold;color:red">[CR]</span> ' : '<span style="font-weight:bold;color:green">[DR]</span> ') . number_format(abs($debits)) ?></td>
		</tr>
		<tr>
			<td valign="top"><strong>Current Balance</strong></td>
			<td valign="top" id="cbal" class="amount"><?= number_format(($credits - $debits), 2, '.', ''); ?>
				<!--          --><?php //echo number_format(($outstanding_total + $previousBalance),2, '.','');?><!--</td>-->
		</tr>
	</table>
</div>
<!--if action == payment or invoice-->
<?php if ($this_user->hasRole($protect->cashier)) { ?>
	<div class="blockElem payment">
		<div class="row-fluid">
			<label class="span6">Payment Method:<select name="pay_method" id="pay_method" class="wide">
				</select></label>
			<label class="span2">Payment Reference:
				<input type="text" name="payment_reference"></label>
			<label class="span2">Amount Paying: (<?= $currency ?>)
				<input type="number" min="0" name="paid_value" id="paid_value" value="0"/></label>
			<span class="span2"><label> <span class="loader" id="amount_loader"></span></label>
            <button type="button" id="button2" class="btn pull-right wide">Save &amp; Print</button>
        </span>
		</div>
	</div>
	<!--/if action == payment or invoice-->
<?php } ?>
<div class="blockElem balance">
	<table width="100%" border="0">
		<tr>
			<td valign="top">Present Balance (Outstanding)</td>
			<td valign="top" id="presbal" class="amount"></td>
		</tr>
	</table>
</div>
<script>
	$('select[name="PageSize"]').select2();


	var setPatient = function () {
		$('[name="patient_id"]').select2({
			placeholder: "Filter List by Patient EMR or Name",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					}
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		}).change(function (e) {
			if (!e.handled) {
				e.handled = true;
				goTo(0);
			}
		});


		// for statement
		$('[name="patient_id_"]').select2({
			placeholder: "Filter List by Patient EMR or Name",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					}
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		});

	};
	var setDates = function () {
		jQuery('#date_start').datetimepicker({
			format: 'Y/m/d',
			onShow: function (ct) {
				this.setOptions({
					maxDate: jQuery('#date_end').val() ? jQuery('#date_end').val() : false
				})
			},
			timepicker: false
		});
		jQuery('#date_end').datetimepicker({
			format: 'Y/m/d',
			onShow: function (ct) {
				this.setOptions({
					minDate: jQuery('#date_start').val() ? jQuery('#date_start').val() : false
				})
			},
			timepicker: false
		});
	};
	var goTo = function (page) {
		var data = {};
		var id = $('select[name="iSchemes"]').val();
		var pid = $('[name="patient_id"]').select2("val");
		var dates = {start: $('#date_start').val(), end: $('#date_end').val()};
		var pageSize = $('select[name="pageSize"]').val();
		var provider_id = $('select[name="SchemeOwner"]').val();
		data.page = page;
		data.pageSize = pageSize;
		data.id = id;
		data.provider = provider_id;
		if (pid !== "") {
			data.pid = pid;
		}
		if (dates.start !== "" && dates.end !== "") {
			data.start = dates.start;
			data.end = dates.end;
		}
		$.get("/billing/insurance/insuranceaccount.php", data, function (s) {
			$("#area11").html($(s).filter("#area11").html());
			setPatient();
			setDates();
		});
	};
	$(document).on('click', '.list11.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				goTo(page);
			}
			e.clicked = true;
		}
	}).on('click', '#date_filter', function (e) {
		if (!e.handled) {
			goTo(0);
			e.handled = true;
		}
	}).on('change', 'select[name="pageSize"]', function (e) {
		if (!e.handled) {
			goTo(0);
			e.handled = true;
		}
	}).on('change', '#invoiceAll_Sel', function (e) {
		if (!e.handled) {
			if ($(this).is(":checked")) {
				$('[name="bills[]"]:checkbox').prop('checked', true).iCheck('update');
			} else {
				$('[name="bills[]"]:checkbox').prop('checked', false).iCheck('update');
			}
			e.handled = true;
		}
	}).ready(function () {
		setPatient();
		setDates();
	});
	
	$('#exportStatement').on('click', function (e) {
		if (!e.handled) {
			var bill_source = $('#bill_source_ids').val();
			if(bill_source === null){
				bill_source = "";
			}
			window.open('/excel.php?dataSource=ins_statement&filename=Statement Report&sid=<?= $_GET['schemeid']?>&provider_id=<?= $_GET['provider_id'] ?>&date_from=' + $('#date_from').val() + '&date_to=' + $('#date_to').val() + '&type=' + $('#tType').val() + '&patient_id=' + $('[name="patient_id_"]').val() + '&bill_source_ids=' + bill_source + '&claimed_state=' +$('#claimedBillsState').val() + '_blank');
			e.handled = true;
			e.preventDefault();
		}
	});

	$('#exportStatement3').on('click', function (e) {
		if (!e.handled) {
			window.open('/excel.php?dataSource=claim&filename=Claim_Report&from='+ $("#date_from3").val() + '&to='+$("#date_to3").val()+'&insurance_scheme_id=<?=(isset($_GET['schemeid'])) ? $_GET['schemeid'] : ''?>&provider=<?=(isset($_GET['provider_id'])) ? $_GET['provider_id'] : ''?>', '_blank');
			e.handled = true;
			e.preventDefault();
		}

		
	});


</script>
