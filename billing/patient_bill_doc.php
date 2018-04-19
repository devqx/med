<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
if (!isset($_SESSION)) {
	session_start();
}
//used to track items selected in the insurance bills page
unset($_SESSION['checked_items_all']);
unset($_SESSION['checked_items']);
unset($_SESSION['checked_bill_all']);
unset($_SESSION['checked_bill']);

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = (isset($_REQUEST['PageSize'])) ? $_REQUEST['PageSize'] : 20;
if(!isset($_REQUEST['page'])){
	unset($_SESSION['invoice_items_all']);
	unset($_SESSION['invoice_items']);
}
if(!isset($_SESSION['invoice_items'])){$_SESSION['invoice_items'] = [];}
if(!isset($_SESSION['invoice_items_all'])){$_SESSION['invoice_items_all'] = [];}
//$page = ($page < 0)

$_SESSION['invoice_items'][$page-1] = !is_blank(@$_GET['lines']) ? array_filter(explode(',', @$_GET['lines'])) : (isset($_SESSION['invoice_items'][$page-1]) ? [] : []) ;
$_SESSION['invoice_items_all'] = isset($_SESSION['invoice_items']) && is_array(array_filter($_SESSION['invoice_items'])) ? array_flip(array_flip(array_flatten($_SESSION['invoice_items']))) : [];

$grouped = false;
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->accounts) && !$this_user->hasRole($protect->nurse)){
	exit($protect->ACCESS_DENIED);
}
$mode = 'patient';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
sessionExpired();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
$bills = new Bills();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
$staff = new StaffManager();

