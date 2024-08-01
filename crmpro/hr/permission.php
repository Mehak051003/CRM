<?php
session_start();
include '../includes/db_connect.php';  
include '../timeout_check.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'hr') {
    header('Location: ../login.php');
    exit();
}

// Fetch permission requests
$query_permission_requests = "
    SELECT pr.id, u.name, u.email, pr.request_date, pr.status
    FROM permission_requests pr
    JOIN users u ON pr.user_id = u.id
    WHERE pr.status = 'Pending' and u.role!='hr'
    ORDER BY pr.request_date DESC
";

$result_permission_requests = $conn->query($query_permission_requests);

// Approve permission request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $request_id = intval($_POST['request_id']);
    
    $stmt_update = $conn->prepare("UPDATE permission_requests SET status = 'Approved' WHERE id = ?");
    $stmt_update->bind_param("i", $request_id);
    if ($stmt_update->execute()) {
        $message = "Permission granted successfully.";
    } else {
        $message = "Error granting permission: " . $stmt_update->error;
    }
    $stmt_update->close();
    
    // Redirect to avoid resubmission
    header("Location: permission.php");
    exit();
}

$conn->close();
?>

<?php include 'header.php'; ?>

<style>
    .permission-table {
        width: 100%;
        border-collapse: collapse;
    }

    .permission-table th, .permission-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .permission-table th {
        background-color: #f4f4f4;
    }

    .approve-button {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    .approve-button:hover {
        background-color: #218838;
    }

    .message {
        text-align: center;
        color: #28a745;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .main-content {
            margin-left: 120px;
            margin-top: 40px;
            padding: 20px;
        }
</style>

<div class="main-content">
    <?php if (isset($message) && $message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <h2>Permission Requests</h2>
    <table class="permission-table">
        <tr>
            <th>Request ID</th>
            <th>User Name</th>
            <th>User Email</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result_permission_requests->fetch_assoc()) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <button type="submit" name="approve" class="approve-button">Approve</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>
