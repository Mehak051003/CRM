<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch user details
$email = $_SESSION['user'];
$message = '';
$stmt = $conn->prepare("SELECT name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$name = isset($user['name']) ? htmlspecialchars($user['name']) : 'Name not found';
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'Email not found';


// Fetch all users, team leaders, and project managers
$userQuery = "SELECT name, email, role, designation FROM users WHERE role != 'admin'";
$userResult = $conn->query($userQuery);

$users = [];
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    $message = "Error fetching users: " . $conn->error;
}

$conn->close();
?>

<?php include 'header.php'; ?>

<style>

    .time-in-button:hover {
        background-color: #2980b9;
    }

    .main-content {
        margin-left: 120px;
        margin-top: 30px;
        padding: 20px;
    }

    .message {
        text-align: center;
        color: #28a745;
        font-weight: bold;
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .user-table th, .user-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .user-table th {
        background-color: #f2f2f2;
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

<?php if ($message): ?>
    <p class="message"><?php echo $message; ?></p>
<?php endif; ?>



<div class="main-content">
    <div class="user-info-box">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    </div>

    <br>
    <h2>All Employees</h2>
    <table class="user-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Designation</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars($user['designation']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
