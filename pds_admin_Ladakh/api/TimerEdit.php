<?php

require('../util/Connection.php');
require('../util/SessionFunction.php');

if(!SessionCheck()){
	return;
}

require('Header.php');

if(empty($_POST)){
	die("Something went wrong.");
}

$date = $_POST['date'];
$time = $_POST['time'];

// Validate date format (YYYY-MM-DD)
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
    echo "Error: Invalid date format. Please use the format YYYY-MM-DD.";
    exit;  // Stop further execution
}

// Optionally, validate time format (HH:MM)
if (!preg_match("/^\d{2}:\d{2}$/", $time)) {
    echo "Error: Invalid time format. Please use the format HH:MM.";
    exit;  // Stop further execution
}

$currentDate = date("Y-m-d");
if ($date < $currentDate) {
    echo "Error: Date cannot be a past date.";
    exit;
}

$query = "UPDATE timer SET deadline_date='$date', deadline_time='$time' WHERE 1";
mysqli_query($con,$query);
mysqli_close($con);

echo "<script>window.location.href = '../Timer.php';</script>";

?>
<?php require('Fullui.php');  ?>