<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
if (!$this_user->hasRole($protect->records))
	exit ($protect->ACCESS_DENIED);
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/state_lga.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/lifeStyles.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/clinics.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/socioEconomics.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_staff.php';
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/insuranceSchemes.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/KinRelationDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Title.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CoverageType.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Country.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CompanyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$contactTypes = getTypeOptions('type', 'contact');
$companies = (new CompanyDAO())->all();
$countries = (new Country())->all();
$pat = (new PatientDemographDAO())->getPatient($_REQUEST['id'], true);
$contacts = $pat->getContacts();
$industries = (new IndustryDAO())->all();

$referrals = (new ReferralDAO())->all($page=0, $pageSize=10000);
$referral_companies = (new ReferralCompanyDAO())->all($page=0, $pagesize=10000);

$_contacts = [];
foreach ($contacts as $contact) {
	if ($contact->getPrimary()) {
		$_['primary'] = 'on';
	}
	$_['country_id'] = $contact->getCountry()->id;
	$_['phone'] = $contact->getPhone();
	$_['type'] = $contact->getType();
	$_contacts[] = $_;
	unset($_);
}


$existingTitles = [];
foreach (explode("|", $pat->getTitle()) as $t_) {
	$titles__['id'] = $t_;
	$titles__['text'] = $t_;
	$existingTitles[] = $titles__;
	unset($titles__);
}
$existingTitles = count($existingTitles)==0 ? null : $existingTitles;

