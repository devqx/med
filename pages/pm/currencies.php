<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/14/17
 * Time: 10:17 AM
 */
Header("Content-Type: text/html; charset=UTF-8");
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$data = (new CurrencyDAO())->all();

if($_POST){
	if($_POST['action']=='activate'){
		$cur = (new CurrencyDAO())->get($_POST['id'])->setActive($_POST['checked']=='true' ? TRUE : FALSE )->update();
	}
	if($_POST['action']=='default'){
		$cur = (new CurrencyDAO())->get($_POST['id'])->setDefault($_POST['checked']=='true' ? TRUE : FALSE )->update();
	}
}

?>
<form id="currFrm" method="post" action="<?=$_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, formHandler20)">
	<table class="table">
		<thead>
		<tr>
			<th>Currency</th>
			<th>Symbol</th>
			<th>Value</th>
			<th>Default</th>
			<th>Active</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $datum) {//$datum=new Currency();?>
			<tr>
				<td><?= $datum->getTitle() ?> (<?= $datum->getCode() ?>)</td>
				<td>
					<?= ($datum->getSymbolLeft()) ?>
					<?= ($datum->getSymbolRight()) ?>
				</td>
				<td><?= $datum->getValue() ?></td>
				<td><input onchange="onDefault(this)" title="Default Currency" type="radio" name="default" value="<?= $datum->getId() ?>" <?= $datum->isDefault() ? "checked" : "" ?>></td>
				<td><input onchange="onActiveDe(this)" title="Active or Not" type="checkbox" name="active" value="<?= $datum->getId() ?>" <?= $datum->isActive() ? "checked" : "" ?>></td>
				<td><a href="javascript:" class="edit_currency" data-href="/pm/accounts/currency.edit.php?id=<?= $datum->getId() ?>">Edit</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</form>
<script type="text/javascript">
	var onDefault = function (item) {
		$.post('<?=$_SERVER['REQUEST_URI'] ?>', {
			id: item.value,
			checked: item.checked,
			action: 'default'
		})
	};
	var onActiveDe = function (item) {
		$.post('<?=$_SERVER['REQUEST_URI'] ?>', {
			id: item.value,
			checked: item.checked,
			action: 'activate'
		})
	};
	var formHandler20 = {onStart: function () {
		$(document).trigger('ajaxSend');
	}, onComplete: function (s) {
		$(document).trigger('ajaxStop');
		var data=s.split(':');
		if(data[0]==='error'){
			Boxy.warn(data[1]);
		} else if(data[0]==='success'){
			showTabs(7);
			Boxy.get($('.close')).hideAndUnload();
		}
	}};
	
	$(document).on('click', '.edit_currency', function (e) {
		if(!e.handled){
			Boxy.load($(e.target).data('href'));
			e.handled = true;
		}
	})
</script>