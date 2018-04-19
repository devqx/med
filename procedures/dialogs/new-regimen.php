<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/14
 * Time: 12:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugBatch.php';

$bills = new Bills();
$procedure = (new PatientProcedureDAO())->get($_GET['id']);
$protect = new Protect();
$pat = (new PatientDemographDAO())->getPatient($procedure->getPatient()->getId(), false, null, null);

$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
//$generics = (new DrugGenericDAO())->all();


$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$selfOwe = $_ > 0 ? $_ : 0;
if (!isset($_SESSION)) {
	session_start();
}
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedureRegimen.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugBatch.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureRegimenDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	
	$regimen = new PatientProcedureRegimen();
	$regimen->setPatientProcedure((new PatientProcedureDAO())->get($_GET['id'], $pdo)); //new PatientProcedure() );
	
	$data = [];
	$dao = new  DrugDAO();
	foreach ($_POST['drug'] as $index => $it) {
	
	$dr = !is_blank($_POST['drug'][$index]) ? $_POST['drug'][$index] : null;
	$drug = $dao->getDrug($dr, true, $pdo);
	$prescribed_by = $_POST['prescribed_by'];
	$quantity = !is_blank($_POST['quantity'][$index]) ? $_POST['quantity'][$index] : 0;
	$units = !is_blank($_POST['units'][$index]) ? $_POST['units'][$index] : null;
	$note = !is_blank($_POST['note']) ? $_POST['note'] : null;
		
		$batch = (new DrugBatchDAO())->getBatch($_POST['batch'][$index], $pdo);
	$batch_quantity = $batch->getQuantity();
	
	$regimen->setBatch($batch);
	$regimen->setDrug($dr);
	$regimen->setUnit($units);
	$regimen->setNote($note);
	$amount = $drug->getBasePrice() * $quantity;
	
	if (count(array_filter($_POST['drug'])) <= 0) {
		exit("error:Drug brand is required");
	}
	
	$billDesc = '';
	
		$Drug = (new DrugDAO())->getDrug($_POST['drug'][$index], true, $pdo);
		$billDesc = $Drug->getName();
		$regimen->setDrugGeneric($Drug->getGeneric());
		$regimen->setDrug($Drug);
	
	if (count(array_filter($_POST['dose'])) > 0) {
		$regimen->setQuantity($_POST['dose'][$index]);
	} else {
		exit("error:Dose used is required");
	}
	
	if (!is_null($batch->getExpirationDate()) && $batch->getExpirationDate() < date('Y-m-d')) {
		exit("error:The selected batch is expired");
	}
	if ($quantity > $batch_quantity) {
		exit("error:Quantity of batch requested not available");
	}
	
	$s_center = (new ServiceCenterDAO())->get($_POST['pharmacy_id'], $pdo);
	$regimen->setRequestingUser((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], $pdo));
	$newRegimen = (new PatientProcedureRegimenDAO())->add($regimen, $pdo);
	if ($newRegimen !== null) {
		if ($dr !== null && $batch !== null && $quantity !== 0) {
			$pres = new Prescription();
			$inpatient = isset($_GET['aid']) ? $_GET['aid'] : null;
			$pres->setPatient($pat);
			//$pres->setInPatient($inpatient);
			$pres->setPrescribedBy($_POST['prescribed_by']);
			$pres->setRequestedBy($this_user);
			$pres->setNote($_POST['note']);
			$pres->setHospital($this_user->getClinic());
			$pres->setServiceCentre($s_center);
			$pres->setEncounter(!is_blank(@$_REQUEST['encounter_id']) ? new Encounter(@$_REQUEST['encounter_id']) : null);
			$pds = array();
			
			$pd = new PrescriptionData();
			
			
			$pd->setDrug(!is_blank($_POST['drug'][$index]) ? $regimen->getDrug() : null);
			$pd->setGeneric($regimen->getDrugGeneric());
			$pd->setDose($_POST['dose'][$index]);
			$pd->setStatus('completed');
			$pd->setDuration(1);
			$pd->setFrequency(1);
			$pd->setRequestedBy($this_user);
			$pd->setRefillNumber(1);
			$pd->setHospital($this_user->getClinic());
			$pd->setBatch($batch);
			$pd->setCompletedBy($this_user);
			$pds[] = $pd;
			$pres->setData($pds);
			$p = (new PrescriptionDAO())->addPrescription($pres, $pdo);
			
			$bil = new Bill();
			$bil->setPatient($pat);
			$bil->setDescription("Prescription used in procedure: $billDesc");
			
			$bil->setItem($drug);
			$bil->setSource((new BillSourceDAO())->findSourceById(2, $pdo));
			$bil->setTransactionType("credit");
			$bil->setAmount($amount);
			$bil->setDiscounted(null);
			$bil->setDiscountedBy(null);
			$bil->setClinic($this_user->getClinic());
			$bil->setBilledTo($pat->getScheme());
			
			$bil->setCostCentre(null);
			
			$bill = (new BillDAO())->addBill($bil, $_POST['quantity'][$index], $pdo, $inpatient);
			$regimen->setBillLine($bill->getId());
			$regimen->setStatus('completed');
			$procedure_regimen = (new PatientProcedureRegimenDAO())->updateStatus($regimen, $pdo);
			if ($p === null && $procedure_regimen == null) {
				$pdo->rollBack();
				exit("error:Failed to add prescription");
			}
			$drug_batchDao = new DrugBatchDAO();
			$drug_batch = new DrugBatch();
			
			$rem_quantity = $batch_quantity - $quantity;
			$drug_batch->setQuantity($rem_quantity);
			$drug_batch->setServiceCentre($s_center);
			$drug_batch->setId($batch->getId());
			$batch_updte = $drug_batchDao->stockAdjust($drug_batch, $pdo);
			
			if ($batch_updte == null) {
				exit("error:Failed to update inventory");
			}
			
		} else {
			exit("error:Could not save successfully");
		}
	}else{
		exit("error:Failed to add regimen");
		
	}
	
}
	
	exit("success:Saved and processed");
}


