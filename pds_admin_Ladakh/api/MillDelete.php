<?php
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

require('Header.php');


$person = new Login;

if (empty($_POST['username']) || empty($_POST['password'])) {
    echo "Error: Username or Password missing";
    return;
}

$person->setUsername($_POST["username"]);
$Encryption = new Encryption();
$decryptedPassword = $Encryption->decrypt($_POST["password"], $nonceValue);

if ($decryptedPassword === null) {
    echo "Error: Invalid encrypted password";
    return;
}

$person->setPassword($decryptedPassword);

if ($_SESSION['user'] != $person->getUsername()) {
    echo "User is logged in with different username and password";
    return;
}

$query = "SELECT * FROM login WHERE username='" . mysqli_real_escape_string($con, $person->getUsername()) . "'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "Error: Password or Username is incorrect";
    return;
}

$dbHashedPassword = $row['password'];

if (password_verify($person->getPassword(), $dbHashedPassword)) {
    $DCP = new DCP;
    $DCP->setUniqueid($_POST['uid']);

    if ($_POST['uid'] == "all") {
        $query = $DCP->deleteall($DCP);
        $log_name = "all";
    } else {
        $query = $DCP->delete($DCP);
        $log_query = $DCP->logname($DCP);
        $log_result = mysqli_query($con, $log_query);
        $log_name = "unknown";
        if ($log_result && $log_row = $log_result->fetch_assoc()) {
            $log_name = $log_row['name'];
        }
    }

    mysqli_query($con, $query);
    mysqli_close($con);

    $filteredPost = $_POST;
    unset($filteredPost['username'], $filteredPost['password']);

    writeLog(
        "User -> Mill deleted -> " . $_SESSION['user'] .
        " | Requested JSON -> " . json_encode($filteredPost) .
        " | " . $log_name
    );

    echo "<script>window.location.href = '../Mill.php';</script>";
} else {
    echo "Error : Password or Username is incorrect";
}
