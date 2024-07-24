<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$stmt_leave_requests = $conn->prepare("
    SELECT id, start_date, end_date, leave_type, duration, purpose, address_during_leave, mobile_number, project_id, tl_response, created_at
    FROM leave_requests
    WHERE user_id = ?
    ORDER BY start_date DESC
");
$stmt_leave_requests->bind_param("i", $user_id);
$stmt_leave_requests->execute();
$result_leave_requests = $stmt_leave_requests->get_result();
$stmt_leave_requests->close();

$conn->close();
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Leave Requests</title>
    <style>
    
        .main-content {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
    <h2>My Leave Requests</h2>
    <?php if ($result_leave_requests->num_rows > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Leave Type</th>
                    <th>Duration (days)</th>
                    <th>Purpose</th>
                    <th>Address during Leave</th>
                    <th>Mobile Number</th>
                    <th>Project ID</th>
                    <th>TL Response</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_leave_requests->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $row['start_date']; ?></td>
                        <td><?php echo $row['end_date']; ?></td>
                        <td><?php echo ($row['leave_type'] == 1) ? 'Full Day' : 'Half Day'; ?></td>
                        <td><?php echo $row['duration']; ?></td>
                        <td><?php echo $row['purpose']; ?></td>
                        <td><?php echo $row['address_during_leave']; ?></td>
                        <td><?php echo $row['mobile_number']; ?></td>
                        <td><?php echo $row['project_id']; ?></td>
                        <td><?php echo $row['tl_response']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="message">You have no leave requests submitted.</div>
        <table>
            <thead>
                <tr>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Leave Type</th>
                    <th>Duration (days)</th>
                    <th>Purpose</th>
                    <th>Address during Leave</th>
                    <th>Mobile Number</th>
                    <th>Project ID</th>
                    <th>TL Response</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <!-- Placeholder row or empty -->
                <tr>
                    <td colspan="10">No leave requests found.</td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
<?php include 'footer.php'; ?>