$activeGenericsOnly = true;
$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php';
?>
<section style="width: 600px;">
	<div class="well">
		Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return AIM.submit(this, {onStart:__start,onComplete:__done})">
		<label> Business Unit/Service Center
			<select id="pharmacy_id" name="pharmacy_id" data-placeholder="-- Select pharmacy --">
				<option value=""></option>
				<?php foreach ($pharmacies as $k => $pharm) { ?>
					<option value="<?= $pharm->getId() ?>"><?= $pharm->getName() ?></option>
				<?php } ?>
			</select></label>
		<!--<label>Drug Generic <span id="drug-info" class="fadedText pull-right"></span><input type="hidden" name="drug_generic" id="drug_generic"></label>-->
		<!--<label style="margin-bottom: -5px">Dose(s) <input type="text" name="dose" id="dose" required/></label>-->
		<label <?php if ($this_user->hasRole($protect->doctor_role)){ ?>class="hide"<?php } ?>>Prescribed By
			<input type="text" id="prescribed_by" name="prescribed_by" placeholder="Enter Fullname" required value="<?= ($this_user->hasRole($protect->doctor_role)) ? $this_user->getFullname() : '' ?>">
		</label>
		<label><span class="pull-right"><a class="btn btn-mini add_item"><i class="icon-plus"></i></a> </span> </label>
    <div class="clear"></div>
		<div class="row-fluid procedureRegimes">
			<label class="span3">Drug Name<input type="text" name="drug[]" id="drug"></label>
			<label class="span2">Batch<input type="text" name="batch[]" id="batch"></label>
			<label class="span2">Quantity<input type="number" min="0" name="quantity[]" ></label>
			<label class="span2">Dose<input type="text"  name="dose[]" required></label>
		<label class="span2">Unit<input type="text" name="units[]" readonly></label>
		</div>
		<label style="margin-bottom: -5px">Note <textarea cols="5" name="note"></textarea></label>
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>

	</form>
