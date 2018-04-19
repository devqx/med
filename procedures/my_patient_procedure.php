<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/19/14
 * Time: 11:05 AM
 * @param $id
 * @param $array
 * @return bool
 */
function findStaffIdInResources($id, $array)
{
	foreach ($array as $element) {
		if ($id == $element->getResource()->getId()) return true;
	}
	return false;
}

?>
<!--<a href="">Open</a> | <a href="">All</a>-->
<div class="clear"></div>
<div>
	<?php
	@session_start();
	$staffId = $_SESSION['staffID'];
	$serviceCentreId = isset($_REQUEST['service_centre_id']) ? $_REQUEST['service_centre_id'] : null;
	$category_id = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : null;
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
	$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
	$pageSize = 10;
	$pro = [];
	$data = (new PatientProcedureDAO())->all(null, null, $serviceCentreId, $category_id, null, $page, $pageSize);
	$totalSearch = $data->total;
	foreach ($data->data as $p) {
		//check if this staff is a `resource',
		//or is the surgeon,
		//or is the anaesthesiologist

		if (findStaffIdInResources($staffId, $p->getResources()) || ($p->getAnesthesiologist() && $p->getAnesthesiologist()->getId() == $staffId) || ($p->getSurgeon() && $p->getSurgeon()->getId() == $staffId)) {
			$pro[] = $p;
		}
	}

	$totalSearch = count($pro);

	include_once 'templater.php';
	exit; ?>
</div>