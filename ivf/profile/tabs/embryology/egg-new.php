<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:42 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/EggCollectionMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/FollicleSizeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/EggCollection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/EggCollectionFollicleData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/FollicleSize.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$eggMethods = (new EggCollectionMethodDAO())->all();
$follicleSizes = (new FollicleSizeDAO())->all();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	if (is_blank($_POST['collection_date'])) {
		exit('error:Collection date is required');
	}
	if (is_blank($_POST['method_id'])) {
		exit('error:Method is required');
	}
	if (is_blank($_POST['lefts'])) {
		exit('error:Total Left is required');
	}
	if (is_blank($_POST['rights'])) {
		exit('error:Total Right is required');
	}
	if (is_blank($_POST['comment'])) {
		exit('error:Comment is required');
	}
	if (is_blank($_POST['procedure_by_id'])) {
		exit('error:Who performed the procedure?');
	}
	if (is_blank($_POST['aid'])) {
		exit('error:A general id error');
	}
	$witnesses = [];
	$post = json_encode(explode(',', @$_POST['witness_ids'][0]));
	foreach (json_decode($post) as $wit) {
		if ((int)$wit !== 0) {
			$witnesses[] = (int)$wit;
		}
	}
	$data = [];
	$totals1 = intval(parseNumber($_POST['lefts']) + parseNumber($_POST['rights']));
	//$totals2 = 0;
	//foreach ($_POST['size'] as $sizeId => $value) {
	//	if (is_blank($value)) {
	//		exit('error:You must specify number of eggs that fall within a Follicle Size category');
	//	}
	//	//$totals2 = $totals2 + parseNumber($value);
	//	$data[] = (new EggCollectionFollicleData())->setSize(new FollicleSize($sizeId))->setValue($value);
	//}
	//
	if(!$totals1){
		exit('error:Follicle Sizes should be equal to the Lefts and Rights');
	}
	
	$pdo->beginTransaction();
	
	$save = (new EggCollection())->setInstance(new IVFEnrollment($_POST['aid']))->setTimeEntered(date(MainConfig::$mysqlDateTimeFormat))->setUser(new StaffDirectory($_SESSION['staffID']))->setCollectionTime($_POST['collection_date'])->setMethod(new IVFMethod($_POST['method_id']))->setDoneBy(new StaffDirectory($_POST['procedure_by_id']))->setData($data)->setTotalLeft($_POST['lefts'])->setTotalRight($_POST['rights'])->setWitnesses($witnesses)->setComment($_POST['comment'])->add($pdo);
	
	if ($save !== null) {
		$pdo->commit();
		exit('success:Data saved!');
	}
	$pdo->rollBack();
	exit('error:Failed to save data');
}
?>
<section style="width: 400px;">
	<div class="well">Egg Collection</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: __09sdfl, onComplete: __9834rjwe})">
		<div class="row-fluid">
			<label class="span6">Time of collection <input type="text" name="collection_date"> </label>
			<label class="span6">Method
				<select name="method_id" data-placeholder="-- Select Method --"><?php foreach ($eggMethods as $method) { ?>
						<option value="<?= $method->getId() ?>"><?= $method->getName() ?></option><?php } ?></select></label>
		</div>
		
		<label>Procedure done by <input type="hidden" name="procedure_by_id" required data-placeholder="--Select--"></label>
		<div class="paper-card">
			<div class="heading">Follicles</div>
			<div class="card-content">
				<!--<div class="row-fluid">-->
				<!--	-->
				<!--		<div class="span--> <!--"><label>-->
				<!--				<input name="size[--><!--]" type="number" data-decimals="0" value="0" class="cell-number-"> </label></div>-->
				<!--	-->
				<!--</div>-->
				<div class="row-fluid">
					<label class="span6">Left Ovary Total <input type="number" data-decimals="0" name="lefts" min="1" step="1"> </label>
					<label class="span6">Right Ovary Total<input type="number" data-decimals="0" name="rights" min="1" step="1"> </label>
				</div>
			</div>
		</div>
		<!-- witness is a m2m -->
		<label>Comment <textarea name="comment"></textarea> </label>
		<label>Witness(es) <input type="hidden" name="witness_ids[]"> </label>
		<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
		<p></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	__09sdfl = function () {
		$(document).trigger('ajaxSend');
	};

	__9834rjwe = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
			setTimeout(function () {
				$('a[data-href^="egg.php"]')[0].click();
			}, 50);
		}
	};

	$(document).ready(function () {
		
		$('textarea[name="comment"]').summernote(SUMMERNOTE_CONFIG);
		$('[name="collection_date"]').datetimepicker({
			onShow: function (ct) {
				this.setOptions({maxDate: new Date().toISOString().split('T')[0]});
			}
		});

		$('[name="witness_ids[]"]').select2({
			placeholder: "Staff Name (Specialization [Staff ID])",
			allowClear: true,
			multiple: true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "]) " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "])";
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
					//console.log(data)
					return {results: data};
				}
			}
		});//End Staff Select2
		$('[name="procedure_by_id"]').select2({
			placeholder: "Staff Name (Specialization [Staff ID])",
			allowClear: true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "]) " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "])";
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
					//console.log(data)
					return {results: data};
				}
			}
		});//End Staff Select2
	})
</script>
