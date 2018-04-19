<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/16
 * Time: 10:09 AM
 */
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Company.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CompanyDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

	if(!is_blank($_POST['company_name'])){
		$company = (new Company())->setName($_POST['company_name']);

		$co = (new CompanyDAO())->add($company);
		if($co != null){exit("success:Company added");}
		else {
			exit("error:Failed to add Company");
		}
	} else {
		exit("error:Company name is required");
	}

}
?>
<section style="width: 600px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: comple})">
		<label>Company Name <input type="text" name="company_name"> </label>
		<div class="btn-block">
			<button type="submit" class="btn">Add Company</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<script type="text/javascript">
	var comple = function (data) {
		var dat = data.split(":");
		if (dat[0]==="error"){
			Boxy.warn(dat[1]);
		} else if(dat[0] === "success"){
			Boxy.info(dat[1], function(){
				Boxy.get($(".close")).hideAndUnload();
			});
		}
	};
</script>