$sources = (new BillSourceDAO())->getBillSources();
?>
<!--suppress HtmlUnknownAnchorTarget -->
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
	<?php $previousBalance__ = number_format(-$bills->_getPatientPaymentsTotals($_GET['id']), 2, '.', '');?>
	<?php $debits = number_format(-$bills->_getPatientPaymentsTotals($_GET['id']), 2, '.', '');?>
	<?php $credits = number_format($bills->_getPatientCreditTotals($_GET['id']), 2, '.', '');?>
	//var payments = <?=$bills->_getPatientPaymentsTotals($_GET['id']);?>;
	var pid = '<?= $_GET['id'];?>';
	var credits = <?=$credits ?>;

	var prevBal = <?= $debits ?>;

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
			var pres = parseFloat(parseFloat($("#cbal").html().replace(/,/g, '')) - parseFloat($("#paid_value").val()));
			$("#presbal").html( $.number(pres) );
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
			if (TYPE === "payment" && ref.val().trim() !== "" && confirm("Are you sure you want to make a payment of <?= $currency->getSymbolLeft()?>" + amount.val() + "<?= $currency->getSymbolRight()?>?")) {
				var dueDate = '';
				if ($('input:text[name="due_date"]').val() !== '') {
					if (window.confirm('You have a specified a due date.\nWould you like to use this date as the effective payment date?')) {
						dueDate = '&due_date=' + $('input:text[name="due_date"]').val();
					}
				}
				$.ajax({
					url: '/billing/modifybill.php',
					type: 'POST',
					data: 'type=' + TYPE + '&pid=' + pid + '&amount=' + amount.val() + '&method=' + $("#pay_method").val() + '&payment_reference=' + ref.val() +''+ dueDate,
					beforeSend: function () {
						showLoader(button);
					},
					complete: function (a, b) {
						var ret = a.responseText.split(":");//;

						if (b === "success" && ret[0].trim() === "success") {
							$("#pay_method,#paid_value").prop("disabled", "disabled");
							hideLoader(button);
							if (isContinued) {
								Print("inv+pay", ret[1]);
							} else {
								Print("pay", ret[1]);
							}
							isContinued = false;
							setTimeout(function(){
								$(button).prop("disabled", "disabled");
							}, 1000);
							
							ok(button);
							updateBill();
						} else {
							errr(button, a.responseText);
						}
					}
				});
			} else if (TYPE === "discount") {
				$.ajax({
					url: '/billing/modifybill.php',
					type: 'POST',
					data: 'type=' + TYPE + '&pid=' + pid + '&amount=' + amount.val() + '&method=' + $("#pay_method").val() + '&payment_reference=' + ref.val(),
					beforeSend: function () {
						showLoader(button);
					},
					complete: function (a, b) {
						if (b === "success" && a.responseText.trim() === "success") {
							$("#discount_val").prop({"defaultValue":amount.val(),"disabled": "disabled"});
							setTimeout(function(){
								$(button).prop("disabled", "disabled");
							}, 1000);
							ok(button);
							updateBill();
							setTimeout(function () {
								<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
							}, 1000);
						} else {
							errr(button, a.responseText);
						}
					}
				});
			} else if (TYPE === "voucher") {
				$.ajax({
					url: '/billing/modifybill.php',
					type: 'POST',
					data: 'type=' + TYPE + '&pid=' + pid + '&method=' + $("#voucher_method_id").val() + '&voucher_code=' + auth_code.val(),
					beforeSend: function () {
						showLoader(button);
					},
					complete: function (a, b) {
						if (b === "success" && a.responseText.trim().indexOf("success") !== -1) {
							setTimeout(function(){
								$(button).prop("disabled", "disabled");
							}, 1000);
							Boxy.info(a.responseText.split(":")[2]);
							ok(button);
							updateBill();
							Print("voucher", a.responseText.split(":")[1]);
							setTimeout(function () {
								<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
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

	function Print(what, billId, reprint) {
		//$("button").hide();
		//$(".blockElem.demograph").show();
		//$("span.iTabs").parent().hide();
		//convertInputs(true);
		switch (what) {
			case "inv":
				var params = "", bills_to_invoice = [];
				$.each($('input[name="bills[]"]:checked'), function (i, obj) {
					bills_to_invoice.push($(obj).val());
				});
				if (bills_to_invoice.length > 0) {
					params = "&bills=" + bills_to_invoice.join(",");
				}
				var address;
			<?php if($grouped){?>
				address = '/billing/invoice_grouped.php?mode=patient&pid=<?=$_GET['id']?>' + params;
			<?php } else {?>
				address = '/billing/invoice.php?mode=patient&pid=<?=$_GET['id']?>' + params;
			<?php } ?>
				window.open(address, "", "scrollbars=1,width=1600,height=700");
				setTimeout(function () {
					//<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
					
				}, 400);
				break;
			case "voucher":
			case "receipt":
			case "ireceipt":
			case "pay":
				Boxy.load('/billing/boxy.select_printer.php?type=' + what + '&bid=' + billId + '&reprint=' + reprint, {
					title: 'Select Printer', afterHide: function () {
						//<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
						
					}
				});
				break;
			case "ireceipt2":
				Boxy.load('/billing/boxy.select_printer.php?type=' + what + '&bid=' + billId + '&reprint=' + reprint+'&grouped', {
					title: 'Select Printer', afterHide: function () {
						//<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
						
					}
				});
				break;
			case "invpay":
				Boxy.load('/billing/boxy.select_printer.php?type=' + what + '&bid=' + billId, {
					title: 'Select Printer', afterHide: function () {
						//<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
						
					}
				});
				break;
			case "inv+pay":
				var params_ = "", bills_to_invoice_ = [];
				$.each($('input[name="bills[]"]:checked'), function (i, obj) {
					bills_to_invoice_.push($(obj).val());
				});
				if (bills_to_invoice_.length > 0) {
					params_ = "&bills=" + bills_to_invoice_.join(",");
				}
				$.get('/billing/generate_invoice.php?type=invpay&mode=patient&pid=<?= $_GET['id'] ?>' + params_, function (vid) {
					var id = vid.split(":");
					if (id[0] === 'ok') {
						Print('invpay', billId + ',' + id[1]);
					} else {
						Boxy.alert(id[1]);
					}
				});
				setTimeout(function () {
					//<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
					
				}, 400);
				break;
			case "stmnt":
				address = '/billing/statement.php?mode=patient&sid=<?=$_GET['id']?>&date_from=' + $('#date_from').val() + '&date_to=' + $('#date_to').val() + '&type=' + $('#tType').val() + '&bill_source_ids=' + $('#bill_source_ids').val()+'&PageSize='+$('#PageSize').val();
				window.open(address, "", "scrollbars=1,width=1600,height=700");
				setTimeout(function () {
					<?= isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
					setTimeout(function () {
						$(".iTabs a[href='#statement']").click();
					}, 500);
				}, 400);
				break;
			default:
				break;
		}
	}
	$(document).ready(function () {
		setTimeout(function(){
			$(".iTabs a[href='#statement']").click();
			setTimeout(function(){
				$('#StatementFilterBtn').click();
			}, 100);
		}, 500);

		$('select[name="bill_source_ids[]"]').select2();
		
		$('select[id="tType"]').select2({width: '100%', allowClear: true});
		$('select[name="PageSize"]').select2();


		setTimeout(function () {
			$.ajax({
				url: '/billing/ajax.get_payment_methods.php',
				dataType: 'json',
				beforeSend: function () {
				},
				success: function (s) {
					var html1 = '', html2 = '';
					for (var i = 0; i < s.length; i++) {
						if(!_.includes(["refund", "discount", "voucher"], s[i].type)){
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
					$.growlUI('<img src="/img/warning.png"> Failed to list payment methods');
				}
			});
		}, 10);

		$("#presbal").html(parseFloat($("#cbal").html()).toFixed(2));
		$("#button").click(function (e) { //save discount
			if(!e.handled) {
				$.Event($("#discount_val").change());
			<?php if(($this_user->hasRole($protect->mgt) && $this_user->hasRole($protect->accounts)) || $this_user->hasRole($protect->cashier)){?>
			save($("#button"), "discount", $("#discount_val"));
			<?php }else{?>
			Boxy.alert("You do not have the privilege to give discounts");
			<?php }?>
		}
		});
		$("#voucher_button").click(function (e) {
			if(!e.handled) {
				//save discount
				$.Event($("#refund_val").change());
				<?php if($this_user->hasRole($protect->voucher) || $this_user->hasRole($protect->cashier)) {?>
				save($("#voucher_button"), "voucher", null);
				<?php } else {?>
				Boxy.alert("You do not have the privilege to give refunds");
				<?php } ?>
				e.handled = true;
			}
		});

		$("#button2").live('click',function (e) {
			if(!e.handled){
				//save payment
				save($("#button2"), "payment", $("#paid_value"));
				e.preventDefault();
				e.handled = true;
			}
		});

		$("#paid_value").live('change',function (e) {
			if(!e.handled){
				updateBill();
				e.handled = true;
			}
		});

		$(".iTabs a").click(function (e) {
			if(!e.handled){
			$(".iTabs a").removeClass("actif");
			var parts = decodeURI(e.target).split('#');
//			$("#discount_val, #paid_value").val("0").trigger("change");
			showDoc(parts[1]);
			$(".iTabs a[href='#" + parts[1] + "']").addClass("actif");
			e.preventDefault();
			e.handled = true;
			}
		});
		$("#discount_val").change(function (e) {
			if(!e.handled){
				$(this).attr("defaultValue", $(this).val());
				updateBill();
				e.handled = true;
			}
		});
		$("#pay_method").change(function (e) {
			if(!e.handled) {
				for (var io = 0; io < this.options.length; io++) {
					this.options[io].removeAttribute("selected");
				}
				this.options[this.selectedIndex].setAttribute("selected", "selected");
				e.handled = true;
			}
		});

		$("#resetFilter").click(function (e) {
			if (!e.handled) {
				e.preventDefault();
				$("#filterForm")[0].reset();
				$("#bill_source_ids").select2("val", "");
				$("#tType").select2("val", "");
				$("#PageSize").select2("val", "");
				$(".filters button.btn").get(0).click();
				e.handled = true;
			}
		});

		$("#printInvoiceBtn").live('click', function (e) {
			if (!e.handled) {
				isContinued = true;
				Boxy.ask("Do you want to make a payment now ?", ['Yes', 'Not Now', 'Cancel Print'],
					function (s) {
						if (s === 'Yes') {
							$(".iTabs a[href='#payment']").click();
						} else if (s === 'Not Now') {
							Print("inv");
						}
					}, {title: 'Invoice / Payment'});
				e.handled = true;
			}
		});
		showDoc("invoice");
		$(".filters button").click(function (e) {
			e.preventDefault();
			$.ajax({
				url: '/billing/filter_statement.php',
				data: {
					pid: '<?= $_GET['id']?>',
					date_from: $("#date_from").val(),
					date_to: $("#date_to").val(),
					tType: $("#tType").val(),
					bill_source_ids: $('#bill_source_ids').val(),
					PageSize: $('#PageSize').val()
				},
				type: 'POST',
				beforeSend: function () {
					$(".blockElem.statements").html('<div class="ball"></div>').show();
				},
				complete: function (a, b) {
					if (b === "success") {
						t = $(".blockElem.statements");
						t.html(a.responseText);
					} else {
						$(".blockElem.statements table").html('<tr><td><span class="error">Document failed to load. Please check your network</span></td><td>&nbsp;</td></tr>');
					}
				}
			});
		});

		var now = new Date().toISOString().split('T')[0];
		$(function () {
			$('#date_from').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function () {
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
				onShow: function () {
					this.setOptions({
						maxDate: now,
						minDate: $("#date_from").val() ? $("#date_from").val() : false
					});
				}
			});
			<?php if($this_user->hasRole($protect->creditLimit)){ ?>
			$('#creditLimit').on('click', function (e) {
				if (!e.handled) {
					Boxy.load("/billing/boxy.edit_credit_limit.php?pid=<?= $_GET['id']?>", {
						afterHide: function () {
							<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
						}
					});
					e.handled = true;
				}
			});<?php }?>
		});
		$('.addBill.action').bind('click', function () {
			Boxy.load('/billing/boxy.misc.charge.php?id=<?=$_GET['id']?><?= (isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '')?>', {
				title: 'Add Miscellaneous Bill', afterHide: function () {
					<?=isset($_GET['aid']) ? 'showTabs(13);' : 'showTabs(7);'?>
				}
			});
		});
		$('.creditTransfer.action').bind('click', function () {
			$(".iTabs a[href='#statement']").click();
			$("#notify2").notify({speed: 500, expires: false}).notify("create", {
				title: "Use the Apply button to find the items to transfer.",
				text: 'click to hide'
			}, {
				expires: false,
				custom: true,
				//icon:'alert.png',
				click: function (e, instance) {
					instance.close();
				}
			});
//			$("#notify2");
		});

		$('.cancelConsultation').live('click', function (e) {
			if (!e.handled) {
				Boxy.info("Sorry this function has been deprecated.<br>You can only cancel the encounter from its tab");
				e.handled = true;
			}
		});
	});
	function showDoc(xg) {
		if (xg === "statement") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.total").show();
			$(".blockElem.balance").show();
			$(".blockElem.filterblock").show();
		} else if (xg === "payment") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.payment").show();
			$(".blockElem.balance").show();
			$(".blockElem.voucher").show();
			$('#paid_value').number(true, 2);
			$('input:text[name="due_date"]').datetimepicker('destroy').datetimepicker();
		} else if (xg === "invoice") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.invoice").show();
			$(".blockElem.discount").show();
			$(".blockElem.total").show();
		} else if (xg === "invoices") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.invoices").show();
			$(".blockElem.balance").show();
			$.get('/billing/invoices.php?pid=<?= $_GET['id']?>', function (data) {
				$('.blockElem.invoices').html(data);
				$('.blockElem.invoices table.table').dataTable();
			});
		} else if (xg === "insuredBills") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.total").hide();
			$(".blockElem.balance").hide();
			$(".blockElem.insuranceBills").show();
			$.get('/billing/insurance_bills.php?pid=<?= $_GET['id']?>', function (data) {
				$('.blockElem.insuranceBills').html(data);
			});

		} else if (xg === "claims") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.total").show();
			$(".blockElem.balance").show();
			$(".blockElem.claims").show();
			$.get('/billing/claims.php?pid=<?= $_GET['id']?>', function (data) {
				$('.blockElem.claims').html(data);
			});
		} else if (xg === "promos") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.promos").show();
			$.get('/billing/patient_packages.php?pid=<?= $_GET['id']?>', function (data) {
				$('.blockElem.promos').html(data);
			});
		
		} else if (xg === "pa_codes") {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.pa_codes").show();
			$.post('/billing/pa_codes.php', {patient_id: '<?= $_GET['id']?>'}, function (data) {
				$('.blockElem.pa_codes').html($(data).find('#requestsList').html());
			});
		
		} else if (xg === "unreviewed") {
			$(".blockElem:not(.tabs)").hide();
			$('.blockElem.total').hide();
			$('.blockElem.balance').hide();
			$(".blockElem.unreviewed").show();
			$.post('/billing/unreviewed-transactions.php', {patient_id: '<?= $_GET['id']?>'}, function (data) {
				$('.blockElem.unreviewed').html(data)  //.html($(data).find('#requestsList').html());
			});
			
			
		} else if (xg === "estimatedBills"){
		    $('.blockElem:not(.tabs)').hide();
		    $('.blockElem.total').hide();
		    $('.blockElem.balance').hide();
		    //$('.blockElem.claims').hide();
		    $('.blockElem.estimatedBills').show();
		    $.get('/billing/patient_estimated_bills.php?pid=<?=$_GET['id']?>',function (data) {
			    $('.blockElem.estimatedBills').html(data);
            });

        }else {
			$(".blockElem:not(.tabs)").hide();
			$(".blockElem.code").show();
			$.post('/billing/unreviewed-transactions.php', {patient_id: '<?= $_GET['id']?>'}, function (data) {
				$('.blockElem.code').html($(data).find('#requestsList').html());
			});
		}

	}
</script>
<link href="/style/patient.bill.css?rand=<?= time()?>" rel="stylesheet">
<?php $patient = (new PatientDemographDAO())->getPatient($_GET['id'], FALSE); ?>

<div class="blockElem tabs menu-head">
	<span class="iTabs">
			<!--<a href="#invoice" class="actif">Invoice</a>-->
			<a href="#payment">Payments/Vouchers</a>
			<a href="#statement">Statement</a>
			<a href="#insuredBills">Insurance Bills</a>
			<!--<a href="#invoices">Invoices</a>-->
			<a href="#claims">Claims</a>
			<a href="#promos">Packages</a>
			<a href="#pa_codes">PA Codes</a>
		<a href="#estimatedBills">Quotations</a>
		<a href="#unreviewed">To Be Reviewed</a>
	</span>
	<?php if ($this_user->hasRole($protect->hmo_officer)) { ?><span class="pull-right"><button
			class="action creditTransfer">Credit Transfer</button></span><?php } ?>
	<?php if($this_user->hasRole($protect->bill_auditor)){?>
	<span class="pull-right"><button class="action addBill">Add Misc. Transaction</button></span>
	<?php }?>
	<span class="pull-right">Credit Limit: <a id="creditLimit" href="javascript:">
			<span class="price-input"><?= (new CreditLimitDAO())->getPatientLimit($_GET['id'])->getAmount() ?></span>
			Validity: <?= date("d/m/Y", strtotime((new CreditLimitDAO())->getPatientLimit($_GET['id'])->getExpiration())) ?></a></span>

	<?php if (strtolower($patient->getScheme()->getType()) != 'self') { ?>
		<div class="notify-bar" style="margin-top: 5px"><?= $patient->getFullname() ?> is insured
		by <?= $patient->getScheme()->getName() ?></div><?php } ?>
</div>
<div class="blockElem filterblock">
	<form id="filterForm">
		<div class="filters row-fluid">
			<div class="span4">
				Transaction Date:
				<div class="input-prepend">
					<span class="add-on">From</span>
					<input class="span5" type="text" name="date_from" id="date_from" placeholder="Start Date">
					<span class="add-on">To</span>
					<input class="span5" type="text" name="date_to" id="date_to" placeholder="Stop Date">
				</div>
			</div>
			<div class="span2" style="margin:0 -10px 0 30px">
				<label>Transaction Type:<select name="tType" id="tType" multiple data-placeholder="Select">
						<option></option>
						<option value="credit">CHARGE</option>
						<option value="debit">PAYMENT</option>
						<option value="discount">DISCOUNT</option>
						<option value="reversal">REVERSAL</option>
						<option value="refund">REFUND</option>
						<option value="write-off">WRITE-OFF</option>
						<option value="transfer-debit">TRANSFER</option>
					</select></label>
			</div>
			<div class="span3">
				Bill Source:
				<label><select name="bill_source_ids[]" id="bill_source_ids" multiple="multiple" class="wide">
						<?php foreach ($sources as $source) { ?>
							<option value="<?= $source->getId() ?>"><?= ucwords( str_replace('_', ' ', $source->getName())) ?></option><?php } ?>
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
						</select>
				</label>
			</div>
			<div class="span1">
				<button type="button" id="StatementFilterBtn" class="btn wide" style="margin-top:24px">Apply</button>
			</div>
			<div class="span1">
				<button type="button" class="btn-link wide" id="resetFilter" style="margin-top: 24px">Reset</button>
			</div>

		</div>
	</form>
</div>
<!--if action == invoice-->
<?php
$pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : 10;
$outstanding_total = 0;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
$data = (new BillDAO())->getPatientUnInvoicedBills($_REQUEST['id'], $page, $pageSize);
$totalSearch = $data->total;
?>
<div id="area11" class="blockElem invoice">
	<div class="row-fluid">
		<h6 class="pull-left span6">Current Invoice Items: </h6>
		<label class="pull-right span2">Page Size
			<select data-placeholder="Page Size" name="pageSize">
				<option>10</option>
				<option<?= isset($_REQUEST['pageSize']) && $_REQUEST['pageSize']== 20 ? ' selected':'' ?>>20</option>
				<option<?= isset($_REQUEST['pageSize']) && $_REQUEST['pageSize']== 50 ? ' selected':'' ?>>50</option>
				<option<?= isset($_REQUEST['pageSize']) && $_REQUEST['pageSize']== 100 ? ' selected':'' ?>>100</option>
			</select>
		</label>
	</div>
	<br/>
	<table class="data table table-hover table-striped">
		<thead>
		<tr>
			<th><label><input type="checkbox" id="invoiceAll_Sel"> Bill #</label></th>
			<th>Description</th>
			<th>Date</th>
			<th class="amount">Amount</th>
			<th><strong>Responsible</strong></th>
			<!--<th>*</th>-->
		</tr>
		</thead>
		<?php foreach ($data->data as $row) {//$row = new Bill();
			$item = (new InsuranceItemsCostDAO())->getInsuranceItem($row->item_code, $row->patient_id); ?>
			<!-- else start repeat-->
			<tr>
				<td>
					<label class="nowrap"><input type="checkbox" name="bills[]" value="<?= $row->bill_id ?>" <?= isset($_SESSION['invoice_items_all']) && in_array($row->bill_id, $_SESSION['invoice_items_all'] ) ? 'checked' :'' ?>> <?= $row->bill_id ?>
					</label></td>
				<td>
					<?= ($row->item_code && $item != null) ? '[<b>' . truncate($item->type, -1, false) . '</b>]' : '' ?>
					<?= $row->description; ?></td>
				<td nowrap><?= date(MainConfig::$dateTimeFormat, strtotime($row->transaction_date)) ?></td>
				<td class="amount"><?= number_format($row->amount); ?></td>
				<td nowrap><?= ($row->receiver == null) ? '' : $row->receiverName ?></td>
			</tr>
			<!--end repeat-->
		<?php } ?>
	</table>
	<div class="list11 dataTables_wrapper no-footer">
		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
			results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
		</div>
		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
				records</a>
		</div>
	</div>
	<div class="btn-block">
		<button <?= $totalSearch == 0 ? "disabled " : "" ?>id="printInvoiceBtn" class="btn"><i class="icon-print"></i>PROCESS
			INVOICE
		</button>
	</div>
</div>
<div class="blockElem insuranceBills">
	<div class="ball"></div>
</div>
<div class="blockElem estimatedBills">
    <div class="ball"></div>
</div>
<div class="blockElem invoices">
	<div class="ball"></div>
</div>
<div class="blockElem claims">
	<div class="ball"></div>
</div>
<div class="blockElem promos">
	<div class="ball"></div>
</div>
<div class="blockElem pa_codes">
	<div class="ball"></div>
</div>
<div class="blockElem unreviewed">
	<div class="ball"></div>
</div>
<div class="blockElem" id="invoice_grouped" style="display: none">
	<p align="center"><strong>Current Invoice Items:</strong></p><br/>
	<table class="data table table-hover table-striped">
		<thead>
		<tr>
			<th><strong>Bill Source</strong></th>
			<th class="amount"><strong>Amount</strong></th>
		</tr>
		</thead>
		<?php
		$outstanding_total = 0;
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT bs.name AS bill_source, SUM(b.amount) AS amount FROM bills b LEFT JOIN bills_source bs ON b.bill_source_id=bs.id LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE (b.invoiced <> 'yes' OR b.invoiced IS NULL) AND b.patient_id=" . escape($_GET['id']) . " AND b.transaction_type='credit' AND s.pay_type = 'self' AND b.cancelled_on IS NULL GROUP BY bs.name";

		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		?>
		<!--if num_rows == 0-->
		<?php if ($stmt->rowCount() == 0) { ?>
			<tr>
				<td><em>No un-invoiced items</em></td>
				<td>&nbsp;</td>
			</tr>
			<!--end if-->
		<?php } else {
			do { ?>
				<!-- else start repeat-->
				<tr>
					<td><h5><?= ucwords($row['bill_source']); ?></h5></td>
					<td class="amount"><?= number_format($row['amount'], 2);
						$outstanding_total = $row['amount'] + $outstanding_total; ?></td>
				</tr>
				<!--end repeat-->
			<?php } while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
		} ?>
	</table>
</div>
<!--/if action == invoice-->
<div class="blockElem statements">Statements<br/>
	<div class="ball"></div>
</div>
<?php if ($this_user->hasRole($protect->cashier)) { ?>
	<div class="blockElem voucher">Vouchers: <br/>
		<table class="data wide" border="0">
			<tr>
				<td valign="top">
					<div class="row-fluid">
						<label class="span6">Method<select id="voucher_method_id" name="voucher_method_id"></select></label>
						<label class="span4">Code <input type="text" name="voucher_auth_code" onkeyup="this.value=this.value.toUpperCase()"> </label>
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
			<!--repeat-->

			<tr>
				<td valign="top" colspan="2">
					<div class="row-fluid">
						<label class="span10">Discount (value)<input disabled type="number" min="0" name="discount_val" id="discount_val" value="0"/></label>
						<div class="span2">
							<label>&nbsp;<span class="loader" id="discount_loader"></span><br>
								<button disabled type="button" name="button" id="button" class="btn pull-right wide">Discount</button>
							</label>
						</div>
					</div>
				</td>
			</tr>
			<!-- end repeat-->
		</table>
	</div>
<?php } ?>
<div class="blockElem total">Totals
	<table width="100%" border="0" class="data">
		<!--<tr>-->
		<!--	<td valign="top">Invoice Total</td>-->
		<!--	<td valign="top" class="amount">--><?//= number_format($outstanding_total, 2) ?><!--</td>-->
		<!--</tr>-->
		<tr>
			<td valign="top"><h5>Total Charges <em class="fadedText">[Includes Refunds]</em></h5></td>
			<td valign="top" id="totalMinusDiscount" class="amount"><?= number_format($credits, 2) ?></td>
		</tr>
		<tr>
			<td valign="top"><h5>Total Payments <em class="fadedText">[Includes Discounts, Reversals, WriteOffs,
						Transfers]</em></h5></td>
			<td valign="top" id="prevbal"
			    class="amount"><?= (($debits < 0) ? '<span style="color:red">[CR]</span> ' : '<span style="color:green">[DR]</span> ') . number_format(abs($debits), 2) ?></td>
		</tr>
		<tr>
			<td valign="top"><strong>Current Balance</strong></td>
			<td valign="top" id="cbal" class="amount"><?= number_format(($credits - $debits), 2, '.', ''); ?></td>
		</tr>
	</table>
</div>
<!--if action == payment or invoice-->
<?php if ($this_user->hasRole($protect->cashier)) { ?>
	<div class="blockElem payment">
		<div class="row-fluid">
			<label class="span4">Payment Method:<select name="pay_method" id="pay_method" class="wide">
				</select></label>
			<label class="span2">Due Date: <input type="text" name="due_date"> </label>
			<label class="span2">Payment Reference:
				<input type="text" name="payment_reference"></label>
			<label class="span2">Amount Paying: (<?= $currency->getSymbolLeft() . $currency->getSymbolRight() ?>)
				<input type="text" min="0" name="paid_value" class="price-input_" id="paid_value"></label>
			<span class="span2"><label><span class="loader" id="amount_loader"></span>
					<button type="button" id="button2" class="btn pull-right wide">Save &amp; Print</button></label></span>
		</div>
	</div>
<?php } ?>
<!--/if action == payment or invoice-->
<div class="blockElem balance">
	<table width="100%" border="0">
		<tr>
			<td valign="top">Present Balance (Outstanding)</td>
			<td valign="top" id="presbal" class="amount price-input"></td>
		</tr>
	</table>
</div>
<script>
	$(document).on('change', 'select[name="pageSize"]', function(e){
		if(!e.handled){
			$.get("/billing/patient_bill_doc.php", {pageSize: $('select[name="pageSize"]').val() ,page: 0, id: '<?= $_GET['id']?>'}, function (s) {
				$("#area11").html($(s).filter("#area11").html());
			});
			e.handled = true;
		}
	});
	$(document).on('click', '.list11.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var items = $('input[name="bills[]"]:checkbox');
			var selItems = [];
			$.each(items, function (i, el) {
				if ($(el).is(":checked")) {
					selItems.push($(el).val());
				}
			});
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.get("/billing/patient_bill_doc.php", {pageSize: $('select[name="pageSize"]').val() ,page: page, id: '<?= $_GET['id']?>', lines: selItems.join()}, function (s) {
					$("#area11").html($(s).filter("#area11").html());
				});
			}
			e.clicked = true;
		}
	}).on('click', '#transferBtn', function (e) {
		if (!e.handled) {
			var items = $('input[name="tBill[]"]:checkbox');
			var selItems = [];
			$.each(items, function (i, el) {
				if ($(el).is(":checked")) {
					selItems.push($(el).val());
				}
			});
			Boxy.load("/billing/boxy.credit.transfer_batch.php?id=<?= $_GET['id']?><?= (isset($_GET['aid']) ? '&aid=' . @$_GET['aid'] : '') ?>&items=" + selItems.join(), {title: 'Credit Transfer'});

			e.handled = true;
		}

	}).on('click', '#drtBtn', function (e) {
		if (!e.handled) {
			var items = $('input[name="tBill[]"]:checkbox');
			var selItems = [];
			$.each(items, function (i, el) {
				if ($(el).is(":checked")) {
					selItems.push($(el).val());
				}
			});
			Boxy.load("/billing/boxy.drt.php?id=<?= $_GET['id']?><?= (isset($_GET['aid']) ? '&aid=' . @$_GET['aid'] : '') ?>&items=" + selItems.join(), {title: 'D. R. T.'});
			e.handled = true;
		}

	}).on('click', '#button_cancel_transfer', function () {
		$('input[name="tBill[]"]:checkbox').css('visibility', 'hidden');
		$(this).remove();
	}).on('click', '#requestPABtn', function (e) {
		if (!e.handled) {
			var items = $('input[name="tBill[]"]:checkbox');
			var selItems = [];
			$.each(items, function (i, el) {
				if ($(el).is(":checked")) {
					selItems.push($(el).val());
				}
			});
			Boxy.load("/billing/boxy.request_pa_code.php?id=<?= $_GET['id']?>&items=" + selItems.join(), {title: 'Request Authorization Code for services Transfer'});
			e.handled = true;
		}

	}).on('click', '#claimsBtn', function (e) {
		if (!e.handled) {
			var items = $('input[name="insBill[]"]:checkbox');
			var selItems = [];
			$.each(items, function (i, el) {
				if ($(el).is(":checked")) {
					selItems.push($(el).val());
				}
			});
			Boxy.load("/billing/boxy.start.claims.php?id=<?= $_GET['id']?>&items=" + selItems.join(), {title: 'Process Claims'});
			e.handled = true;
		}

	}).on('click', '#validateBtn', function (e) {
		if (!e.handled) {
			var items = $('input[name="insBill[]"]:checkbox');
			var selItems = [];
			$.each(items, function (i, el) {
				if ($(el).is(":checked")) {
					selItems.push(parseInt($(el).val()));
				}
			});
			window.open("/billing/validation_sheet.php?pid=<?= $_GET['id']?>&items=" + selItems.join());
			e.handled = true;
		}

	}).on('click', '.rewriteBill a', function (e) {
		var id = $(this).data("id");
		if (!e.handled) {
			<?php if($this_user->hasRole($protect->bill_auditor) || $this_user->hasRole($protect->records) ){?>
			Boxy.load("/billing/rewrite_bill.php?id=" + id, {
				afterHide: function () {
					setTimeout(function () {
						showTabs(7);
						setTimeout(function () {
							$('a[href="#insuredBills"]').get(0).click();
						}, 500)
					}, 50);
				}
			});
			<?php } ?>
			e.handled = true;
		}
	}).on('change', '#invoiceAll_Sel', function (e) {
		if (!e.handled) {
			if($(this).is(":checked")){
				$('[name="bills[]"]:checkbox').prop('checked', true).iCheck('update');
			} else {
				$('[name="bills[]"]:checkbox').prop('checked', false).iCheck('update');
			}
			e.handled = true;
		}
	}).on('click', '#PrivateClaimsBtn', function (e) {
		if (!e.handled) {
			var items = $('input[name="tBill[]"]:checkbox');
			var selItems = [];
			$.each(items, function (i, el) {
				if ($(el).is(":checked")) {
					selItems.push($(el).val());
				}
			});
			Boxy.load("/billing/boxy.start_private.claims.php?id=<?= $_GET['id']?>&items=" + selItems.join(), {title: 'Process bills'});
			
			e.handled = true;
		}

	});

</script>