</section>
<script type="text/javascript">
	//var drug_generic = <?//= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>//;
	var drugs = <?=json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
	function __start() {
		$(document).trigger('ajaxSend');
	}
	function __done(s) {
		$(document).trigger('ajaxStop');
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		} else if (data[0] === "success") {
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
		}
	}
	
	$(document).ready(function () {
		getDrug();

		/*$("#drug_generic").select2({
			width: '100%',
			allowClear: true,
			placeholder: '--Drug Generics--',
			data: function () {
				return {results: drug_generic, text: 'name'};
			},
			formatResult: function (data) {
				return data.name + " (" + data.form + ") " + data.weight;
			},
			formatSelection: function (data) {
				return data.name + " (" + data.form + ") " + data.weight;
			}
		}).on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				if (id !== "") {
					getDrug(id);
				} else {
					getDrug();
				}
			}
		}); */

		$('.add_item').on('click', function (e) {
			if (!e.handled) {
				var tag_holder = '<div class="row-fluid procedureRegimes"> '+
				'<label class="span3">Drug Name<input type="text" name="drug[]" ></label>'+
					'<label class="span2">Batch<input type="text" name="batch[]"></label>'+
					'<label class="span2">Quantity<input type="number" min="0" name="quantity[]" ></label>'+
					'<label class="span2">Dose<input type="text"  name="dose[]" required></label>'+
					'<label  class="span2" >Unit<input type="text" name="units[]"  readonly></label>'+
				'<label class="span1" style="margin-top: 20px;"><a class="btn btn-mini remove_item">&minus;</a></label></div>';
				$('.procedureRegimes:last').after(tag_holder);
				$(document).trigger('ajaxStop');
				e.handled = true;
				getDrug();
			}
			$('.remove_item').on('click', function (e) {
				if (!e.handled) {
					if ($('.procedureRegimes').length > 1) {
						$(this).parents('.procedureRegimes').remove();
					}
					e.handled = true;
				}
			});

			$('input[name="drug[]"]').on('change', function (e) {
				if (!e.handled) {
					var id = $(this).val();
					var batch_id = $(this).parent().next().find('input');
					console.log(batch_id);
					var somu = $(this).parent().next().next().next().next().find('input');
					console.log(somu);
					if (id !== '') {
						$.ajax({
							url: '/api/get_batches.php',
							type: 'POST',
							dataType: 'json',
							data: {did: id},
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
						$(somu).val(e.added.stockUOM);
								//var id = $(this).val();
								//if (id !== '') {
								//	getUnit(e.added.stockUOM);
								//	getPrice(e.added.basePrice);
								//	getBatch(id);
								//}
								//else {
								//	setBatch([]);
								//	// $('input[name="batch"]').select2('data', [], true);
								//	$('input[name="units"]').val("");
								//	$('input[name="price"]').val('');
								//}
						
					}
				}
			});
		});
		
		$('input[name="drug[]"]').on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				var batch_id = ($(this)).parent().next().find('input');
				console.log(batch_id);
				var somu = $(this).parent().next().next().next().next().find('input');
				console.log(somu);
				if (id !== '') {
					$.ajax({
						url: '/api/get_batches.php',
						type: 'POST',
						dataType: 'json',
						data: {did: id},
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
					$(somu).val(e.added.stockUOM);
					//var id = $(this).val();
					//if (id !== '') {
					//	getUnit(e.added.stockUOM);
					//	getPrice(e.added.basePrice);
					//	getBatch(id);
					//}
					//else {
					//	setBatch([]);
					//	// $('input[name="batch"]').select2('data', [], true);
					//	$('input[name="units"]').val("");
					//	$('input[name="price"]').val('');
					//}

				}
			}
		});

	});

	function getUnit(unit) {
		if (unit !== '') {
			$("#units").val(unit);
		}
		else {
			$("#units").val('N/A');
		}
	}

	function getPrice(price) {
		if (price !== '') {
			$('#price').val(price);
		} else {
			$('#price').val('');
		}

	}
	function getDrug(id) {
		$.ajax({
			url: '/api/get_drugs.php',
			type: 'POST',
			dataType: 'json',
			data: {gid: id},
			success: function (result) {
				setDrug(result);
			}
		});
	}

	function setDrug(data) {
		$('input[name="drug[]"]').select2({
			width: '100%',
			allowClear: true,
			placeholder: '--Drugs--',
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (result) {
				return result.name;
			},
			formatSelection: function (result) {
				return result.name;

			}
		})
		//	.on('change', function (e) {
		//	if (!e.handled) {
		//		var id = $(this).val();
		//		if (id !== '') {
		//			getUnit(e.added.stockUOM);
		//			getPrice(e.added.basePrice);
		//			getBatch(id);
		//		}
		//		else {
		//			setBatch([]);
		//			// $('input[name="batch"]').select2('data', [], true);
		//			$('input[name="units"]').val("");
		//			$('input[name="price"]').val('');
		//		}
		//	}
		//
		//});
	}


	//    function  filterBatch(data) {
	//
	//        $.each(data, function (key,value) {
	//            if (value.expirationDate < new  Date()){
	//                expired_batches.push(value);
	//
	//            }
	//            else{
	//                batches_inuse.push(value);
	//
	//            }
	//        });
	//      return batches_inuse;
	//    }
	//function setBatch(data) {
	//
	//	$('input[name="batch[]"]').select2({
	//		width: '100%',
	//		allowClear: true,
	//		placeholder: '--Batch--',
	//		data: function () {
	//			return {results: data, text: 'name'}
	//		},
	//		formatResult: function (result) {
	//			return result.name
	//		},
	//		formatSelection: function (result) {
	//			return result.name;
	//
	//		}
	//	}).on('change', function (b) {
	//		console.log(b);
	//		if (!b.handled) {
	//
	//			if (b.val !== '') {
	//				$("#expdate").val(b.added.expirationDate);
	//				$("#batch_quantity").val(b.added.quantity);
	//			}
	//			else {
	//				$('input[name="batch"]').select2('data', '');
	//			}
	//		}
	//
	//
	//	});
	//}
	//
	//function getBatch(id) {
	//	$.ajax({
	//		url: '/api/get_batches.php',
	//		type: 'POST',
	//		dataType: 'json',
	//		data: {did: id},
	//		success: function (result) {
	//			//console.log(result);
	//			setBatch(result);
	//
	//		}
	//	});
	//}
	


</script>