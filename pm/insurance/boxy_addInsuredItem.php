<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceItemsCostDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Drug.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Lab.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Vaccine.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Clinic.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Item.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Ophthalmology.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Dentistry.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/NursingService.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Ward.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Package.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DRT.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceSchemeDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/InsuranceItemsCost.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffSpecialization.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PhysiotherapyItem.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/MedicalExam.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/AntenatalPackages.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/ivf/classes/GeneticLab.php";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();

$show = explode(",", $_REQUEST['show']);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$filter = !is_blank(@$_POST['filter']) ? $_POST['filter'] : NULL;
$show_ = isset($_POST['show']) ? $_POST['show'] : $show[0];
$sid = $_GET['sid'];

$data = (new InsuranceItemsCostDAO())->getNonInsuredItemsCostByScheme($sid, $show_, $page, $pageSize, $filter);
$itemCosts = $data->data;
$totalSearch = $data->total;
$scheme = (new InsuranceSchemeDAO())->get($sid, TRUE);

if (isset($_POST['sid'])) {
	$items = array();

	//For Drugs
	if (isset($_POST['drug_item'])) {
		foreach ($_POST['drug_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$drug = new Drug();
			$drug->setCode(explode("|", $idCode)[1]);
			$item->setItem($drug);
			$item->setCapitated(isset($_POST['drug_capitated_' . explode("|", $idCode)[0]]) && $_POST['drug_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['drug_type_' . explode("|", $idCode)[0]]);
			$item->setSellingPrice($_POST['drug_ins_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['drug_code_ins_' . explode("|", $idCode)[0]]);

			$items[] = $item;
		}
	}

	//For Labs
	if (isset($_POST['lab_item'])) {
		foreach ($_POST['lab_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$lab = new Lab();
			$lab->setCode(explode("|", $idCode)[1]);
			$item->setItem($lab);
			$item->setCapitated(isset($_POST['lab_capitated_' . explode("|", $idCode)[0]]) && $_POST['drug_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['lab_type_' . explode("|", $idCode)[0]]);
			$item->setSellingPrice($_POST['lab_ins_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['lab_code_ins_' . explode("|", $idCode)[0]]);

			$items[] = $item;
		}
	}

	//For Vaccines
	if (isset($_POST['vac_item'])) {
		foreach ($_POST['vac_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$vac = new Vaccine();
			$vac->setCode(explode("|", $idCode)[1]);
			$item->setItem($vac);
			$item->setCapitated(isset($_POST['vac_capitated_' . explode("|", $idCode)[0]]) && $_POST['vac_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['vac_type_' . explode("|", $idCode)[0]]);
			$item->setSellingPrice($_POST['vac_ins_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['vac_code_ins_' . explode("|", $idCode)[0]]);

			$items[] = $item;
		}
	}

	//For Consultancies
	if (isset($_POST['con_item'])) {
		foreach ($_POST['con_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$con = new StaffSpecialization();
			$con->setCode(explode("|", $idCode)[1]);
			$item->setItem($con);
			$item->setCapitated(isset($_POST['con_capitated_' . explode("|", $idCode)[0]]) && $_POST['con_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['con_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['con_code_ins_' . explode("|", $idCode)[0]]);
			$item->setSellingPrice($_POST['con_ins_' . explode("|", $idCode)[0]]);

			$items[] = $item;
		}
	}

	//For Imagery
	if (isset($_POST['img_item'])) {
		foreach ($_POST['img_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$con = new Scan();
			$con->setCode(explode("|", $idCode)[1]);
			$item->setItem($con);
			$item->setCapitated(isset($_POST['img_capitated_' . explode("|", $idCode)[0]]) && $_POST['img_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['img_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['img_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['img_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}

	//for procedures
	if (isset($_POST['pr_item'])) {
		foreach ($_POST['pr_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$con = new Procedure();
			$con->setCode(explode("|", $idCode)[1]);
			$item->setItem($con);
			$item->setCapitated(isset($_POST['pr_capitated_' . explode("|", $idCode)[0]]) && $_POST['pr_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['pr_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['pr_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['pr_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}

	//For Clinical Items
	if (isset($_POST['item_item'])) {
		foreach ($_POST['item_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$item_ = new Item();
			$item_->setCode(explode("|", $idCode)[1]);
			$item->setItem($item_);
			$item->setCapitated(isset($_POST['item_capitated_' . explode("|", $idCode)[0]]) && $_POST['item_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['item_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['item_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['item_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}

	//for bed/room types
	if (isset($_POST['bed_item'])) {
		foreach ($_POST['bed_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$con = new RoomType();
			$con->setCode(explode("|", $idCode)[1]);
			$item->setItem($con);
			$item->setCapitated(isset($_POST['bed_capitated_' . explode("|", $idCode)[0]]) && $_POST['bed_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['bed_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['bed_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['bed_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}

	//for nursing fees
	if (isset($_POST['addm_item'])) {
		foreach ($_POST['addm_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new AdmissionConfiguration();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['addm_capitated_' . explode("|", $idCode)[0]]) && $_POST['addm_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['addm_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['addm_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['addm_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	//for nursing services used in clinical tasks
	if (isset($_POST['nrss_item'])) {
		foreach ($_POST['nrss_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new NursingService();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['nrss_capitated_' . explode("|", $idCode)[0]]) && $_POST['nrss_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['nrss_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['nrss_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['nrss_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	//for ward fees
	if (isset($_POST['wdf_item'])) {
		foreach ($_POST['wdf_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new Ward();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['wdf_capitated_' . explode("|", $idCode)[0]]) && $_POST['wdf_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['wdf_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['wdf_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['wdf_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}

	//for ophthalmology fees
	if (isset($_POST['oph_item'])) {
		foreach ($_POST['oph_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new Ophthalmology();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['oph_capitated_' . explode("|", $idCode)[0]]) && $_POST['oph_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['oph_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['oph_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['oph_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	//for physiotherapy fees
	if (isset($_POST['physio_item'])) {
		foreach ($_POST['physio_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new PhysiotherapyItem();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['physio_capitated_' . explode("|", $idCode)[0]]) && $_POST['physio_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['physio_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['physio_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['physio_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	//for dentology fees
	if (isset($_POST['dent_item'])) {
		foreach ($_POST['dent_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new Dentistry();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['dent_capitated_' . explode("|", $idCode)[0]]) && $_POST['dent_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['dent_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['dent_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['dent_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	//for medical report fees
	if (isset($_POST['exam_item'])) {
		foreach ($_POST['exam_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new MedicalExam();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['exam_capitated_' . explode("|", $idCode)[0]]) && $_POST['exam_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['exam_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['exam_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['exam_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	//for antenatal pkgs fees
	if (isset($_POST['antePkg_item'])) {
		foreach ($_POST['antePkg_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new AntenatalPackages();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['antePkg_capitated_' . explode("|", $idCode)[0]]) && $_POST['antePkg_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['antePkg_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['antePkg_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['antePkg_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	//for genetic labs fees
	if (isset($_POST['pgd_item'])) {
		foreach ($_POST['pgd_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new GeneticLab();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['pgd_capitated_' . explode("|", $idCode)[0]]) && $_POST['pgd_capitated_' . explode("|", $idCode)[0]] == "on" ? TRUE : FALSE);
			$item->setType($_POST['pgd_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['pgd_code_ins_' . explode("|", $idCode)[0]]);

			$item->setSellingPrice($_POST['pgd_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	if (isset($_POST['pkg_item'])) {
		foreach ($_POST['pkg_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new Package();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['pkg_capitated_' . explode("|", $idCode)[0]]) && $_POST['pkg_capitated_' . explode("|", $idCode)[0]] == "on" ? true : false);
			$item->setType($_POST['pkg_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['pkg_code_ins_' . explode("|", $idCode)[0]]);
			
			$item->setSellingPrice($_POST['pkg_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}
	if (isset($_POST['drt_item'])) {
		foreach ($_POST['drt_item'] as $idCode) {
			$item = new InsuranceItemsCost();
			$item->setClinic($scheme->getHospital());
			$item->setInsuranceScheme($scheme);
			$adc = new DRT();
			$adc->setCode(explode("|", $idCode)[1]);
			$item->setItem($adc);
			$item->setCapitated(isset($_POST['drt_capitated_' . explode("|", $idCode)[0]]) && $_POST['drt_capitated_' . explode("|", $idCode)[0]] == "on" ? true : false);
			$item->setType($_POST['drt_type_' . explode("|", $idCode)[0]]);
			$item->setInsuranceCode($_POST['drt_code_ins_' . explode("|", $idCode)[0]]);
			
			$item->setSellingPrice($_POST['drt_ins_' . explode("|", $idCode)[0]]);
			$items[] = $item;
		}
	}

	if ((new InsuranceItemsCostDAO())->addInsuranceItemsCosts($items)) {
		if (sizeof($items) == 0) {
			$result = "error:Sorry, Nothing was added to the current scheme's insured list. Try again";
		} else {
			$result = "success:You have successfully added " . sizeof($items) . " items to the current scheme's insured list";
		}
	} else {
		$result = 'error:Sorry, something went wrong';
	}
	exit(json_encode($result));
}

?>
<link rel="stylesheet" href="/style/insurance_items.css">
<div style="width: 1200px;">
	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" id="addItemsForm">
		<div class="row-fluid filters card" style="width:50%">
			<label class="span10">Filter items by name <input type="text" id="searchTerm"></label>
			<button class="btn span2 no-label" type="button" data-show="<?=$show[0]?>" id="filterItemsInsurance">Search</button>
		</div>
		<div class="dataTables_wrapper" id="contentArea">
			
		<?php if (in_array("CO", $show)) { ?>
			<h3 style="border-top: 4px dotted #D6D6D6; padding-top:30px ">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="con">Consultancy</a></h3>
			<table class="table table-striped" data-block="con">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="con"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="con_billBy" id="con_byPrice" value="byPrice"> Price (<?= $currency ?>)</label></th>
					<th><label><input type="radio" name="con_billBy" id="con_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "CO") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="con_item_<?= $itc->id ?>" name="con_item[]" data-block="con">
							</td>
							<td><label for="con_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="con_code_ins_<?= $itc->id ?>"></td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="con_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="con_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="con_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="con_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="con_capitated_<?= $itc->id ?>"></label></td>
							
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("DR0", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="drug">Drugs</a></h3>
			<table class="table table-striped" data-block="drug">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="drug"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="drug_billBy" id="drug_byPrice" value="byPrice"> Price (<?= $currency ?>)</label></th>
					<th><label><input type="radio" name="drug_billBy" id="drug_byPercent" value="byPercent">(% of Default)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "DR0") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="drug_item_<?= $itc->id ?>" name="drug_item[]" data-block="drug">
							</td>
							<td><label for="drug_item_<?= $itc->id ?>"><?= $itc->item_description ?> [<?= $itc->item_extra_details ?>]</label></td>
							<td><input type="text" name="drug_code_ins_<?= $itc->id ?>"></td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="drug_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="drug_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="drug_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="drug_type_<?= $itc->id ?>" data-data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="drug_capitated_<?= $itc->id ?>"></label></td>
							
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("LA", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="lab">Laboratory</a></h3>
			<table class="table table-striped" data-block="lab">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="lab"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="lab_billBy" id="lab_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="lab_billBy" id="lab_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "LA") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="lab_item_<?= $itc->id ?>" name="lab_item[]" data-block="lab">
							</td>
							<td><label for="lab_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="lab_code_ins_<?= $itc->id ?>"></td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="lab_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="lab_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="lab_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="lab_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="lab_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>

			</table>
		<?php } ?>

		<?php if (in_array("VC", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;border-bottom: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="vac">Vaccination</a></h3>
			<table class="table table-striped" data-block="vac">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="vac"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="vac_billBy" id="vac_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="vac_billBy" id="vac_byPercent" value="byPercent"> Insured Price
							(<a href="javascript:void(0)" title="Apply same rate across all Vaccines">% of Default</a>)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "VC") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="vac_item_<?= $itc->id ?>" name="vac_item[]" data-block="vac">
							</td>
							<td><label for="vac_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="vac_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="vac_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="vac_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="vac_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="vac_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="vac_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("SC", $show)) { ?>

			<h3 style="border-top: 1px solid #D6D6D6;border-bottom: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="img">Imagery</a></h3>
			<table class="table table-striped" data-block="img">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="img"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="img_billBy" id="img_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="img_billBy" id="img_byPercent" value="byPercent"> Insured Price
							(<a href="javascript:void(0)" title="Apply same rate across all imageries">% of Default</a></label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "SC") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="img_item_<?= $itc->id ?>" name="img_item[]" data-block="img">
							</td>
							<td><label for="img_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="img_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="img_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="img_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="img_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="img_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="img_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("PR", $show)) { ?>

			<h3 style="border-top: 1px solid #D6D6D6;border-bottom: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="img">Procedures</a></h3>
			<table class="table table-striped" data-block="pr">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="pr"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="pr_billBy" id="pr_byPrice" value="byPrice"> Price (<?= $currency ?>)</label></th>
					<th><label><input type="radio" name="pr_billBy" id="pr_byPercent" value="byPercent"> Insured Price
							(<a href="javascript:void(0)" title="Apply same rate across all procedures">% of Default</a></label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "PR") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="pr_item_<?= $itc->id ?>" name="pr_item[]" data-block="pr">
							</td>
							<td><label for="pr_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="pr_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="pr_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="pr_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="pr_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="pr_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="pr_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("IT", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="item">Clinical Items</a></h3>
			<table class="table table-striped" data-block="item">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="item"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="item_billBy" id="item_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="item_billBy" id="item_byPercent" value="byPercent"> (% of
							Default)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "IT") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="item_item_<?= $itc->id ?>" name="item_item[]" data-block="item">
							</td>
							<td><label for="item_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="item_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="item_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="item_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="item_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="item_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="item_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("RT", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="bed">Beds/Room Types</a></h3>
			<table class="table table-striped" data-block="bed">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="bed"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="bed_billBy" id="bed_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="bed_billBy" id="bed_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "RT") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="bed_item_<?= $itc->id ?>" name="bed_item[]" data-block="bed">
							</td>
							<td><label for="bed_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="bed_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="bed_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="bed_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="bed_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="bed_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="bed_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("AD", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="addm">Admission Fees</a></h3>
			<table class="table table-striped" data-block="addm">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="addm"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="addm_billBy" id="addm_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="addm_billBy" id="addm_byPercent" value="byPercent"> (% of
							Default)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "AD") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="addm_item_<?= $itc->id ?>" name="addm_item[]" data-block="addm">
							</td>
							<td><label for="addm_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="addm_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="addm_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="addm_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="addm_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="addm_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="addm_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("NS", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="nrss">Nursing Services</a></h3>
			<table class="table table-striped" data-block="nrss">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="nrss"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="nrss_billBy" id="nrss_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="nrss_billBy" id="nrss_byPercent" value="byPercent"> (% of
							Default)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "NS") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="nrss_item_<?= $itc->id ?>" name="nrss_item[]" data-block="nrss">
							</td>
							<td><label for="nrss_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="nrss_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="nrss_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="nrss_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="nrss_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="nrss_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="nrss_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("WR", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="wdf">Ward Fees</a></h3>
			<table class="table table-striped" data-block="wdf">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="wdf"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="wdf_billBy" id="wdf_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="wdf_billBy" id="wdf_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "WR") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td><input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="wdf_item_<?= $itc->id ?>" name="wdf_item[]" data-block="wdf"></td>
							<td><label for="wdf_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="wdf_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="wdf_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="wdf_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="wdf_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="wdf_type_<?= $itc->id ?>" data-data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="wdf_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("OP", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="oph">Ophthalmology Charges</a></h3>
			<table class="table table-striped" data-block="oph">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="oph"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="oph_billBy" id="oph_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="oph_billBy" id="oph_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "OP") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="oph_item_<?= $itc->id ?>" name="oph_item[]" data-block="oph">
							</td>
							<td><label for="oph_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="oph_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="oph_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="oph_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="oph_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="oph_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="oph_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("IP", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="oph">Physiotherapy Charges</a></h3>
			<table class="table table-striped" data-block="oph">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="physio"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="physio_billBy" id="physio_byPrice" value="byPrice"> Price
							(<?= $currency ?>)</label></th>
					<th><label><input type="radio" name="physio_billBy" id="physio_byPercent" value="byPercent"> (% of
							Default)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "IP") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td>
								<input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="physio_item_<?= $itc->id ?>" name="physio_item[]" data-block="physio">
							</td>
							<td><label for="physio_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="physio_code_ins_<?= $itc->id ?>"></td>

							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="physio_def_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="physio_ins_<?= $itc->id ?>">
							</td>
							<td>
								<input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="physio_per_ins_<?= $itc->id ?>">
							</td>
							<td><select aria-controls name="physio_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="physio_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("DT", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="dent">Dentistry Charges</a></h3>
			<table class="table table-striped" data-block="dent">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="dent"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="dent_billBy" id="dent_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="dent_billBy" id="dent_byPercent" value="byPercent"> (% of
							Default)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "DT") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td><input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="dent_item_<?= $itc->id ?>" name="dent_item[]" data-block="dent"></td>
							<td><label for="dent_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="dent_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="dent_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>" name="dent_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100" name="dent_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="dent_type_<?= $itc->id ?>" data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="dent_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("ME", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="exam">Medical Report Charges</a></h3>
			<table class="table table-striped" data-block="exam">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="exam"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="exam_billBy" id="exam_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="exam_billBy" id="exam_byPercent" value="byPercent"> (% of
							Default)</label></th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "ME") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td><input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>" id="exam_item_<?= $itc->id ?>" name="exam_item[]" data-block="exam"></td>
							<td><label for="exam_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="exam_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="exam_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="exam_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100"
							           name="exam_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="exam_type_<?= $itc->id ?>" data-data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="exam_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("AP", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="antePkg">Antenatal Packages</a></h3>
			<table class="table table-striped" data-block="antePkg">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="antePkg" class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="antePkg_billBy" id="antePkg_byPrice" value="byPrice"> Price
							(<?= $currency ?>)</label></th>
					<th><label><input type="radio" name="antePkg_billBy" id="antePkg_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "AP") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td><input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>"
							           id="antePkg_item_<?= $itc->id ?>" name="antePkg_item[]" data-block="antePkg"></td>
							<td><label for="antePkg_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="antePkg_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="antePkg_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="antePkg_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100"
							           name="antePkg_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="antePkg_type_<?= $itc->id ?>" data-data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="antePkg_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>

		<?php if (in_array("PGD", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="pgd">Genetic Lab Tests</a></h3>
			<table class="table table-striped" data-block="pgd">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="pgd"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="pgd_billBy" id="pgd_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="pgd_billBy" id="pgd_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "PGD") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td><input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>"
							           id="pgd_item_<?= $itc->id ?>" name="pgd_item[]" data-block="pgd"></td>
							<td><label for="pgd_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="pgd_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="pgd_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="pgd_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100"
							           name="pgd_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="pgd_type_<?= $itc->id ?>" data-data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="pgd_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>
		
		<?php if (in_array("PKG", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="pkg">Packages/Promos</a></h3>
			<table class="table table-striped" data-block="pkg">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="pkg"  class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="pkg_billBy" id="pkg_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="pkg_billBy" id="pkg_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "PKG") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td><input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>"
							           id="pkg_item_<?= $itc->id ?>" name="pkg_item[]" data-block="pkg"></td>
							<td><label for="pkg_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="pkg_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="pkg_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="pkg_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100"
							           name="pkg_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="pkg_type_<?= $itc->id ?>" data-data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="pkg_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>
			
			<?php if (in_array("DRT", $show)) { ?>
			<h3 style="border-top: 1px solid #D6D6D6;">
				<a href="javascript:void(0)" onclick="showMe(this)" data-block="drt">DRGs</a></h3>
			<table class="table table-striped" data-block="drt">
				<thead>
				<tr>
					<th>SN</th>
					<th><a href="javascript:void(0)" id="drt" class="all" title="Check/Uncheck all">All</a></th>
					<th>Item/Service Name</th>
					<th>Insurance Code</th>
					<th>Default Price</th>
					<th><label><input type="radio" name="drt_billBy" id="drt_byPrice" value="byPrice"> Price (<?= $currency ?>)</label>
					</th>
					<th><label><input type="radio" name="drt_billBy" id="drt_byPercent" value="byPercent"> (% of Default)</label>
					</th>
					<th>Type</th>
					<th>Capitated</th>
				</tr>
				</thead>
				<?php $key = $page * $pageSize;
				foreach ($itemCosts as $itc) {
					if (strpos($itc->item_code, "DRT") !== false) { ?>
						<tr>
							<td><?= (++$key) ?></td>
							<td><input type="checkbox" value="<?= $itc->id ?>|<?= $itc->item_code ?>"
							           id="drt_item_<?= $itc->id ?>" name="drt_item[]" data-block="drt"></td>
							<td><label for="drt_item_<?= $itc->id ?>"><?= $itc->item_description ?></label></td>
							<td><input type="text" name="drt_code_ins_<?= $itc->id ?>"></td>

							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="drt_def_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" value="<?= $itc->selling_price ?>"
							           name="drt_ins_<?= $itc->id ?>"></td>
							<td><input type="number" class="price" readonly="readonly" min="0" max="100" value="100"
							           name="drt_per_ins_<?= $itc->id ?>"></td>
							<td><select aria-controls name="drt_type_<?= $itc->id ?>" data-data-placeholder="Type">
									<option>Primary</option>
									<option>Secondary</option>
								</select></td>
							<td><label><input type="checkbox" name="drt_capitated_<?= $itc->id ?>"></label></td>
						</tr>
					<?php }
				} ?>
			</table>
		<?php } ?>
			
			<div class="itemsPager dataTables_wrapper no-footer">
				<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
				     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
					of <?= ceil($totalSearch / $pageSize) ?>) <span class="fadedText required">* Please we do not process items selected across multiple pages </span>
				</div>

				<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
					<a id="DataTables_Table_1_first" data-page="0"
					   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
						records</a>
					<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
					   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
						records</a>

					<a id="DataTables_Table_1_last"
					   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
					   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_next"
					   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
					   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
				</div>
			</div>
		</div>

		<div>
			<input name="sid" type="hidden" value="<?= $_GET['sid'] ?>">
			<input name="hid" type="hidden" value="<?= @$_GET['hid'] ?>">
			<button name="insurance_save" id="saveItemsButton" type="button" class="btn">Save</button>
			<button name="cancel" type="reset" class="cancelBtn btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	var isAllChecked = false;
	var block;
	$clicker = $('a.all');//.first();
	$clicker.live('click', function (e) {
		if (!e.handled) {
			isAllChecked = true;
			block = $(this).prop('id');
			$.each($("input[type='checkbox'][id*='" + block + "']"), function (i, v) {
				if (!$(this).is(":checked")) {
					isAllChecked = false;
				}
			});

			if (isAllChecked) {
				$("input[type='checkbox'][id*='" + block + "']").prop("checked", false).trigger('change').iCheck('update');
			} else {
				$("input[type='checkbox'][id*='" + block + "']").prop("checked", true).trigger('change').iCheck('update');
			}

			//Reset the value
			$.each($("input[class='price'][name*='" + block + "_def_']"), function (i, v) {
				$("input[class='price'][name='" + block + "_ins_" + $(this).prop("name").split("_")[2] + "']").val($(this).val());
				$("input[class='price'][name='" + block + "_per_ins_" + $(this).prop("name").split("_")[2] + "']").val(100);
			});

			//Mark Both as Readonly
			$("input[class='price'][name*='" + block + "_ins_'],[name*='" + block + "_per_ins_']").prop("readonly", true);

			if ($("#" + block + "_byPrice").is(":checked")) {
				$("input[class='price'][name*='" + block + "_ins_']").prop("readonly", isAllChecked);
			} else {
				$("input[class='price'][name*='" + block + "_per_ins_']").prop("readonly", isAllChecked);
			}
			e.handled = true;
		}
	});

	$(document).ready(function () {
		$("input[type='checkbox'][name*='_item[]']").live('change',function () {
			block = $(this).data("block");
			idd = $(this).prop("id").split("_")[2];
			//Reset the value
			$("input[class='price'][name='" + block + "_ins_" + idd + "']").val($("input[class='price'][name='" + block + "_def_" + idd + "']").val());
			$("input[class='price'][name='" + block + "_per_ins_" + idd + "']").val(100);

			//Mark Both as Readonly
			$("input[class='price'][name='" + block + "_ins_" + idd + "'],[name='" + block + "_per_ins_" + idd + "']").prop("readonly", true);

			if ($(this).is(":checked")) {
				if ($("#" + block + "_byPrice").is(":checked")) {
					$("input[class='price'][name='" + block + "_ins_" + idd + "']").prop("readonly", false);
				} else {
					$("input[class='price'][name='" + block + "_per_ins_" + idd + "']").prop("readonly", false);
					$("input[type='text'][name='" + block + "_code_ins_']").prop("readonly", false);
				}
			}
		});

		/**
		 * Onclick of either bill by price/percentage
		 * Reset and mark as readonly all the price/percentage price for the block where the click is coming from
		 * Fianlly remove readonly for the intended click say price or percentage iff it is selected
		 */
		$("input[type='radio'][name*='billBy']").live('change',function () {
			block = $(this).prop("id").split("_")[0];
			//Mark Both as Readonly
			$("input[class='price'][name*='" + block + "_ins_'],[name*='" + block + "_per_ins_']").prop("readonly", true);

			//Reset the value
			$.each($("input[class='price'][name*='" + block + "_def_']"), function (i, v) {
				$("input[class='price'][name='" + block + "_ins_" + $(this).prop("name").split("_")[2] + "']").val($(this).val());
				$("input[class='price'][name='" + block + "_per_ins_" + $(this).prop("name").split("_")[2] + "']").val(100);
			});

			if ($(this).val() === "byPrice") {
				$.each($("input[class='price'][name*='" + block + "_def_']"), function (i, v) {
					idd = $(this).prop("name").split("_")[2];
					if ($("input[type='checkbox'][id='" + block + "_item_" + idd + "']").is(":checked")) {
						$("input[class='price'][name='" + block + "_ins_" + idd + "']").prop("readonly", false);
						$("input[class='price'][name='" + block + "_code_ins_" + idd + "']").prop("readonly", false);
					}
					$("input[class='price'][name='" + block + "_ins_" + idd + "']").val($(this).val());
				});
			} else {
				$.each($("input[class='price'][name*='" + block + "_def_']"), function (i, v) {
					idd = $(this).prop("name").split("_")[2];
					if ($("input[type='checkbox'][id='" + block + "_item_" + idd + "']").is(":checked")) {
						$("input[class='price'][name='" + block + "_per_ins_" + idd + "']").prop("readonly", false);
						$("input[class='price'][name='" + block + "_code_ins_" + idd + "']").prop("readonly", false);
					}
					$("input[class='price'][name='" + block + "_per_ins_" + idd + "']").val(100);
				});
			}
		});

		$("input[class='price'][name*='_per_ins_']").change(function () {
			block = $(this).prop("name").split("_")[0];
			idd = $(this).prop("name").split("_")[3];
			$("input[class='price'][name='" + block + "_ins_" + idd + "']").val((parseFloat($(this).val()) * parseFloat($("input[class='price'][name='" + block + "_def_" + idd + "']").val())) / 100);
		});

		//$('table[class="table table-striped"]').hide("slow");
		$("input[type='radio'][value='byPrice']").prop("checked", true).iCheck('update');

		$("#saveItemsButton").click(function () {
			var $Form = $("#addItemsForm");
			$.ajax({
				url: $Form.prop("action"),
				type: 'post',
				data: $Form.serialize(),
				dataType: 'json',
				beforeSend: function () {
				},
				success: function (d) {
					if (d.split(":")[0] === "success") {
						Boxy.get($(".close")).hideAndUnload();
						Boxy.info(d.split(":")[1], function () {
							//simulates a reload of the parent window
							Boxy.get($(".close")).hideAndUnload();
							Boxy.load('/pm/insurance/boxy.editscheme_items.php?sid=<?=$_REQUEST['sid']?>');
						});

					} else {
						Boxy.alert(d.split(":")[1]);
					}
				}
			});
		});

		$('input:checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		}).on('ifClick', function (event) {
			$(event.currentTarget).trigger('click');
		});
		
		$('input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		}).on('ifClick', function (event) {
			$(event.currentTarget).trigger('click');
		});

		$(document).on('click', '.itemsPager.dataTables_wrapper a.paginate_button', function (e) {
			if (!e.clicked) {
				var page = $(this).data("page");
				if (!$(this).hasClass("disabled")) {
					var searchTerm = $('#searchTerm').val();
					
					var postData = {'page': page, 'show': $('#filterItemsInsurance').data('show')};
					if(searchTerm.trim().length !== 0) {
						postData['filter'] = searchTerm;
					}
					$.post('/pm/insurance/boxy_addInsuredItem.php?sid=<?= $_GET['sid']?>', postData, function (response) {
						$('#contentArea').html($(response).find('#contentArea').html());
						initChecks();
					});
				}
				e.clicked = true;
			}
		});
		$(document).on('click', '#filterItemsInsurance', function (e) {
			var show = $(this).data('show');
			if (!e.clicked) {
				var searchTerm = $('#searchTerm').val();
				if(searchTerm.trim().length !== 0) {
					var page = 0;
					$.post('/pm/insurance/boxy_addInsuredItem.php?sid=<?= $_GET['sid']?>', {
						'page': page,
						'filter': searchTerm,
						'show': show
					}, function (response) {
						$('#contentArea').html($(response).find('#contentArea').html());
						initChecks();
					});
				} else {
					$.post('/pm/insurance/boxy_addInsuredItem.php?sid=<?= $_GET['sid']?>', {'page': 0,'show': show}, function (response) {
						$('#contentArea').html($(response).find('#contentArea').html());
						initChecks();
					});
				}
				e.clicked = true;
			}
		});
		
	});

	function showMe(ele) {
		$('table[class="table table-striped"]').hide("fast");
		$('table[class="table table-striped"][data-block="' + $(ele).data("block") + '"]').show("fast");
	}
	
	function initChecks(){
		$("input[type='radio'][value='byPrice']").prop("checked", true).iCheck('update');
		$('#contentArea input:checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		}).on('ifClick', function (event) {
			$(event.currentTarget).trigger('click');
		});

		$('#contentArea input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		}).on('ifClick', function (event) {
			$(event.currentTarget).trigger('click');
		});
	}
</script>