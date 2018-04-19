<?php
if ($_POST) {
	sleep(0.1);
	
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabCategory.php';
	
	if(is_blank($_POST['test_class'])){exit('error:Blank entry is not allowed');}
	$lab = new LabCategory();
	$lab->setName($_POST['test_class']);
	
	$return =  (new LabCategoryDAO())->addLabCategory($lab);
	
	exit($return != null ? 'ok': 'error:Looks like you already have that category' );
}
?>
<div>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
		<label>New Test Category <span class="right">e.g. Serology</span><input type="text" name="test_class" id='test_class'/></label>

		<div class="btn-block">
			<button type="submit" class="btn" onclick="setAddedCat()">Add</button>
			<button type="button" data-name="cancel" onclick="Boxy.get(this).hide()" class="btn-link">Cancel &raquo;</button>
		</div>
		<div id="mgniu"></div>

	</form>
</div>


<script type="text/javascript">
	function start() {
		$('#mgniu').html('<img src="/img/loading.gif"> please wait');
	}

	function done(s) {
		if (s == 'ok') {
			$('#mgniu').html('<span class="alert alert-info">Saved</span>');

		} else {
			var data = s.split(":");
			$('#mgniu').html('<span style="color:#C00;font-weight:bold;">' + data[1] + '</span>');
		}
	}

	function setAddedCat() {
		newlyAdded = $('#test_class').val();
	}
</script>