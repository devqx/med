<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/28/16
 * Time: 4:17 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Procedure.php';
$itc = (new InsuranceItemsCostDAO())->getInsuredItemCostsByScheme($_GET['id']);
$_ = new Procedure();
$desc = $_::$desc;

$category = (new BillSourceDAO())->getBillSource($itc->item_group_category_id);

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	$sellingPrice = in_array($itc->item_group_category_id, [8]) ? $desc[0] : 'Selling Price';
	$theatrePrice = in_array($itc->item_group_category_id, [8]) ? $desc[3] : 'Theatre Price';
	$anaesthesiaPrice = in_array($itc->item_group_category_id, [8]) ? $desc[2] : 'Anaesthesia Price';
	$surgeonPrice = in_array($itc->item_group_category_id, [8]) ? $desc[1] : 'Surgeon Price';
	
	if (isset($_POST['selling_price']) && is_blank($_POST['selling_price'])) {
		exit("error:Input required for $sellingPrice");
	}
	if (isset($_POST['followUpPrice']) && is_blank($_POST['followUpPrice'])) {
		exit('error:Input required for followUp Price');
	}
	if (isset($_POST['theatrePrice']) && is_blank($_POST['theatrePrice'])) {
		exit("error:Input required for $theatrePrice");
	}
	if (isset($_POST['theatrePrice']) && is_blank($_POST['anaesthesiaPrice'])) {
		exit("error:Input required for $anaesthesiaPrice");
	}
	if (isset($_POST['surgeonPrice']) && is_blank($_POST['surgeonPrice'])) {
		exit("error:Input required for $surgeonPrice");
	}
	if (is_blank($_POST['insurance_code'])) {
		//exit("error:Input required for Insurance Code");
	}
	if (is_blank($_POST['co_pay'])) {
		exit("error:Input required for CoPay");
	}
	
	$item = $dao = new InsuranceItemsCostDAO();
	$price['selling_price'] = parseNumber($_POST['selling_price']);
	$price['followUp'] = 0;
	$price['theatre'] = 0;
	$price['anaesthesia'] = 0;
	$price['surgeon'] = 0;
	$type = $_POST['coverage_type'];
	$capitated = isset($_POST['capitated']) && $_POST['capitated'] == 'on' ? true : false;
	$price['followUp'] = isset($_POST['followUpPrice']) ? parseNumber($_POST['followUpPrice']) : 0;
	
	$price['theatre'] = isset($_POST['theatrePrice']) ? parseNumber($_POST['theatrePrice']) : 0;
	$price['anaesthesia'] = isset($_POST['anaesthesiaPrice']) ?  parseNumber($_POST['anaesthesiaPrice']):0;
	$price['surgeon'] = isset($_POST['surgeonPrice']) ? parseNumber($_POST['surgeonPrice']):0;
	
	$obj = new stdClass();
	$obj->co_pay = !is_blank($_POST['co_pay']) ? parseNumber($_POST['co_pay']) : 0;
	$obj->insurance_code = !is_blank($_POST['insurance_code']) ? $_POST['insurance_code']: null;
	$obj->price = $price;
	$obj->type = $type;
	$obj->capitated = $capitated;
	
	if ($dao->updateInsuranceItemCostById($_POST['id'], $obj)) {
		exit("success:Changes committed successfully");
	} else {
		exit("error:Something went wrong. We are unable to save your changes");
	}
}
?>
<section style="width: 650px;">
	<div class="alert-box notice">You are editing the price for &laquo; <?= $itc->item_description ?> <?= $itc->item_extra_details ? ' (' . $itc->item_extra_details . ')' : '' ?> &raquo;</div>
	<div class="alert-box notice">Category &laquo; <?= ucwords(str_replace('_',' ', $category->getName())) ?> &raquo;</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: _sdifw, onComplete: __sicms})">
		<input type="hidden" name="id" value="<?= $itc->id ?>">
		<?php if (in_array($itc->item_group_category_id, [3])) { ?>
			<label>Default Price <input type="number" value="<?= $itc->default_price ?>" readonly="readonly" placeholder="Default Price"> </label>
			<div class="row-fluid">
				<label class="span6">Selling Price <input name="selling_price" type="number" value="<?= $itc->selling_price ?>"></label>
				<label class="span6">Follow-Up price: <input name="followUpPrice" type="number" value="<?= $itc->followUpPrice ?>"></label>
			</div>
		
		<?php } else if (in_array($itc->item_group_category_id, [8])) { ?>
			<div class="row-fluid">
				<label class="span6"><?= $desc[0] ?><input type="number" name="selling_price" value="<?= $itc->selling_price ?>"></label>
				<label class="span6"><?= $desc[3] ?><input type="number" name="theatrePrice" value="<?= $itc->theatrePrice ?>"></label>
			</div>
			<div class="row-fluid">
				<label class="span6"><?= $desc[2] ?><input type="number" name="anaesthesiaPrice" value="<?= $itc->anaesthesiaPrice ?>"></label>
				<label class="span6"><?= $desc[1] ?><input type="number" name="surgeonPrice" value="<?= $itc->surgeonPrice ?>"></label>
			</div>
		
		<?php } else if (!in_array($itc->item_group_category_id, [3, 8])) { ?>
			<label>Default Price <input type="number" value="<?= $itc->default_price ?>" readonly="readonly" placeholder="Default Price"> </label>
			<label>Selling Price <input name="selling_price" type="number" value="<?= $itc->selling_price ?>"></label>
		<?php } ?>
		<label><input name="capitated" type="checkbox" <?= ($itc->capitated == 1) ? ' checked' : '' ?>> Capitated</label>
		<label>Service Coverage Type <select name="coverage_type">
				<option value="primary"<?= $itc->type == "primary" ? ' selected' : '' ?>>Primary</option>
				<option value="secondary"<?= $itc->type == "secondary" ? ' selected' : '' ?>>Secondary</option>
			</select></label>
		<label>CoPay <input type="number" name="co_pay" value="<?=$itc->co_pay?>"> </label>
		<label>Insurance (HMIS) Code <input type="text" name="insurance_code" value="<?=$itc->insurance_code?$itc->insurance_code:''?>"> </label>

		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close</button>
		</div>
	</form>
	<script type="text/javascript">
		var _sdifw = function () {
			$(document).trigger('ajaxSend');
		};

		var __sicms = function (s) {
			$(document).trigger('ajaxStop');
			var data = s.split(':');
			if (data[0] == 'error') {
				Boxy.warn(data[1]);
			} else if (data[0] == 'success') {
				Boxy.info(data[1], function () {
					Boxy.get($('.close')).hideAndUnload();
				})
			}
		}
	</script>
</section>
