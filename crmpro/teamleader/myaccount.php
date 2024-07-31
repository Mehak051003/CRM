<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];

$stmt = $conn->prepare("SELECT name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();


$name = isset($user['name']) ? htmlspecialchars($user['name']) : 'Name not found';
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'Email not found';

$conn->close();
?>

<?php include 'header.php'; ?>
<style>
    .main-content {
            margin-left: 120px;
            margin-top: 30px;
            padding: 20px;
        }
    </style>
<div class="main-content">
    <h2>My Account</h2>
    <p>Name: <?php echo $name; ?></p>
    <p>Email: <?php echo $email; ?></p>
</div>

<?php include 'footer.php'; ?>