$kins = (new KinRelationDAO())->all();
if ($pat !== null) {
	if (!isset($_SESSION)) {
		@session_start();
	}
	$_SESSION['patientID'] = $pat->getId();
	$_SESSION['patient_sex'] = $pat->getSex(); ?>
	<img src="/img/loading.gif" width="0" height="0"/>
	<link rel="stylesheet" href="/assets/css/camStyles.css">
	<script src="/assets/webcam/webcam.js"></script>
	<script src="/assets/js/camScript.js"></script>
	<script type="text/javascript">
		state = "<?= $pat->getState()->getId() ?>";
		lga = "<?= $pat->getLga()->getId()?>";
		lga_res = "<?= $pat->getResLga()->getId()?>";
		h_lga = "<?= $pat->getBaseClinic()->getLga()->getId()?>";
		hid = "<?= $pat->getBaseClinic()->getId() ?>";
		insSchemeId = "<?= $pat->getScheme()->getId() ?>";
		insExpDate = "<?= $pat->getInsurance()->getExpirationDate() ?>";
		states =<?= json_encode($states, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
		clinics =<?= json_encode($clinics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
		var countries = <?=json_encode($countries, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
		var referals = <?= json_encode($referrals->data, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
		var refCompanies = <?= json_encode($referral_companies->data, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
	</script>
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/inc.webcam.php'; ?>
	<div id="camera_b">
		<a id="showSnap" href="javascript:void(0)" title="Capture New Image"><span class="camTop_b"></span></a>
		<div id="screen_b">
			<img class="aligncenter" id="passport" src="<?= $pat->getPassportPath() ?>?<?= rand() ?>" width="160" height="160" style="margin-top:22px">
		</div>
	</div>
	<div><span id="output"></span></div>

	<form class="" method="post" action="/edit_patient_profile.php" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
		<input name="pid" type="hidden" value="<?= $pat->getId() ?>">
		<input name="id" type="hidden" value="<?= $pat->getId() ?>">
		<h3>Patient Info</h3>
		<div class="row-fluid">
			<label class="sub span8">Legacy Patient
				#:<input type="text" name="legacy_num" value="<?= $pat->getLegacyId() ?>"></label>
			<label class="sub span4">
				Title
				<select id="_title" multiple data-placeholder="Title/Salutation">
					<option></option>
					<?php
					$TITLES = explode(",", $pat->getTitle());
					foreach (Title::$titles as $title) { ?>
						<option<?php foreach ($TITLES as $_) {
							if ($title == $_) {
								echo ' selected';
							}
						} ?>><?= $title ?></option>
						<?php unset($title);
					} ?>
				</select>
			</label>
			<input type="hidden" name="title" value="<?= $pat->getTitle() ?>">
		</div>
		<div class="row-fluid">
			<label class="sub span4">First
				Name<input type="text" name="fname" id="fname" value="<?= $pat->getFname() ?>"/></label>
			<label class="sub span4">Middle
				Name<input type="text" name="mname" id="mname" value="<?= $pat->getMname() ?>"></label>
			<label class="sub span4">Last
				Name<input type="text" name="lname" id="lname" value="<?= $pat->getLname() ?>"/></label>
		</div>
		<label class="sub"></label>
		<div class="row-fluid">
			<label class="sub span10">Date of Birth
				<span class="required-text">*</span><input type="text" name="dob" id="dob" value="<?= $pat->getDateOfBirth() ? DateTime::createFromFormat('Y-m-d', $pat->getDateOfBirth())->format('d/m/Y') : "" ?>" readonly="readonly" placeholder="Click/touch to select a date of birth"></label>
			<label class="sub span2 no-label"><input type="checkbox" class="checkbox" name="estimated_dob"<?= $pat->getDobEstimated() ? ' checked' : '' ?>>
				Date estimated</label>
		</div>

		<div class="row-fluid">
			<label class="span3">Sex<select name="sex" id="sex">
					<option value="--"<?php if ($pat->getSex() == null) { ?> selected<?php } ?>>
						Select
					</option>
					<option value="male" <?php if ($pat->getSex() == 'male'){ ?>selected<?php } ?>>
						Male
					</option>
					<option value="female" <?php if ($pat->getSex() == 'female'){ ?>selected<?php } ?>>
						Female
					</option>
				</select></label>
			<label class="sub span3">Email
				<input type="text" name="email" id="email" value="<?= $pat->getEmail() ?>"></label>
			<!--<label class="sub span3">Phone Number
				<input type="text" name="phonen" id="phonen" value="<?= $pat->getPhoneNumber() ?>"></label>
			<label class="sub span3">International/Other
				Number<input type="text" name="patIntNumber" id="patIntNumber" value="<?= $pat->getForeignNumber() ?>"></label>-->
			<label class="span6">Phone Numbers
				<span class="pull-right fadedText">
				<a href="javascript:" id="_delNumLnk">Reset</a> |
				<a href="javascript:" id="_addNumLnk">Add Phone Number</a>
			</span>
				<input type="text" name="phonen_" readonly id="phonen">
				<input type="hidden" name="phonen" value="<?= htmlentities(json_encode($_contacts)) ?>" id="multiple_phone">
			</label>
		</div>
        <label>Ethnic Origin
            <input type="text" name="ethnic" placeholder="Enter Ethnic of Orgin" value="<?= $pat->getEthnic() ?>">
        </label>
        <label>Spoken Language
            <select name="language_id" data-placeholder="Patient's spoken language">
                <option></option>
                <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SpokenLanguageDAO.php';
                $language = (new SpokenLanguageDAO())->all();
                foreach ($language as $l) { ?>
                    <option value="<?= $l->getId() ?>"<?= $pat->getSpokenLang() && $pat->getSpokenLang()->getId() == $l->getId() ? ' selected':'' ?>><?= $l->getName() ?></option>
                <?php } ?>
            </select> </label>

		<h5>Address of origin</h5>
		<label>Nationality <select name="nationality">
				<?php
				foreach ((new Country())->all() as $country) { ?>
					<option value="<?= $country->id ?>"<?= ($pat->getNationality()->id == $country->id ? ' selected="selected"' : '') ?>><?= $country->country_name ?></option>
					<?php unset($country);
				} ?>
			</select></label>
		<label>Occupation
			<input type="text" name="occupation" value="<?= $pat->getOccupation() ?>">
		</label>
		<label>Work Address
			<textarea name="work_address"><?= $pat->getWorkAddress() ?></textarea>
		</label>
		<label>Industry
			<select name="industry_id" data-placeholder="--Work Industry--">
				<option></option>
				<?php foreach ($industries as $industry){?>
					<option value="<?=$industry->getId()?>"<?= $pat->getIndustry() && $pat->getIndustry()->getId() == $industry->getId() ? ' selected':'' ?>><?= $industry->getName()?></option>
				<?php }?>
			</select>
		</label>
		<label>Religion <select name="religion_id" data-placeholder="Patient's religion">
				<option></option>
				<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReligionDAO.php';
				$religions = (new ReligionDAO())->getReligions();
				foreach ($religions as $r) {
					echo '<option value="' . $r->getId() . '"' . ($pat->getReligion() != null && $pat->getReligion()->getId() == $r->getId() ? ' selected="selected"' : '') . '>' . $r->getName() . '</option>';
					unset($r);
				}
				?>
			</select> </label>
		<div class="row-fluid">
			<label class="sub span6">State <select name="state" id="state">
					<option value="0">-- select state of origin --</option>
					<?php
					foreach ($states as $st) {
						echo("<option value='" . $st->getId() . "' " . (($pat->getState()->getId() == $st->getId()) ? "selected" : "") . " >" . $st->getName() . "</option>");
						unset($st);
					}
					?></select></label>

			<label class="sub span6">Local Government Area
				<select name="lga" id="lga">
					<option value="0">-- select LGA --</option>
					<?php
					$index = getStateIndex($states, $pat->getLga()->getId());
					$lgas = $index == -1 ? [] : $states[$index]->getLgas();
					foreach ($lgas as $lg) {
						echo("<option value='" . $lg->getId() . "' " . (($pat->getLga()->getId() == $lg->getId()) ? " selected" : "") . " >" . $lg->getName() . "</option>");
						unset($lg);
					}
					?></select></label>

		</div>

		<h5>Address of residence</h5>
		<div class="row-fluid">
			<label class="sub span4">State <select name="state_r" id="state_r">
					<option value="0">-- select state of origin --</option>
					<?php
					foreach ($states as $st) {
						echo("<option value='" . $st->getId() . "' " . (($pat->getResState()->getId() == $st->getId()) ? "selected" : "") . " >" . $st->getName() . "</option>");
						unset($st);
					}
					?></select></label>
			<label class="sub span4">Local Government Area
				<select name="lga_r" id="lga_r">
					<option value="0">-- select LGA --</option>
					<?php
					$index = getStateIndex($states, $pat->getResLga()->getId());
					$lgas = $index == -1 ? [] : $states[$index]->getLgas();
					foreach ($lgas as $lg) {
						echo("<option value='" . $lg->getId() . "' " . (($pat->getResLga()->getId() == $lg->getId()) ? " selected" : "") . " >" . $lg->getName() . "</option>");
						unset($lg);
					}
					?></select></label>
			<label class="sub span4">
				District
				<input type="hidden" name="district_res_id" value="<?= $pat->getDistrictRes() ? $pat->getDistrictRes()->getId() : '' ?>" placeholder="-- Select district--">
			</label>
		</div>

		<label class="sub">Address<input type="text" name="address" id="address" value="<?= $pat->getAddress() ?>"></label>

		<div class="row-fluid">
			<label class="sub span6">Blood Group
				<select name="bloodg" id="bloodg">
					<?php $bloodGroups = PatientDemograph::$bloodGroups;
					foreach ($bloodGroups as $bloodGroup) {
						echo '<option' . (strtolower($bloodGroup) == strtolower($pat->getBloodgroup()) ? ' selected="selected"' : '') . '>' . $bloodGroup . '</option>';
						unset($bloodGroup);
					}
					?>
				</select></label>
			<label class="sub span6">GenoType
				<select name="bloodt" id="bloodt">
					<?php
					require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BloodTypeDAO.php';
					$bloodTypes = (new BloodTypeDAO())->all();
					foreach ($bloodTypes as $bt) {
						echo '<option' . (strtolower($bt->getName()) == strtolower($pat->getBloodType()) ? ' selected="selected"' : '') . '>' . $bt->getName() . '</option>';
						unset($bt);
					} ?>
				</select>

			</label>
		</div>

		<h3>Current HealthCare Center</h3>
		<label class="sub">Facility
			<select id="currentHealthCenter" name="hospital">
				<option value="0">-- select health care center --</option>
				<?php
				foreach ($clinics as $cl) {
					echo('<option value="' . $cl->getId() . '"' . (($pat->getBaseClinic()->getId() == $cl->getId() || $staff->getClinic()->getId() == $cl->getId()) ? " selected" : "") . " >" . $cl->getName() . "</option>");
					unset($cl);
				}
				?></select></label>

		<h3>Next of Kin details</h3>
		<label class="sub">First
			Name<input type="text" name="kinfname" id="kinfname" value="<?= $pat->getKinsFirstName() ?>"></label>
		<label>Last
			Name<input type="text" name="kinlname" id="kinlname" value="<?= $pat->getKinsLastName() ?>"></label>
		<label>Relationship
			<select name="kin_relation_id" data-placeholder="Kin Relationship">
				<option></option><?php foreach ($kins as $k) { ?>
					<option <?= ($pat->getKinRelationship() !== null && $pat->getKinRelationship()->getId() == $k->getId() ? ' selected="selected"' : '') ?> value="<?= $k->getId() ?>"><?= $k->getName() ?></option><?php unset($k);} ?>
			</select></label>
		<!--<label>Phone<input type="text" name="kinphone" id="kinphone" value="<?= $pat->getKinsPhone() ?>"></label>-->
		<h5>Phone</h5>
		<div class="row-fluid">
			<label class="span4">Type <select name="type"><?php foreach ($contactTypes as $type) { ?>
						<option value="<?= $type ?>" <?= $pat->getKinsPhone() && $pat->getKinsPhone()->getType()==$type ? 'selected':'' ?>><?= ucwords($type) ?></option><?php } ?>
				</select> </label>
			<label class="span4">
				Country
				<input type="hidden" name="kin_phone_country_id" placeholder="select country" value="<?= $pat->getKinsPhone() ? $pat->getKinsPhone()->getCountry()->id :'' ?>">
			</label>

			<label class="span4">
				Phone
				<input type="text" name="kinphone" placeholder="8021234567" value="<?= $pat->getKinsPhone() ? $pat->getKinsPhone()->getPhone() : ''?>" pattern="^[0-9]*$">
			</label>

		</div>
		<label>Address
			<span style="margin-left: 20px;">
			<input type="checkbox" class="checkbox" data-same-address="true"> The same as Patient's address
	  	</span>
			<input type="text" name="kinaddress" id="kinaddress" value="<?= $pat->getKinsAddress() ?>"></label>

		<h3>Billing Profile</h3>
		<!--<label class="sub">Type
            <select name="insuranceType" id="insuranceType">
              <?php
		echo "<option value='self' " . (strtolower($pat->getScheme() == null) ? 'selected' : (strtolower($pat->getScheme()->getType()) == "self") ? "selected" : "") . " >SELF</option>";
		echo "<option value='insured' " . (strtolower($pat->getScheme() == null) ? '' : (strtolower($pat->getScheme()->getType()) != "self") ? "selected" : "") . " >INSURED</option>";
		?>
        </select></label>-->
		<label class="sub">Payer
			<select name="insuranceClass" id="insuranceClass" required="required">
				<?php
				foreach ($insSchemes as $insScheme) {
					?>
					<option data-type="<?= $insScheme->getType() ?>" value="<?= $insScheme->getId() ?>"<?= ($pat->getScheme()->getId() == $insScheme->getId() ? " selected" : "") ?>><?= $insScheme->getName() ?>
						(<?= ucwords($insScheme->getType()) ?>)
					</option>
					<?php unset($insScheme);
				} ?></select></label>
		<label class="sub" data-class="ins">Insurance Expiration</label>
		<label data-class="ins">
			<input type="text" name="insuranceExpiration" id="insuranceExpiration_" readonly="readonly" value="<?= $pat->getInsurance()->getExpirationDate() ? DateTime::createFromFormat('Y-m-d', $pat->getInsurance()->getExpirationDate())->format('d/m/Y') : $pat->getInsurance()->getExpirationDate(); ?>" placeholder="Click/touch to select expiration date">
		</label>
		<div data-class="ins" class="row-fluid">
			<label class="span6">Company
				<select name="company_id" data-placeholder="Select Company">
					<option value=""></option>
					<?php foreach ($companies as $company) { ?>
						<option value="<?= $company->getId() ?>" <?= ($pat->getInsurance()->getCompany() && $pat->getInsurance()->getCompany()->getId() == $company->getId() ? ' selected' : '') ?>><?= $company->getName() ?></option>
						<?php unset($company);
					} ?>
				</select>
			</label>
			<label class="span3">Policy
				Number<input type="text" name="policy_number" value="<?= $pat->getInsurance()->getPolicyNumber() ?>"></label>
			<label class="span3">Enrollee #
				<input type="text" name="enrollee_number" value="<?= $pat->getInsurance()->getEnrolleeId() ?>"></label>
		</div>
		<label data-class="ins" class="row-fluid">
      <span class="span12">Coverage Type
	      <select name="coverage_type" data-placeholder=" -- Coverage Type --">
	        <option value=""></option>
		      <?php foreach (CoverageType::$types as $type) { ?>
			      <option<?= $pat->getInsurance()->getCoverageType() == $type ? ' selected="selected"' : '' ?>><?= $type ?></option>
			      <?php unset($type);
		      } ?>
        </select>
      </span>
		</label>

		<div data-class="ins" class="row-fluid" id="dependentBlock">
			<label class="span10">
				Insurance Dependent
				<input type="hidden" name="dependent_id" value="<?= $pat->getInsurance()->getDependent() ? $pat->getInsurance()->getDependent()->getId() : '' ?>">
				<input type="text" name="parent_enrollee_id" value="<?= $pat->getInsurance()->getParentEnrolleeId() ? $pat->getInsurance()->getParentEnrolleeId() : '' ?>" placeholder="Principal Enrollee's ID or other details">
			</label>
			<label class="span2 no-label">
				<input type="checkbox" name="external_parent"<?= $pat->getInsurance()->getExternal() ? ' checked' : '' ?>>
				External Patient
			</label>
		</div>
		<label>Referring Company
			<input type="hidden" name="referral_company_id" value="<?= $pat->getReferralCompany() ? $pat->getReferralCompany()->getId() : ''?>" placeholder="Select referring facility where applicable">
			<?php /*
			<select name="referral_company_id" data-allow-clear="true" data-placeholder="Select referring facility where applicable">
				<option></option>
				<?php foreach ($referral_companies->data as $ref) {?>
					<option value="<?= $ref->getId() ?>" <?= $pat->getReferralCompany() && $pat->getReferralCompany()->getId()==$ref->getId() ? ' selected':''?>><?= $ref->getName() ?></option>
				<?php } ?>
			</select>
      <?php */ ?>
		</label>
		<label>Referring Doctor
			<input type="hidden" name="referral_id" value="<?= $pat->getReferral() ? $pat->getReferral()->getId() : '' ?>" data-allow-clear="true" data-placeholder="Select referring person where applicable">
			<?php /*
			<select name="referral_id" data-allow-clear="true" data-placeholder="Select referring person where applicable">
				<option></option>
				<?php foreach ($referrals->data as $ref) { ?>
					<option value="<?= $ref->getId() ?>" <?= $pat->getReferral() && $pat->getReferral()->getId()==$ref->getId() ? ' selected': ''?>><?= $ref->getName() ?>
						(<?= $ref->getCompany()->getName() ?>)
					</option>
				<?php } ?>
			</select>
      <?php */?>
		</label>
		<h3 class="hide-">Care Manager</h3>
		<label class="hide-">Care Manager
			<input type="hidden" value="<?= ($pat->getCareManager() != null) ? $pat->getCareManager()->getId() : "" ?>" name="care_manager_id" id="care_manager_id" class="select2">
		</label>

		<div class="btn-block">
			<input name="my_scheme_id" value="<?= $pat->getScheme()->getId() ?>" type="hidden">
			<button type="submit" class="btn">Save &raquo;</button>
			<button type="reset" class="btn-link" onclick="location.href='<?php if (isset($_GET['aid'])) { ?>/admissions/inpatient_profile.php?aid=<?= $_GET['aid'] ?>&pid=<?= $_REQUEST['id']; ?><?php } else { ?>/patient_profile.php?id=<?= $_REQUEST['id']; ?><?php } ?>'">
				Cancel &raquo;</button>
		</div>
	</form>
	<script>
		var districts = [];
		var districts_res = [];
		var referalsFiltered = [];
		$(document).ready(function () {
			$('#_title').select2({width: '100%'}).change(function () {
				var vars = [];
				_.each($('#_title').select2('data'), function (obj, idx) {
					vars.push(obj.text);
				});
				$('[name="title"]').val(vars.join('|'));
			}).select2('data', <?= json_encode($existingTitles, JSON_PARTIAL_OUTPUT_ON_ERROR)?>);
			
			$('input:hidden[name="referral_company_id"]').select2({
				width: '100%',
				allowClear: true,
				placeholder: $(this).data('placeholder'),
				data: function () {
					return {results: refCompanies, text: 'name'};
				},
				formatResult: function (source) {
					return source.name;
				},

				formatSelection: function (source) {
					return source.name;
				},
				initSelection: function (element, callback) {
					var id = $(element).val();
					if (id !== "") {
						$.ajax("/api/get_referral_companies.php?single&id=" + id, {
							dataType: "json"
						}).done(function (data) {
							callback(data);
						});
						$(element).trigger('change');
					}
				}
			}).change(function (e) {
				if(!e.handled){
					//console.log("changed");
					//if (e.added) {
					//	console.log(e.added.id)
					//}
					e.handled = true;
				}
			});
			
			$('input:hidden[name="referral_id"]').select2({
				width: '100%',
				allowClear: true,
				placeholder: $(this).data('placeholder'),
				data: function () {
					return {results: referals, text: 'name'};
				},
				formatResult: function (source) {
					return source.name;
				},

				formatSelection: function (source) {
					return source.name;
				},
				initSelection: function (element, callback) {
					var id = $(element).val();
					if (id !== "") {
						$.ajax("/api/get_referral_person.php?single&id=" + id, {
							dataType: "json"
						}).done(function (data) {
							callback(data);
							//$(element).trigger('change');
						});
					}
				}
			});
			
			var x = $("#address").val();
			var y = $("#kinaddress").val();
			if (x === y) {
				$(".checkbox:not([name='estimated_dob'])").attr('checked', true).iCheck('update');
				$("#kinaddress").hide();
			}

			$('[data-same-address="true"]').change(function (e) {
				var add = $("#address").val();
				if ($(this).is(':checked') && add !== '') {
					$("#kinaddress").val(add).hide();
				} else if ($(this).is(':checked') && add === '') {
					setTimeout(function () {
						$(e.target).prop('checked', false).trigger('change').iCheck('update');
					}, 50);
					Boxy.warn("Fill the patient address field");
				} else {
					$("#kinaddress").val("").show();
				}
			});

			$('[name="coverage_type"]').select2({width: '100%'}).change(function (e) {
				if ($(this).val() !== 'Principal' && $('#insuranceClass').find(":selected").data("type")==='insurance') {
					$('#dependentBlock').slideDown();
				} else {
					$('#dependentBlock').slideUp();
					$('[name="dependent_id"]').select2('val', '').val('');
				}
				//
			}).trigger('change');

			$('[name="dependent_id"]').select2({
				placeholder: "Search and select the principal patient",
				minimumInputLength: 3,
				width: '100%',
				allowClear: true,
				ajax: {
					url: "/api/search_insurance_principals.php",
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term,
							scheme_id: $('#insuranceClass').val()
						};
					},
					results: function (data, page) {
						return {results: data};
					}
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
				},
				formatResult: function (data) {
					var details = [];
					details.push(data.patientId ? "EMR ID:" + data.patientId : null);
					details.push(data.fname ? data.fname : null);
					details.push(data.mname ? data.mname : null);
					details.push(data.lname ? data.lname : null);
					return implode(" ", details);
				},
				formatSelection: function (data) {
					var details = [];
					details.push(data.patientId ? "EMR ID:" + data.patientId : null);
					details.push(data.fname ? data.fname : null);
					details.push(data.mname ? data.mname : null);
					details.push(data.lname ? data.lname : null);
					return implode(" ", details);
				},
				id: function (data) {
					return data.patientId;
				}
			});

			$('[name="lifestyle[]"]').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
				$(event.currentTarget).trigger('change');
			});
			$('.checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
				$(event.currentTarget).trigger('change');
			});

			$('[name="external_parent"]')/*.iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
				$(event.currentTarget).trigger('change');
			})*/.live('change',function (e) {
				if ($(e.target).is(":checked")) {
					$('[name="dependent_id"]').prev('.select2-container').addClass('hide');
					$('[name="parent_enrollee_id"]').removeClass('hide');
				} else {
					$('[name="dependent_id"]').prev('.select2-container').removeClass('hide');
					$('[name="parent_enrollee_id"]').addClass('hide');
				}
			}).trigger('change');
			
			setInterval(function () {
				//$('[name="external_parent"]').trigger('change').iCheck('update');
			}, 50);
			$("#care_manager_id").select2({
				placeholder: "Staff Name (Specialization [Staff ID])",
				allowClear: true,
				minimumInputLength: 3,
				width: '100%',
				formatResult: function (data) {
					return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "]) " + data.phone;
				},
				formatSelection: function (data) {
					return data.fullname + " (" + (data.specialization == null ? "" : data.specialization.name) + " [" + data.id + "])";
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the staff name or ID or phone or specialization";
				},
				ajax: {
					url: '/api/search_staffs.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							limit: 100,
							asArray: true
						};
					},
					results: function (data, page) {
						// console.log(data)
						return {results: data};
					}
				},
				initSelection: function (element, callback) {
					var id = $(element).val();
					if (id !== "") {
						$.ajax("/api/get_staff.php?sid=" + id, {
							dataType: "json"
						}).done(function (data) {
							callback(data);
						});
					}
				}
			});//End Staff Select2

			$('#state').change(function (e) {
				$('[name="district_id"]').select2('val', '');
				$.post('/api/get_district.php', {
					'type': 'list',
					state_id: e.val || $(e.target).val()
				}, function (data) {
					districts = JSON.parse(data);
				});
			}).trigger('change');

			$('[name="district_id"]').select2('val', '').select2({
				width: '100%',
				allowClear: true,
				placeholder: "--select district--",
				data: function () {
					return {results: districts, text: 'name'};
				},
				formatResult: function (source) {
					return source.name;
				},
				formatSelection: function (source) {
					return source.name;
				},
				initSelection: function (element, callback) {
					var id = $(element).val();
					if (id !== "") {
						$.post('/api/get_district.php', {
							type: 'single',
							id: id
						}, function (data) {
							callback(data);
						}, 'json');
					}
				}
			});

			$('#state_r').change(function (e) {
				$('[name="district_res_id"]').select2('val', '');
				$.post('/api/get_district.php', {
					'type': 'list',
					state_id: e.val || $(e.target).val()
				}, function (data) {
					districts_res = JSON.parse(data);
				});
			}).trigger('change');

			$('[name="district_res_id"]').select2('val', '').select2({
				width: '100%',
				allowClear: true,
				placeholder: "--select district--",
				data: function () {
					return {results: districts_res, text: 'name'};
				},
				formatResult: function (source) {
					return source.name;
				},
				formatSelection: function (source) {
					return source.name;
				},
				initSelection: function (element, callback) {
					var id = $(element).val();
					if (id !== "") {
						$.post('/api/get_district.php', {
							type: 'single',
							id: id
						}, function (data) {
							callback(data);
						}, 'json');
					}
				}
			});
			
			

			$('#_addNumLnk, #phonen').click(function (e) {
				if (!e.handled) {
					Boxy.load('/patient_contacts.php');
					e.handled = true;
				}
			});
			$('#_delNumLnk, #_delNumLnk2').live('click', function (e) {
				if (!e.handled) {
					$('#phonen').val('');
					$('[name="phonen_"]').val('');
					$('#displayPanel').empty();
					$('#multiple_phone').val('');
					e.handled = true;
				}
			});

			var countries = JSON.parse('<?=json_encode($countries) ?>');
			$('input[name="kin_phone_country_id"]').select2({
				width: '100%',
				allowClear: true,
				data: function () {
					return {results: countries, text: 'country_name'};
				},
				formatResult: function (source) {
					return source.country_name + (source.dialing_code ? ' (' + source.dialing_code + ')' : '');
				},
				formatSelection: function (source) {
					return source.dialing_code;
				},
				initSelection: function (element, callback) {
					var id = $(element).val();
					if (id !== "") {
						$.post('/api/get_country.php', {
							type: 'single',
							id: id
						}, function (data) {
							callback(data);
						}, 'json');
					}
				}
			});

			loadContacts();
		});

		function loadContacts() {
			var vars = $('#multiple_phone').val();
			var oldPhones = vars !== '' ? JSON.parse(vars) : [];
			//var phones = $('#contactForm').serializeObject();
			//oldPhones.push(phones);
			$('#multiple_phone').val(JSON.stringify(oldPhones));
			//$('#contactForm')[0].reset();
			//$('#contactForm [name="country_id"]').select2("val", "").val("");
			//var init = $('#contactForm [name="type"] option:first-child').attr('value');
			//$('#contactForm [name="type"]').select2("val", init).val(init);
			//$('[name="primary"]').iCheck('update');
			var vals = [];
			_.each(oldPhones, function (obj, idx) {
				var country = _.find(countries, ['id', obj.country_id]);
				//console.log(country);
				vals.push('(' + country.dialing_code + ')' + obj.phone + (obj.primary && obj.primary === "on" ? ' *Primary' : ''));
			});
			$('#phonen').val(vals.join(', '));
		}
	</script>
<?php } else { ?>
	<div class="warning-bar">Error: This page has misbehaved. Please contact the
		administrator about this page.
	</div>
<?php } ?>
