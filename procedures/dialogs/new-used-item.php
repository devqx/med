<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/14
 * Time: 12:19 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequestData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGrpScDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemBatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

$service_center = (new ServiceCenterDAO())->all('item');
$procedure = (new PatientProcedureDAO())->get($_GET['id']);
$pat = (new PatientDemographDAO())->getPatient($procedure->getPatient()->getId(), false, null, null);
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId(), null)->getAmount();
$bills = new Bills();
$_ = $bills->_getPatientPaymentsTotals($pat->getId(), null, null) + $bills->_getPatientCreditTotals($pat->getId(), null, null);
$selfOwe = $_ > 0 ? $_ : 0;

if ($_POST) {
	@session_start();
	if(!isset($_SESSION['staffID'])){exit('error:No active session');}
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo);
	$data = [];
	
	$procedure = (new PatientProcedureDAO())->get($_POST['prid'], $pdo);
	$pat = (new PatientDemographDAO())->getPatient($procedure->getPatient()->getId(), false, $pdo, null);
	$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId(), $pdo)->getAmount();
	$_ = $bills->_getPatientPaymentsTotals($pat->getId(), null, $pdo) + $bills->_getPatientCreditTotals($pat->getId(), null, $pdo);
	$selfOwe = $_ > 0 ? $_ : 0;
	$now = date('Y-m-d H:i:s');
	if ($selfOwe - $creditLimit > 0) {
		//$pdo->rollBack();
		//exit("error:Patient has outstanding credit");
	}
	
	if (is_blank($_POST['service_center_id'])) {
		$pdo->rollBack();
		exit('error:select service center');
	}
	
	if (is_blank($_POST['generic_id']) && count(array_filter($_POST['item'])) <= 0) {
		$pdo->rollBack();
		exit("error:Item or generic name is required");
	}
	if (count(array_filter($_POST['batch'])) <= 0) {
		$pdo->rollBack();
		exit("error:Item batch is required");
	}
	
	if (count(array_filter($_POST['quantity'])) <= 0) {
		$pdo->rollBack();
		exit("error:Please enter the quantity used");
	}
	
	$center = (new ServiceCenterDAO())->get($_POST['service_center_id'], $pdo);
	$rest_code = (new PatientItemRequest())->generateItemCode($pdo);
	
	$data = [];
	foreach ($_POST['item'] as $index => $it) {
		$bt_D = (new ItemBatchDAO())->getBatch($_POST['batch'][$index], $pdo);
		if ($bt_D->getQuantity() >= $_POST['quantity'][$index]) {
			$batch = new ItemBatch();
			$batch->setQuantity($bt_D->getQuantity() - $_POST['quantity'][$index]);
			$batch->setId($bt_D->getId());
			$batcD = (new ItemBatchDAO())->update($batch, $pdo);
			
		} else {
			$pdo->rollBack();
			exit("error:You don't have enough stock");
		}
		
		$gen = (new ItemDAO())->getItem($_POST['item'][$index], $pdo)->getGeneric();
		$batch_ = (new ItemBatchDAO())->getBatch($_POST['batch'][$index], $pdo);
		$bil = new Bill();
		$pid = (new PatientProcedureDAO())->get($_POST['prid'], $pdo)->getPatient()->getId();
		$pat = (new PatientDemographDAO())->getPatient($pid, false, $pdo, null);
		$bil->setPatient($pat);
		$bil->setDescription("Hospital Consumable Item (" . (new ItemDAO())->getItem($_POST['item'][$index], $pdo)->getName() . " )");
		$bil->setItem((new ItemDAO())->getItem($_POST['item'][$index], $pdo));
		$bil->setSource((new BillSourceDAO())->findSourceById(8, $pdo));
		$bil->setTransactionType("credit");
		$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($bil->getItem()->getCode(), $bil->getPatient()->getId(), true, $pdo);
		$bil->setAmount($price * $_POST['quantity'][$index]);
		$bil->setDiscounted(null);
		$bil->setDiscountedBy(null);
		$bil->setClinic($staff->getClinic());
		$bil->setBilledTo($pat->getScheme());
		$request = (new PatientProcedureDAO())->get($_POST['prid'], $pdo);
		$costCentre = (is_null($request->getServiceCentre())) ? null : (new ServiceCenterDAO())->get($request->getServiceCentre()->getId(), $pdo)->getCostCentre();
		$bil->setCostCentre($costCentre);
		$bill = (new BillDAO())->addBill($bil, $_POST['quantity'][$index], $pdo);
		
		if ($bill == null) {
			$pdo->rollBack();
			exit("error:Unable to bill item");
		}
		$data[] = (new PatientItemRequestData())->setItem((new ItemDAO())->getItem($_POST['item'][$index], $pdo))->setHospId(1)->setGroupCode($rest_code)->setQuantity($_POST["quantity"][$index])->setStatus('completed')->setBatch($batch_)->setFilledQuantity($_POST['quantity'][$index])->setFilledBy($staff)->setGeneric($gen)->setCompletedBy($staff)->setFilledDate($now)->setCompletedOn($now);
	}
	unset($it);
	$pat_request = (new PatientItemRequest())->setServiceCenter($center->getId())->setPatient($pat)->setCode($rest_code)->setRequestedBy($staff)->setRequestNote($_POST['note'])->setProcedure($procedure)->setData($data)->add($pdo);
	if ($pat_request == null) {
		$pdo->rollBack();
		exit("error:Unable to add consumable");
	}
	
	$pdo->commit();
	exit("ok:Item added successfully");
}
?>
<section style="width: 650px;">
	<?php if ($bb > 0) { ?>
		<div class="well">
			Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>
		</div>
	<?php } ?>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" id="itemForm">
		<label>Service Center:
			<select name="service_center_id" placeholder=" -- select a service center --">
				<option></option>
				<?php foreach ($service_center as $center) { ?>
					<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label style="margin-top: 20px;">
			<input type="hidden" name="group_id" id="group_id">
		</label><label style="margin-top: 20px;">
			<input type="hidden" name="generic_id" id="generic_id">
		</label>
		<label><span class="pull-right"><a class="btn btn-mini add_item"><i class="icon-plus"></i></a> </span> </label>
		<div class="row-fluid procedureItems">
			<label style="margin-top: 20px;" class="span4"><input type="hidden" name="item[]"></label>
			<label style="margin-top: 20px;" class="span4"><input type="hidden" name="batch[]"></label>
			<label style="margin-top: 20px;" class="span3"><input type="number" name="quantity[]" placeholder="type quantity here"></label>
		</div>
		<input type="hidden" name="prid" value="<?= $_GET['id'] ?>">

		<label>
			Note
			<textarea name="note" placeholder="Request Note" id="note" cols="3"></textarea>
		</label>
		<div class="btn-block">
			<button type="button" class="btn" id="saveItems">Add Items</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>

	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		getItems();
		getGeneric();
		$("#saveItems").click(function () {
			$.ajax({
				url: "<?= $_SERVER['REQUEST_URI'] ?>",
				type: "post",
				data: $("#itemForm").serialize(),
				beforeSend: function () {
				},
				success: function (d) {
					if (d.split(":")[0].trim() === "ok") {
						Boxy.get($(".close")).hideAndUnload();
						$("#notify").notify("create", {text: "Item added successfully"}, {expires: 3000});
					} else {
						$("#notify").notify("create", {text: d.split(":")[1]}, {expires: 3000});
					}
				},
				error: function (d) {
					$("#notify").notify("create", {text: "Sorry action failed"}, {expires: 3000});
				}
			});
		});

		$('select[name="service_center_id"]').on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				getGroup(id);
			}
		});


		$('input[name="group_id"]').on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				if (id !== "") {
					getGeneric(id);
				} else {
					getGeneric();
				}

			}
		});

		$('input[name="generic_id"]').on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				if (id !== "") {
					getItems(id);
				} else {
					getItems();
				}
			}
		});

		$('.add_item').on('click', function (e) {
			$('input[name="generic_id"]').select2('data', null);
			if (!e.handled) {
				var tag_holder = '<div class="row-fluid procedureItems"><label style="margin-top: 20px;" class="span4"><input type="hidden" name="item[]" class="item"></label><label style="margin-top: 20px;" class="span4"><input type="hidden" name="batch[]" id="batch"></label><label style="margin-top: 20px;" class="span3"><input type="number" name="quantity[]" placeholder="type quantity here"></label><label class="span1" style="margin-top: 20px;"><a class="btn btn-mini remove_item">&minus;</a></label></div>';
				$('.procedureItems:last').after(tag_holder);
				$(document).trigger('ajaxStop');
				e.handled = true;
				getItems();
			}
			$('.remove_item').on('click', function (e) {
				if (!e.handled) {
					if ($('.procedureItems').length > 1) {
						$(this).parents('.procedureItems').remove();
					}
					e.handled = true;
				}
			});
			
			$('input[name="item[]"]').on('change', function (e) {
				if (!e.handled) {
					var id = $(this).val();
					var batch_id = $(this).parent().next().find('input');
					if (id !== '') {
						$.ajax({
							url: '/api/get_item.php',
							type: 'POST',
							dataType: 'json',
							data: {i_id: id},
							success: function (data) {
								$(batch_id).select2({
									width: '100%',
									allowClear: true,
									placeholder: "select item batch",
									data: function () {
										return {results: data, text: 'name'};
									},
									formatResult: function (result) {
										return result.name;
									},
									formatSelection: function (result) {
										return result.name;
									}
								});
							}
						});
					}
				}
			});
		});


		$('input[name="item[]"]').on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				var batch_id = $(this).parent().next().find('input');
				if (id !== '') {
					$.ajax({
						url: '/api/get_item.php',
						type: 'POST',
						dataType: 'json',
						data: {i_id: id},
						success: function (data) {
							$(batch_id).select2({
								width: '100%',
								allowClear: true,
								placeholder: "select item batch",
								data: function () {
									return {results: data, text: 'name'};
								},
								formatResult: function (result) {
									return result.name;
								},
								formatSelection: function (result) {
									return result.name;
								}
							});
						}
					});
				}
			}
		});

	});

	function getGroup(id) {
		$.ajax({
			url: '/api/get_item.php',
			type: 'POST',
			dataType: 'json',
			data: {c_id: id},
			success: function (result) {
				setGroup(result);
			}
		});
	}


	function setGroup(data) {
		$('#group_id').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select generic group",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (result) {
				return result.name;
			},
			formatSelection: function (result) {
				return result.name;
			}
		});
	}

	function getGeneric(id) {
		$.ajax({
			url: '/api/get_item_generic.php',
			type: 'POST',
			dataType: 'json',
			data: {g_id: id},
			success: function (result) {
				setGeneric(result);
			}
		});
	}

	function setGeneric(data) {
		$('#generic_id').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select item generic name",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (result) {
				return result.name;
			},
			formatSelection: function (result) {
				return result.name;
			}
		});
	}

	function getItems(g) {
		$.ajax({
			url: '/api/get_item.php',
			type: 'POST',
			dataType: 'json',
			data: {gid: g},
			success: function (result) {
				setItems(result);
			}
		});
	}


	function setItems(data) {
		$('input[name="item[]"]').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select item",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (result) {
				return result.name;
			},
			formatSelection: function (result) {
				return result.name;
			}
		});
	}

	function getBatch(g) {
		$.ajax({
			url: '/api/get_item.php',
			type: 'POST',
			dataType: 'json',
			data: {i_id: g},
			success: function (result) {
				setBatch(result);
			}
		});
	}


	function setBatch(data) {
		$('input[name="batch"]').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select item batch",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (result) {
				return result.name;
			},
			formatSelection: function (result) {
				return result.name;
			}
		});
	}

</script>