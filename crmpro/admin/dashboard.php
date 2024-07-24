<?php
session_start();
include '../includes/db_connect.php';

date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];
$message = '';

$stmt = $conn->prepare("SELECT name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timein'])) {
    $current_date = date('Y-m-d');
    $current_time = date('Y-m-d H:i:s');

    error_log("Current Date: $current_date");
    error_log("Current Time: $current_time");

    $query = "SELECT * FROM timein WHERE email = ? AND timein_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $message = "You have already timed in today at " . date('H:i:s', strtotime($row['timein_time'])) . ".";
    } else {
        $insert_query = "INSERT INTO timein (email, timein_time, timein_date) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('sss', $email, $current_time, $current_date);

        if ($insert_stmt->execute()) {
            $message = "Time in recorded successfully at " . date('H:i:s', strtotime($current_time)) . ".";
        } else {
            $message = "Error recording time in: " . $conn->error;
        }

        $insert_stmt->close();
    }

    $stmt->close();
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
        top: 20px;
        right: 20px;
    }

    .time-in-button:hover {
        background-color: #2980b9;
    }

    .profile {
        text-align: center;
        margin-top: 80px;
    }

    .message {
        text-align: center;
        color: #28a745;
        font-weight: bold;
    }
</style>

<?php if ($message): ?>
    <p class="message"><?php echo $message; ?></p>
<?php endif; ?>

<form action="" method="post">
    <button type="submit" name="timein" class="time-in-button">Time In</button>
</form>

<div class="profile">
    <h2><?php echo "Name: " . $user['name']; ?></h2>
    <p><?php echo "Email: " . $user['email']; ?></p>
</div>

<?php include 'footer.php'; ?>
