<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/13/17
 * Time: 11:19 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugSuperGenericData.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/SuperGeneric.php';
$pdo->beginTransaction();
$sql = "SELECT *, GROUP_CONCAT(id) AS ids FROM drug_generics GROUP BY `name` ORDER BY `name`";
$stmt = $pdo->prepare($sql);//, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)
$stmt->execute();
ob_implicit_flush(true);
ob_start();
?>
	<table>
		<?php
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			ob_flush();
			$data = [];
			foreach(explode(',',$row['ids']) as $dId){
				$data[] = (new DrugSuperGenericData())->setDrugGeneric( new DrugGeneric($dId) );
			}
			(new SuperGeneric())->setName($row['name'])->setData($data)->add($pdo);
			sleep(0.4);
			?>
			<tr>
				<td><?= $row['name'] ?></td>
				<td><?= $row['ids'] ?></td>
			</tr>
		<?php } ?>
	</table>
<?php
$pdo->commit();
ob_end_flush();
exit;