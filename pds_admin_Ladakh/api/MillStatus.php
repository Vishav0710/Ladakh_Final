<?php

require('../util/Connection.php');
require('../util/SessionFunction.php');
require('../util/Logger.php');
if(!SessionCheck()){
	return;
}

require('Header.php');


$id = $_POST["uid"];

$query = "SELECT * FROM dcp WHERE uniqueid='$id'";
$result = mysqli_query($con,$query);
$numrows = mysqli_num_rows($result);

if($numrows>0){
	$row = mysqli_fetch_assoc($result);
	$status = $row['active'];
	$Millname = $row['name'];
	if($status==0){
		$query = "UPDATE dcp SET active='1' WHERE uniqueid='$id'";
		writeLog("User ->" ." Mill Active -> ". $_SESSION['user'] . "| " .
$Millname);
		mysqli_query($con,$query);
	}
	else{
		$query = "UPDATE dcp SET active='0' WHERE uniqueid='$id'";
		writeLog("User ->" ." Mill InActive -> ". $_SESSION['user'] . "| " .
$Millname);
		mysqli_query($con,$query);
	}
}


mysqli_close($con);

echo "<script>window.location.href = '../Mill.php';</script>";

?>
<?php require('Fullui.php');  ?>