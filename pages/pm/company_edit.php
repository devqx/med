<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/16
 * Time: 11:08 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CompanyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$company = (new CompanyDAO())->get($_GET['id']);

if($_POST){
	$company = (new CompanyDAO())->get($_POST['company_id']);
	if(!is_blank($_POST['company_name'])){
		$company->setName($_POST['company_name']);
		$co = (new CompanyDAO())->update($company);
		if($co != null){exit("success:Company updated");}
		else {
			exit("error:Failed to update Company");
		}
	} else {
		exit("error:Company name is required");
	}
}
?>
<section style="width: 600px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onComplete: ed_comple})">
		<label>Company Name <input type="text" name="company_name" value="<?= $company->getName()?>"> </label>
		<div class="btn-block">
			<input type="hidden" value="<?= $company->getId()?>" name="company_id">
			<button type="submit" class="btn">Update Company</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<script type="text/javascript">
	var ed_comple = function (data) {
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