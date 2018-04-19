<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 4:48 PM
 */

if (!isset($_SESSION)) {
	session_start();
}
require_once $_SERVER ['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/Package.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/PackageDAO.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/PackageCategoryDAO.php";
$pkgs = (new PackageCategoryDAO())->all();
if ($_POST) {
	if(is_blank($_POST['name'])){exit('error:Package name is required');}
	if(is_blank($_POST['category_id'])){exit('error:Package Category is required');}
	if(is_blank($_POST['price'])){exit('error:Package Price is required');}
	if(is_blank($_POST['expiration'])){exit('error:Package expiration date is required');}
	$active = isset($_POST['active']) && $_POST['active'] == 'on' ? true : false;
	$package = (new Package())->setActive($active)->setExpiration($_POST['expiration'])->setCategory( (new PackageCategoryDAO())->get($_POST['category_id']) )->setName($_POST['name'])->setPrice(parseNumber($_POST['price']))->add();
	
	if ($package !== null) {
		exit("success:Package added");
	}
	exit("error:Failed to create package");
}

?>

<div id="addpackage">
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
		<label>
			<span id="msg"></span>
		</label>
		<label>
			Package Name: <span class="required-text">*</span>
			<input name="name" type="text">
		</label>
		<label>Category
			<span class="pull-right"><a href="javascript:" onclick="Boxy.load('/pages/pm/packages/add.category.php', {title: 'New category'})">New Category</a></span>
			<select name="category_id" data-placeholder="-- select --">
				<option></option>
				<?php foreach ($pkgs as $pkg){?>
					<option value="<?= $pkg->getId()?>"><?=$pkg->getName() ?></option>
				<?php }?>
			</select></label>
		<div class="row-fluid">
			<label class="span6">
				Price to charge: <span class="required-text">*</span>
				<input name="price" type="number" min="0">
			</label>
			<label class="span6">Expiration <input type="text" name="expiration"> </label>
		</div>
		
		<label><input type="checkbox" checked name="active"> Activate already </label>

		<div class="btn-block">
			<button class="btn" type="submit" name="update">CreatePackage &raquo;</button>
			<button class="btn-link cancelBtn" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('[name="expiration"]').datetimepicker({
			format:'Y-m-d',
			formatDate:'Y-m-d',
			timepicker:false,
			onShow:function( ct ){
				this.setOptions({  minDate: new Date().toISOString().split('T')[0] });
			}
		})
	});
	function start() {
		$('#msg').html('<img src="/img/loading.gif"/> <em> please wait ...</em>');
	}
	function done(s) {
		var status_ = s.split(":");
		if (status_[0] === 'success') {
			$('#msg').html('<span class="alert-box notice">' + status_[1] + '</span>');
			showTabs(1);
			setTimeout(function () {
				Boxy.get($('.cancelBtn')).hideAndUnload();
			}, 500);
		} else {
			$('#msg').html('<span class="alert-box error">' + status_[1] + '</span>');
		}
	}
</script>