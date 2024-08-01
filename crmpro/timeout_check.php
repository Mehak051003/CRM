<?php
//session_start();

// Set timeout duration (10 minute = 600 seconds)
$timeout_duration = 600;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    
    session_unset(); 
    session_destroy(); 
    header('Location: ../login.php'); 
    exit();
}


$_SESSION['last_activity'] = time();
?>
