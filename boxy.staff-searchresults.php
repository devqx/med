<div style="width: 100%; height: 350px; overflow:auto">Search Results for query: <?php echo $_GET['id'] ?>
<table class="staff-find table table-hover"><thead><tr>
  <th>&nbsp;</th>
  <th>Name</th>
  <th>Phone Number</th>
  <th>Email</th>
  <th>Profession</th>
  <th>Office/Hospital</th>
</tr></thead>
<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php'; $staff = new StaffManager();
if(!isset($_GET['type']) || $_GET['type']!="advanced"){
	echo $staff->doBasicSearch($_GET['id']);
}else {
	echo $staff->doBasicSearch($_GET['id'],$type="advanced",$options=$_GET['options']);
}?>          
</table>
</div>