<?php
session_start();
include '../includes/db_connect.php'; 
include '../timeout_check.php'; 

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'hr') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id, name, email, last_reset_date FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$user_id = $user['id'];
$last_reset_date = $user['last_reset_date'];
$current_date = date('Y-m-d');

if ($last_reset_date !== $current_date) {
    $stmt_reset = $conn->prepare("UPDATE users SET report_send = 0, permission_request = 'Pending', last_reset_date = ? WHERE id = ?");
    $stmt_reset->bind_param("si", $current_date, $user_id);
    if ($stmt_reset->execute()) {
        $message = "Daily reset completed successfully.";
    } else {
        $message = "Error during daily reset: " . $stmt_reset->error;
    }
    $stmt_reset->close();
}

$name = isset($user['name']) ? htmlspecialchars($user['name']) : 'Name not found';
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'Email not found';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timein'])) {
    date_default_timezone_set('Asia/Kolkata'); 
    $current_time = date('H:i');
    $time_limit = '10:00';
    $current_date = date('Y-m-d');
    $current_time_full = date('Y-m-d H:i');

    // Check if already timed in today
    $query_check = "SELECT * FROM timein WHERE email = ? AND timein_date = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param('ss', $email, $current_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $message = "You have already timed in today at " . date('H:i', strtotime($result_check->fetch_assoc()['timein_time']));
    } else {
        if ($current_time > $time_limit) {
            // Check if permission has been granted
            $stmt_perm_check = $conn->prepare("SELECT status FROM permission_requests WHERE user_id = ? AND request_date = ? ORDER BY id DESC LIMIT 1");
            $stmt_perm_check->bind_param("is", $user_id, $current_date);
            $stmt_perm_check->execute();
            $result_perm_check = $stmt_perm_check->get_result();
            $perm_request = $result_perm_check->fetch_assoc();
            $stmt_perm_check->close();

            if ($perm_request['status'] !== 'Approved') {
                // Insert permission request
                $stmt_insert_permission = $conn->prepare("INSERT INTO permission_requests (user_id, request_date, request_time,status) VALUES (?, ?, ?, 'Pending')");
                $stmt_insert_permission->bind_param("iss", $user_id, $current_date,$current_time_full);
                if ($stmt_insert_permission->execute()) {
                    $message = "You need to get permission from the admin to time in after 10:00 AM. A permission request has been submitted.";
                } else {
                    $message = "Error submitting permission request: " . $stmt_insert_permission->error;
                }
                $stmt_insert_permission->close();
            } else {
                // Insert time-in record if permission is granted
                $insert_query = "INSERT INTO timein (email, timein_time, timein_date) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($insert_query);
                $stmt_insert->bind_param('sss', $email, $current_time_full, $current_date);
                if ($stmt_insert->execute()) {
                    $message = "Time in recorded successfully at " . date('H:i', strtotime($current_time_full));
                } else {
                    $message = "Error recording time in: " . $conn->error;
                }
                $stmt_insert->close();
            }
        } else {
            // Insert time-in record if within time limit
            $insert_query = "INSERT INTO timein (email, timein_time, timein_date) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_query);
            $stmt_insert->bind_param('sss', $email, $current_time_full, $current_date);
            if ($stmt_insert->execute()) {
                $message = "Time in recorded successfully at " . date('H:i', strtotime($current_time_full));
            } else {
                $message = "Error recording time in: " . $conn->error;
            }
            $stmt_insert->close();
        }
    }

    $stmt_check->close();
}



$conn->close();
?>

<?php include 'header.php'; ?>

<style>
    .time-in-button {
        padding: 10px 20px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        position: absolute;
        top: 100px;
        left: 275px;
    }

    .time-in-button:hover {
        background-color: #2980b9;
    }

    .main-content {
            margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
        }
    .message {
        text-align: center;
        color: #28a745;
        font-weight: bold;
    }

    .projects {
        margin-top: 40px;
    }

    .projects table {
        width: 100%;
        border-collapse: collapse;
    }

    .projects th, .projects td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .projects th {
        background-color: #f4f4f4;
    }

    .notification {
        text-align: left;
        color: #27ae60;
        font-weight: bold;
        margin-top: 20px;
        background-color: #eafaf1;
        padding: 10px;
        border: 1px solid #27ae60;
        border-radius: 4px;
        width: 600px;
        margin-bottom: 30px;
    }
    .user-info-box {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        background-color: #f9f9f9;
        margin-top: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .user-info-box p {
        margin: 0;
        padding: 5px 0;
    }
</style>

<div class="main-content">
    <form action="" method="post">
        <button type="submit" name="timein" class="time-in-button">Time In</button>
    </form>
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>


    <div class="user-info-box">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    </div>


</div>

<?php include 'footer.php'; ?>
