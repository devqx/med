<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/28/14
 * Time: 4:50 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PaymentMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PaymentMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$pay_Types = getTypeOptions('type', 'payment_methods');

if ($_POST) {
	if(is_blank($_POST['name'])){exit("error:Name can't be blank");}
	if(is_blank($_POST['type'])){exit("error:Type of method is required");}

	$pm = (new PaymentMethod())->setName($_POST['name'])->setType($_POST['type'])->setLedgerId($_POST['ledger_id'])->add();

	if ($pm !== null) {
		exit("success:Added payment method");
	}
	exit("error:Failed to add method");
}
?>
<section>
	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {onComplete:___posted})">
		<label>Name <input type="text" name="name"></label>
		<label>Type <select name="type"><?php foreach ($pay_Types as $types) { ?>
					<option value="<?= $types ?>"><?= ucfirst($types) ?></option>
				<?php } ?>
			</select></label>
		<label>ERP Ledger <input type="text" name="ledger_id"></label>
		<div class="btn-block">
			<button type="submit" class="btn">Add</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<script type="text/javascript">
	function ___posted(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1])
		} else if (data[0] === "success") {
			showTabs(4);
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			})
		}
	}
</script>
