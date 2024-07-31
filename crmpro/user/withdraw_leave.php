<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the leave request belongs to the user
    $stmt_check = $conn->prepare("
        SELECT id 
        FROM leave_requests 
        WHERE id = ? AND user_id = ?
    ");
    $stmt_check->bind_param("ii", $leave_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Delete the leave request
        $stmt_delete = $conn->prepare("
            DELETE FROM leave_requests 
            WHERE id = ?
        ");
        $stmt_delete->bind_param("i", $leave_id);
        if ($stmt_delete->execute()) {
            $_SESSION['message'] = 'Leave request successfully withdrawn.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to withdraw leave request. Please try again.';
            $_SESSION['message_type'] = 'error';
        }
        $stmt_delete->close();
    } else {
        $_SESSION['message'] = 'Leave request not found or does not belong to you.';
        $_SESSION['message_type'] = 'error';
    }
    $stmt_check->close();
}

$conn->close();

header('Location: myleave.php');
exit();
?>
