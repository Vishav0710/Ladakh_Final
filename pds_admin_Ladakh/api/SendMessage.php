<?php

require('../util/Connection.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php'); 
require('../util/Security.php');
require ('../util/Encryption.php');
$nonceValue = 'nonce_value';
function generateRandomId($length = 10) {
    // Generate random bytes
    $bytes = random_bytes(ceil($length / 2));
    
    // Convert random bytes to hexadecimal string
    $randomId = substr(bin2hex($bytes), 0, $length);

    return $randomId;
}

if(!SessionCheck()){
	return;
}
$RATE_LIMIT_COUNT  = 5;   // change to 3-5 as required
$RATE_LIMIT_WINDOW = 60;  // seconds

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Identify the client — prefer user identity (authenticated), else fallback to IP
$clientKey = isset($_SESSION['user']) ? 'user_'.$_SESSION['user'] : 'ip_'.($_SERVER['REMOTE_ADDR'] ?? 'anon');

// initialize container
if (!isset($_SESSION['rate_limit_sendmessage'])) {
    $_SESSION['rate_limit_sendmessage'] = [];
}
if (!isset($_SESSION['rate_limit_sendmessage'][$clientKey])) {
    $_SESSION['rate_limit_sendmessage'][$clientKey] = [];
}

// Purge timestamps outside the window
$now = time();
$_SESSION['rate_limit_sendmessage'][$clientKey] = array_filter(
    $_SESSION['rate_limit_sendmessage'][$clientKey],
    function($ts) use ($now, $RATE_LIMIT_WINDOW) {
        return ($ts >= $now - $RATE_LIMIT_WINDOW);
    }
);

// Enforce limit
if (count($_SESSION['rate_limit_sendmessage'][$clientKey]) >= $RATE_LIMIT_COUNT) {
    http_response_code(429); // Too Many Requests
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Rate limit exceeded. Please try again later.']);
    // log the event
    writeLog("RateLimit -> SendMessage -> user: ".(isset($_SESSION['user'])?$_SESSION['user']:'anon')." | IP: ".$_SERVER['REMOTE_ADDR']);
    exit;
}

// Record this request
$_SESSION['rate_limit_sendmessage'][$clientKey][] = $now;
// END: RATE LIMITER
// ----------------------------

require('Header.php');


$person = new Login;
$person->setUsername($_POST["username"]);
$Encryption = new Encryption();
$person->setPassword($Encryption->decrypt($_POST["password"], $nonceValue));

if($_SESSION['user']!=$person->getUsername()){
	echo "User is logged in with different username and password";
	return;
}

$query = "SELECT * FROM login WHERE username='".$person->getUsername()."'";
$result = mysqli_query($con,$query);
$row = mysqli_fetch_assoc($result);

// if($numrows == 0){
// 	echo "Error : Password or Username is incorrect";
// 	return;
// }

$dbHashedPassword = $row['password'];
if(password_verify($person->getPassword(), $dbHashedPassword)){
$message = $_POST['message'];
$uniqueid = $_POST['uniqueid'];
$date = date('Y-m-d H:i:s');

if($uniqueid=="all"){
	$query = "SELECT uid FROM login WHERE role!='admin'";
	$result = mysqli_query($con,$query);
	while($row = mysqli_fetch_assoc($result)){
		$uniqueid = $row['uid'];
		$id = generateRandomId(10);
		$query = "INSERT INTO user_message (id,user_id,message,date,acknowledged) VALUES ('$id','$uniqueid','$message','$date','no')";
		mysqli_query($con, $query);
		$log_query = "select username from login WHERE uid='$uniqueid'";
		$log_result = mysqli_query($con,$log_query);
		if ($log_result && $row = $log_result->fetch_assoc()) {
			$log_name = $row['username'];
		}
		$filteredPost = $_POST;
		unset($filteredPost['username'], $filteredPost['password']);
		writeLog("User ->" ." Send Message ->". $_SESSION['user'] . "| Requested JSON ->
		" . json_encode($filteredPost). " | " . $log_name);
		
	}
}
else{
	$id = generateRandomId(10);
	$query = "INSERT INTO user_message (id,user_id,message,date,acknowledged) VALUES ('$id','$uniqueid','$message','$date','no')";
	mysqli_query($con, $query);
}

$log_query = "select username from login WHERE uid='$uniqueid'";
$log_result = mysqli_query($con,$log_query);
if ($log_result && $row = $log_result->fetch_assoc()) {
$log_name = $row['username'];
}
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("User ->" ." Send Message ->". $_SESSION['user'] . "| Requested JSON ->
" . json_encode($filteredPost). " | " . $log_name);

echo "<script>window.location.href = '../SendMessage.php';</script>";
} 
else{
    echo "Error : Password or Username is incorrect";
}

?>
<?php require('Fullui.php');  ?>