<?php

//include($_SERVER['DOCUMENT_ROOT'].'/pages/pm/insurance/boxy.editscheme.php');

$sid = $_GET['sid']; //Retrieve the id of this insurance scheme
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Insurer.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/InsuranceScheme.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Badge.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Procedure.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsurerDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceSchemeDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceItemsCostDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/InsuranceTypeDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BadgeDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.config.main.php";

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/ERPHandler.php";

$providers = (new InsurerDAO())->getInsurers();
$schemeDAO = new InsuranceSchemeDAO();
$scheme = $schemeDAO->get($sid);
$dao = new InsuranceItemsCostDAO();
$itemCosts = $dao->getInsuredItemsCostsByScheme($sid);
$badges = (new BadgeDAO())->all();
if (isset($_POST['updateScheme'])) {
	$scheme = (new InsuranceSchemeDAO())->get($sid, false);
	if (!is_blank($_POST['schemename'])) {
		$scheme->setName($_POST['schemename']);
	} else {
		exit("error:Name is required");
	}
	if (!is_blank($_POST['provider'])) {
		$scheme->setInsurer(new Insurer($_POST['provider']));
	} else {
		exit("error:Payer is required");
	}
	
	if (!is_blank($_POST['pay_type'])) {
		$scheme->setType($_POST['pay_type']);
	} else {
		exit("error:Pay type is missing");
	}
	
	if (!is_blank($_POST['insurance_type_id'])) {
		$scheme->setInsuranceType((new InsuranceTypeDAO())->get($_POST['insurance_type_id']));
	}/*else {
        exit("error:Insurance type is missing");
    }*/
	
	if (!is_blank($_POST['badge_id'])) {
		$scheme->setBadge(new Badge($_POST['badge_id']));
	} else {
		$scheme->setBadge(null);
	}
	
	if (!is_blank(@$_POST['reg_company'])) {
		$scheme->setCompanyRegCost(parseNumber($_POST['reg_company']));
	} else {
		//exit("error:Company Registration charge is missing");
	}
	
	if (!is_blank($_POST['reg_individual'])) {
		$scheme->setIndividualRegCost(parseNumber($_POST['reg_individual']));
	} else {
		exit("error:Individual Registration charge is missing");
	}
	
	if (!is_blank($_POST['credit_limit'])) {
		$scheme->setCreditLimit(parseNumber($_POST['credit_limit']));
	} else {
		exit("error:Credit Limit is required");
	}
	
	$emails = array_filter(explode(",", $_POST['email']));
	if (count($emails) == 0) {
		exit('error:At least one email address is required ');
	}
	$scheme->setEmail($emails);
	
	
	if (!is_blank($_POST['phone'])) {
		$scheme->setPhone($_POST['phone']);
	} else {
		exit("error:Contact Phone is required");
	}
	if (!is_blank($_POST['logo_url'])) {
		$scheme->setLogoUrl($_POST['logo_url']);
	} else {
		$scheme->setLogoUrl((isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/img/logo/logo.jpg");
	}
	
	if (!is_blank($_POST['clinical_services_rate'])) {
		$scheme->setClinicalServicesRate(parseNumber($_POST['clinical_services_rate']));
	} else {
		$scheme->setClinicalServicesRate(0);
	}
	
	if(!is_blank($_POST['enrolees_max'])){
		$scheme->setEnroleesMax(parseNumber($_POST['enrolees_max']));
	} else {
		exit("error:Enrollees allowable maximum is required");
	}
	$scheme->setIsReference(isset($_POST['is_reference']));
	
	if (MainConfig::$erpEnabled) {
		if (is_blank($_POST['receivable_account_id'])) {
			exit("error:Partner's Receivable account is required");
		}
		if (is_blank($_POST['discount_account_id'])) {
			exit("error:Partner's Discount account is required");
		}
		if (is_blank($_POST['partner_id'])) {
			exit("error:Odoo Related Partner account is required");
		}
		$scheme->setReceivablesAccount($_POST['receivable_account_id']);
		$scheme->setDiscountAccount($_POST['discount_account_id']);
		$scheme->setPartner($_POST['partner_id']);
	}
	
	error_log("objssssss".json_encode($scheme));
	
	$chk = $schemeDAO->updateInsuranceScheme($scheme);
	
	if ($chk) {
		$ret = 'success:Insurance Scheme Updated';
	} else {
		$ret = "error:An error occurred";
	}
	exit($ret);
}
?>
<div id="addprofile" style="min-width: 1000px;width: 1000px; max-width:1000px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete': donE});">
		<label>
			<span id="disp"></span>
		</label>
		<label>
			Scheme Name:<input name="schemename" type="text" value="<?= $scheme->getName() ?>">
		</label>
		<label>
			Payer:
			<select name="provider">
				<option></option>
				<?php
				foreach ($providers as $prov) {
					echo '<option value="' . $prov->getId() . '" ' . (($prov->getId() == $scheme->getInsurer()->getId()) ? " selected='selected'" : "") . ' >' . $prov->getName() . '</option>';
				} ?>
			</select>
		</label>
		<label>Billing Type
			<select name="pay_type">
				<option value="self"<?= ($scheme->getType() == 'self' ? ' selected="selected"' : '') ?>>SELF PAY</option>
				<option value="insurance"<?= ($scheme->getType() != 'self' ? ' selected="selected"' : '') ?>>INSURANCE</option>
			</select>
		</label>
		<label>Insurance Type
			<select name="insurance_type_id">
				<?php foreach ((new InsuranceTypeDAO())->all() as $type) { ?>
					<option <?= ($scheme->getInsuranceType() != null && $scheme->getInsuranceType()->getId() == $type->getId()) ? 'selected' : '' ?> value="<?= $type->getId() ?>"><?= $type->getName() ?></option><?php } ?>
			</select></label>
		<label>
			Badge <span class="pull-right" id="badge_icon"></span><select name="badge_id" placeholder=" - Select badge -">
				<option></option>
				<?php foreach ($badges as $badge) { ?>
					<option <?= ($scheme->getBadge() !== null && $badge->getId() == $scheme->getBadge()->getId() ? ' selected="selected"' : '') ?> value="<?= $badge->getId() ?>" data-badge="<?= $badge->getIcon() ?>"><?= $badge->getName() ?></option>
				<?php }
				?>
			</select>
		</label>

		<label class="fees">Individual Registration Fee <span class="pull-right"><i class="icon-info-sign"></i> Help</span>
			<input type="number" name="reg_individual" min="0" value="<?= $scheme->getIndividualRegCost() ?>"> </label>
		<label class="fees">Company Registration Fee <span class="pull-right"><i class="icon-info-sign"></i> Help</span>
			<input type="number" disabled name="reg_company" min="0" value="<?= $scheme->getCompanyRegCost() ?>"> </label>

		<label>Credit Limit <input name="credit_limit" type="number" min="0" value="<?= $scheme->getCreditLimit() ?>">
		</label>

		<div class="row-fluid">
			<label class="span6">Contact Phone <input type="text" name="phone" required value="<?= $scheme->getPhone() ?>">
			</label>
			<label class="span6">Contact Email <input type="text" name="email" id="email" multiple required value="<?= is_array($scheme->getEmail()) ? explode(',', $scheme->getEmail()) : $scheme->getEmail()  ?>">
			</label>
		</div>

		<label>Logo: (paste URL of a hosted image resource)
			<textarea name="logo_url"><?= $scheme->getLogoUrl() ?></textarea></label>
		<label>Clinical Service Charge Rate (%)<input type="number" value="<?= $scheme->getClinicalServicesRate() ?>" min="0" max="100" name="clinical_services_rate"> </label>
		<label>Maximum # of enrollees allowable <input type="number" data-decimals="0" required name="enrolees_max" value="<?=$scheme->getEnroleesMax() ?>"> </label>
		<label><input type="checkbox" name="is_reference" <?= $scheme->isReference() ? 'checked':'' ?>> Check this box if you want this scheme to serve as a reference scheme (Prices can be copied from this scheme to other non-reference schemes) </label>
		<?php if (MainConfig::$erpEnabled) { ?>
			<label>Receivables Account
				<select name="receivable_account_id">
					<?php
					$accounts = (new ERPHandler())->search("account.account", ["id", "name", "code"], ["name ilike %"]);
					if ($accounts !== null) {
						foreach ($accounts as $res) {
							$res = (object)json_decode(json_encode($res)); ?>
							<option <?= ((int)$scheme->getReceivablesAccount() === $res->me->struct->id->me->int ? 'selected="selected"' : '') ?> value="<?= $res->me->struct->id->me->int ?>">
								[<?= $res->me->struct->code->me->string ?>] <?= $res->me->struct->name->me->string ?></option>
						<?php }
					} ?>
				</select>
			</label>
			<label>Discount Account
				<select name="discount_account_id">
					<?php
					$accounts = (new ERPHandler())->search("account.account", ["id", "name", "code"], ["name ilike %"]);
					if ($accounts !== null) {
						foreach ($accounts as $res) {
							$res = (object)json_decode(json_encode($res)); ?>
							<option <?= ((int)$scheme->getDiscountAccount() === $res->me->struct->id->me->int ? 'selected="selected"' : '') ?> value="<?= $res->me->struct->id->me->int ?>">
								[<?= $res->me->struct->code->me->string ?>] <?= $res->me->struct->name->me->string ?></option>
						<?php }
					} ?>
				</select>
			</label>
			<label>Partner
			<select name="partner_id">
				<?php
				$partners = (new ERPHandler())->search("res.partner", ["id", "name", "email", "street", "city", "zip"], ["name ilike %"]);
				if ($partners !== null) {
					foreach ($partners as $res) {
						$res = (object)json_decode(json_encode($res)); ?>
						<option <?= ((int)$scheme->getPartner() == $res->me->struct->id->me->int ? 'selected="selected"' : '') ?> value="<?= $res->me->struct->id->me->int ?>"><?= $res->me->struct->name->me->string ?></option>
					<?php }
				} ?>
			</select>
			</label><?php } ?>
		<div class="btn-block">
			<button type="submit" class="btn" name="updateScheme">Update Scheme &raquo;</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>

	</form>

	<h5><a id="is" href="javascript:void(0)">Show/Edit Insured Services</a></h5>
</div>
<link rel="stylesheet" href="/style/insurance_items.css">
<script type="text/javascript">
	$(document).ready(function () {
		var item;
		$("#is").on('click', function (e) {
			if (!e.handled) {
				Boxy.get($('.close')).hideAndUnload();
				Boxy.load('/pm/insurance/boxy.editscheme_items.php?sid=<?= $_GET['sid']?>', {
					title: 'Items Covered', afterShow: function () {
						Boxy.get($(".close")).centerX();
					}
				});
				e.handled = true;
			}
		});
		$('select[name="badge_id"]').select2().change(function (data) {
			if (data.added !== undefined) {
				var content = $(data.added.element).data("badge");
				$('#badge_icon').html(content);
			} else {
				$('#badge_icon').html('');
			}
		}).trigger('change');
	});

	function donE(s) {
		var status_ = s.split(":");
		if (status_[0] === 'success') {
			$('#disp').html('<span class="alert alert-info">' + status_[1] + '</span>');
			showTabs(2);
			Boxy.get($('.close')).hideAndUnload();
		}
		else {
			$('#disp').html('<span class="alert alert-error">' + status_[1] + '</span>');
			$(".boxy-content").animate({scrollTop: 0}, "slow");
		}
	}

	$('#email').click(function (e) {
		if (!e.handled) {
			Boxy.load('/pages/pm/insurance/add_email.php');
			e.handled = true;
		}
	});

	$('[id^=_delEmailLnk_]').live('click', function (e) {
		if (!e.handled) {
			$('#email_').val('');
			$('[name="email_"]').val('');
			$('#displayPanel').empty();
			e.handled = true;
		}
	});
</script>