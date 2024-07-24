<?php
session_start();
include '../includes/db_connect.php';  

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'User') {
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

$query_projects = "
    SELECT p.*
    FROM project p
    JOIN users u ON 
        (u.id = p.project_manager_id OR
         u.id = p.team_leader_id OR
         u.id = p.developer_id OR
         u.id = p.designer_id OR
         u.id = p.qa_id OR
         u.id = p.seo_id OR
         u.id = p.sysadmin_id)
    WHERE u.id = ?
";

$stmt_projects = $conn->prepare($query_projects);
$stmt_projects->bind_param("i", $user_id);
$stmt_projects->execute();
$result_projects = $stmt_projects->get_result();
$stmt_projects->close();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['timein'])) {
    date_default_timezone_set('Asia/Kolkata'); 
    $current_date = date('Y-m-d');
    $current_time = date('Y-m-d H:i:s');

    $query_check = "SELECT * FROM timein WHERE email = ? AND timein_date = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param('ss', $email, $current_date);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $message = "You have already timed in today at " . date('H:i:s', strtotime($row['timein_time'])) . ".";
    } else {
        $insert_query = "INSERT INTO timein (email, timein_time, timein_date) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param('sss', $email, $current_time, $current_date);

        if ($stmt_insert->execute()) {
            $message = "Time in recorded successfully at " . date('H:i:s', strtotime($current_time)) . ".";
        } else {
            $message = "Error recording time in: " . $conn->error;
        }

        $stmt_insert->close();
    }

    $stmt_check->close();
}

// Fetch approved leave requests
$stmt_leave_requests = $conn->prepare("
    SELECT start_date, end_date, purpose
    FROM leave_requests
    WHERE user_id = ? AND tl_response = 'Approved'
    ORDER BY start_date DESC
");
$stmt_leave_requests->bind_param("i", $user_id);
$stmt_leave_requests->execute();
$result_leave_requests = $stmt_leave_requests->get_result();
$approved_requests = [];
while ($row = $result_leave_requests->fetch_assoc()) {
    $approved_requests[] = $row;
}
$stmt_leave_requests->close();

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
        transition: background-color 0.3s ease;
    }

    .time-in-button:hover {
        background-color: #2980b9;
    }

    .profile {
        text-align: center;
        margin-top: 80px;
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

    .message {
        text-align: center;
        color: #28a745;
        font-weight: bold;
        margin-top: 20px;
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
        margin-bottom: 10px;
    }
</style>

<div class="main-content">
    <form action="" method="post">
        <button type="submit" name="timein" class="time-in-button">Time In</button>
    </form>
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if (!empty($approved_requests)): ?>
        <?php foreach ($approved_requests as $request): ?>
            <?php if ($current_date <= $request['end_date']): ?>
                <div class="notification">
                    <?php echo "Your leave request from " . $request['start_date'] . " to " . $request['end_date'] . " for " . " has been approved."; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="profile">
        <h2><?php echo "Name: " . $name; ?></h2>
        <p><?php echo "Email: " . $email; ?></p>
    </div>

    <div class="projects">
        <h2>Projects Assigned to You</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>Serial Number</th>
                <th>Project Name</th>
                <th>Allocated Hours</th>
                <th>Delivery Date</th>
            </tr>
            <?php
            $serial_number = 1;
            while ($row = $result_projects->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $serial_number++; ?></td>
                    <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['allocated_hours']); ?></td>
                    <td><?php echo htmlspecialchars($row['qc_delivery_date']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
