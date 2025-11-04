<?php
require('../util/Connection.php');
require('../structures/District.php');
require('../util/SessionFunction.php');

if(!SessionCheck()){
	return;
}

$month = $_POST['month'];
$district = $_POST['district'];

// Validate month (letters, numbers, underscore)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $month)) {
    die("Invalid month format");
}

// Validate district (letters only)
if (!preg_match('/^[a-zA-Z]+$/', $district)) {
    die("Invalid district name");
}
$parts = explode('_', $month);

if (count($parts) !== 2) {
    die("Invalid month_year format");
}

$month = $parts[0];
$year = $parts[1];
$query = "SELECT * FROM optimised_table WHERE month='$month' AND year='$year'";
$result = mysqli_query($con,$query);
$numrow = mysqli_num_rows($result);
$id = "";
if($numrow>0){
	$row = mysqli_fetch_assoc($result);
	$id = $row['id'];
}

$tablename = "optimiseddata_".$id;
$result = $con->query("SELECT DISTINCT from_id,from_name from $tablename WHERE to_district='$district'");

if ($result->num_rows > 0) {
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
}
?>