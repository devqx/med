<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/23/17
 * Time: 12:13 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
$scheme = (new InsuranceSchemeDAO())->get($_GET['id']);
$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes(false);

if ($_POST) {
	$schemeSourceId = $_POST['source_id'];
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	//:the ones to affect
	$sql = "SELECT id FROM insurance_schemes WHERE id=".$_POST['destination_id'];
	$stmt = $pdo->prepare($sql);//, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)
	$stmt->execute();
	$v1 = $v2 = false;
	while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
		$sql1 = "DELETE FROM insurance_items_cost WHERE insurance_scheme_id={$row['id']}";
		$stmt1 = $pdo->prepare($sql1, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$v1 = $stmt1->execute();
		$sql2 = "INSERT INTO insurance_items_cost (item_code, selling_price, followUpPrice, theatrePrice, anaesthesiaPrice, surgeonPrice, co_pay, insurance_scheme_id, insurance_code, type, capitated, hospid) SELECT item_code, selling_price, followUpPrice, theatrePrice, anaesthesiaPrice, surgeonPrice, co_pay, ".$row['id'].", insurance_code, type, capitated, 1 FROM insurance_items_cost WHERE insurance_scheme_id=$schemeSourceId";
		$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$v2 = $stmt2->execute();
	}
	if($v2){
		$pdo->commit();
		exit('success:Replication Successful!');
	}
	exit('error:Process Failed');
	
}
?>
<section style="width:500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, _processReplication);">
		Prices will be replaced from <span class="badge"><?= $scheme->getName() ?></span> into:
		<div class="clear"></div>
		<label>Select Destination Scheme
			<select name="destination_id">
				<?php foreach ($schemes as $s) { ?>
					<option value="<?= $s->getId() ?>" <?= ($s->getId() == $scheme->getId()) ? 'disabled' : '' ?>><?= $s->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<input type="hidden" name="source_id" value="<?= $scheme->getId() ?>">
		<div class="clear"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Submit</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var _processReplication = {
		onStart: function(){
			$(document).trigger('ajaxSend');
		}, onComplete: function (s) {
			$(document).trigger('ajaxStop');
			var status_ = s.split(":");
			if (status_[0] === 'success') {
				Boxy.get($('.close')).hideAndUnload();
			}
			else {
				Boxy.warn(status_[1]);
			}
		}
	};
</script>

