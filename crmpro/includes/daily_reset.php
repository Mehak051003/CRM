<?php
// session_start();
include '../includes/db_connect.php';

// Check if the user is logged in and is a team leader
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id, last_reset_date FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    exit("User not found.");
}

$user_id = $user['id'];
$last_reset_date = $user['last_reset_date'];
$current_date = date('Y-m-d');

if ($last_reset_date !== $current_date) {
    // Reset report_send and permission_request
    $stmt_reset = $conn->prepare("UPDATE users SET report_send = 0, permission_request = 'Pending', last_reset_date = ? WHERE id = ?");
    $stmt_reset->bind_param("si", $current_date, $user_id);
    if ($stmt_reset->execute()) {
        $message = "Daily reset completed successfully.";
    } else {
        $message = "Error during daily reset: " . $stmt_reset->error;
    }
    $stmt_reset->close();
}

//$conn->close();
?>
