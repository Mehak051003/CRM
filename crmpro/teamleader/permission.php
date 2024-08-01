<?php
session_start();
include '../includes/db_connect.php';
include '../timeout_check.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = 'Team Leader'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$team_leader = $result->fetch_assoc();
$stmt->close();

if (!$team_leader) {
    exit("Team Leader not found.");
}

$team_leader_id = $team_leader['id'];

$stmt_users = $conn->prepare("
    SELECT id, name, email, report_send, permission_request
    FROM users
    WHERE team_leader_id = ?
");
$stmt_users->bind_param("i", $team_leader_id);
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$stmt_users->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allow_permission'])) {
    $user_id = $_POST['user_id'];

    $stmt_update = $conn->prepare("UPDATE users SET permission_request = 'Approved' WHERE id = ?");
    $stmt_update->bind_param("i", $user_id);
    if ($stmt_update->execute()) {
        $message = "Permission approved successfully!";
    } else {
        $message = "Error: " . $stmt_update->error;
    }
    $stmt_update->close();
}

$conn->close();
?>

<?php include 'header.php'; ?>
<style>

body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

.main-content {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

h2 {
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}

.message {
    color: green;
    margin-bottom: 20px;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table th, table td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: left;
}

table th {
    background-color: #2c3e50;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f4f4f4;
}

button[type="submit"] {
    background-color: #2c3e50;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #34495e;
}

</style>

<div class="main-content">
    <?php if (!empty($message)) : ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <h2>Permission Requests</h2>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Report Sent</th>
            <th>Permission Request</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result_users->fetch_assoc()) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo $row['report_send'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo htmlspecialchars($row['permission_request']); ?></td>
                <td>
                    <?php if ($row['permission_request'] == 'Pending') : ?>
                        <form method="post" action="permission.php">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="allow_permission">Allow</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
