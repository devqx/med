<?php include $_SERVER['DOCUMENT_ROOT'].'/footer.queues.php';?>
<a href="/home.php">Home</a> | <a href="javascript:void(0)" onclick="loadsqX()">View All Admitted</a> | <a href="javascript:void(0)" onclick="loadsqY()">Assign Bed to Patient</a> | <a href="javascript:void(0)" onclick="showAdmissionSearch()">Search</a><?php if (isset($_SESSION['staffID'])){ ?>| <a href="/logout.php">Log Out</a><?php }?>
</div>
</footer>