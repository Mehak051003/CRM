<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader'){
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

$name = $user['name'];
$current_email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $new_email = $_POST['email'];

    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE email = ?");
    $update_stmt->bind_param("sss", $name, $new_email, $email);

    if ($update_stmt->execute()) {
        $_SESSION['user'] = $new_email; // Update session email if it changed
        $message = "Profile updated successfully.";
        $email = $new_email; // Update the email variable
    } else {
        $message = "Error updating profile: " . $conn->error;
    }

    $update_stmt->close();
}

$conn->close();
?>

<?php include 'header.php'; ?>
<style>
    .main-content {
        margin-left: 120px;
        margin-top: 60px;
        padding: 20px;
    }
</style>
<div class="main-content">
    <h2>Edit Profile</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <form action="editprofile.php" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
        <br>
        <button type="submit">Save Changes</button>
    </form>
</div>

<?php include 'footer.php'; ?>
