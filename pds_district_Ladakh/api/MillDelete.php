<?php
// var_dump($_SESSION);
// var_dump($_POST);
require('../util/Connection.php');
require('../structures/Mill.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php');
require('../util/Security.php');
require ('../util/Encryption.php');
$nonceValue = 'nonce_value';

if(!SessionCheck()){
	return;
}

$district = $_SESSION["district_district"];

require('Header.php');

if(empty($_POST) || empty($_POST['username']) || empty($_POST['password'])){
    die("Something went wrong");
}


$person = new Login;
$person->setUsername($_POST["username"]);
$Encryption = new Encryption();
$person->setPassword($Encryption->decrypt($_POST["password"], $nonceValue));

if($_SESSION['district_user']!=$person->getUsername()){
	echo "User is logged in with different username and password";
	return;
}

$query = "SELECT * FROM login WHERE username='".$person->getUsername()."'";
$result = mysqli_query($con,$query);
$row = mysqli_fetch_assoc($result);
// $numrows = mysqli_num_rows($result);

// if($numrows == 0){
// 	echo "Error : Password or Username is incorrect";
// 	return;
// }

$dbHashedPassword = $row['password'];
if(password_verify($person->getPassword(), $dbHashedPassword)){
$DCP = new DCP;
$DCP->setUniqueid($_POST['uid']);

$query = $DCP->delete($DCP);

if($_POST['uid']=="all"){
	$query = $DCP->deletealldistrict($DCP, $district);
}

$log_query = $DCP->logname($DCP);
$log_name= "all";
$log_result = mysqli_query($con,$log_query);
if ($log_result && $row = $log_result->fetch_assoc()) {
$log_name = $row['name'];
}

mysqli_query($con,$query);
mysqli_close($con);

$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("District User ->" ." Mill deleted -> ". $_SESSION['district_user'] . "| Requested JSON -> " . json_encode($filteredPost) . " | " . $log_name);

echo "<script>window.location.href = '../Mill.php';</script>";
}
else{
	echo "Error : Password or Username is incorrect";
}

?>
<?php require('Fullui.php');  ?>
