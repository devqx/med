<?php include $_SERVER['DOCUMENT_ROOT'] . '/footer.queues.php'; ?>
<a href="/home.php">Home</a> |
<a href="javascript:void(0)" onclick="showFind()">Show Patients</a> |


<a href="./">Lab Request Queue</a>
<?php if (isset($_SESSION['staffID'])){ ?>| <a href="/logout.php">Log Out</a><?php } ?></div></footer>