<?php
session_start();
include '../includes/db_connect.php';
include '../timeout_check.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader') {
    header('Location: ../login.php');
    exit();
}

$team_leader_id = $_SESSION['user_id'];

$stmt_leave_requests = $conn->prepare("
    SELECT lr.id, lr.user_id, u.name AS user_name, lr.start_date, lr.end_date, lr.leave_type, lr.duration, lr.purpose, lr.address_during_leave, lr.mobile_number, lr.project_id, lr.tl_response, lr.created_at
    FROM leave_requests lr
    JOIN users u ON lr.user_id = u.id
    WHERE u.team_leader_id = ?
");
$stmt_leave_requests->bind_param("i", $team_leader_id);
$stmt_leave_requests->execute();
$result_leave_requests = $stmt_leave_requests->get_result();
$stmt_leave_requests->close();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'];
    $response = $_POST['response'];

    $stmt_update_leave = $conn->prepare("
        UPDATE leave_requests
        SET tl_response = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt_update_leave->bind_param("si", $response, $leave_id);

    if ($stmt_update_leave->execute()) {
        $message = "Leave request updated successfully!";
    } else {
        $message = "Error: " . $stmt_update_leave->error;
    }

    $stmt_update_leave->close();
}

$conn->close();
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests </title>
    <style>
        .main-content {
            max-width: 1500px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);        
            margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
    
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="main-content">
    <h2>Leave Requests</h2>
    <?php if (!empty($message)) : ?>
        <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Leave Type</th>
                <th>Duration (days)</th>
                <th>Purpose</th>
                <th>Address during Leave</th>
                <th>Mobile Number</th>
                <th>Project ID</th>
                <th>Created At</th>
                <th>Response</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_leave_requests->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $row['user_name']; ?></td>
                    <td><?php echo $row['start_date']; ?></td>
                    <td><?php echo $row['end_date']; ?></td>
                    <td><?php echo ($row['leave_type'] == 1) ? 'Full Day' : 'Half Day'; ?></td>
                    <td><?php echo $row['duration']; ?></td>
                    <td><?php echo $row['purpose']; ?></td>
                    <td><?php echo $row['address_during_leave']; ?></td>
                    <td><?php echo $row['mobile_number']; ?></td>
                    <td><?php echo $row['project_id']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><?php echo $row['tl_response']; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="leave_id" value="<?php echo $row['id']; ?>">
                            <select name="response" <?php echo ($row['tl_response'] != 'Pending') ? 'disabled' : ''; ?>>
                                <option value="Approved" <?php echo ($row['tl_response'] == 'Approved') ? 'selected' : ''; ?>>Approve</option>
                                <option value="Rejected" <?php echo ($row['tl_response'] == 'Rejected') ? 'selected' : ''; ?>>Reject</option>
                            </select>
                            <button type="submit" <?php echo ($row['tl_response'] != 'Pending') ? 'disabled' : ''; ?>>Submit</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php include 'footer.php'; ?>
