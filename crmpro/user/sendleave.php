<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'User') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];


$current_month = date('m');
$current_year = date('Y');
$stmt_leave_balance = $conn->prepare("
    SELECT SUM(duration) AS total_duration
    FROM leave_requests
    WHERE user_id = ? AND MONTH(start_date) = ? AND YEAR(start_date) = ?
");
$stmt_leave_balance->bind_param("iis", $user_id, $current_month, $current_year);
$stmt_leave_balance->execute();
$result_leave_balance = $stmt_leave_balance->get_result();
$total_duration = $result_leave_balance->fetch_assoc()['total_duration'] ?? 0;
$stmt_leave_balance->close();

$monthly_leave_limit = 1.5; 
$remaining_balance = $monthly_leave_limit - $total_duration;


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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_type = $_POST['leave_type'];
    $purpose = $_POST['purpose'];
    $address_during_leave = $_POST['address_during_leave'];
    $mobile_number = $_POST['mobile_number'];
    $project_id = $_POST['project_id'];

    
    $stmt_validate_project = $conn->prepare("
        SELECT COUNT(*) AS project_exists
        FROM project p
        JOIN users u ON 
            (u.id = p.project_manager_id OR
             u.id = p.team_leader_id OR
             u.id = p.developer_id OR
             u.id = p.designer_id OR
             u.id = p.qa_id OR
             u.id = p.seo_id OR
             u.id = p.sysadmin_id)
        WHERE p.id = ? AND u.id = ?
    ");
    $stmt_validate_project->bind_param("ii", $project_id, $user_id);
    $stmt_validate_project->execute();
    $result_validate_project = $stmt_validate_project->get_result();
    $project_exists = $result_validate_project->fetch_assoc()['project_exists'] ?? 0;
    $stmt_validate_project->close();

    if (!$project_exists) {
        $message = "You are not authorized to request leave for this project.";
    } else {
        // Calculate duration
        $datetime1 = new DateTime($start_date);
        $datetime2 = new DateTime($end_date);
        $interval = $datetime1->diff($datetime2);
        $duration = $interval->days + 1; // Include both start and end date

        // Insert leave request into database
        $stmt_insert_leave = $conn->prepare("
            INSERT INTO leave_requests (user_id, start_date, end_date, leave_type, duration, purpose, address_during_leave, mobile_number, project_id, tl_response, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW())
        ");
        $stmt_insert_leave->bind_param("isssisssi", $user_id, $start_date, $end_date, $leave_type, $duration, $purpose, $address_during_leave, $mobile_number, $project_id);

        if ($stmt_insert_leave->execute()) {
            $message = "Leave request submitted successfully!";
        } else {
            $message = "Error: " . $stmt_insert_leave->error;
        }

        $stmt_insert_leave->close();
    }
}

$conn->close();
?>

<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Leave Request</title>
    <style>
        /* Add your CSS styles for form, messages, etc. here */
        .main-content {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="date"],
        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button[type="submit"],
        button[type="reset"] {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button[type="submit"]:hover,
        button[type="reset"]:hover {
            background-color: #45a049;
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
        .leave-balance {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="main-content">
    <!--
    <div class="leave-balance">
        <strong>Your Leave Balance for <?php echo date('F Y'); ?>:</strong>
        <p>Total Allowed: <?php echo $monthly_leave_limit; ?> days</p>
        <p>Total Used: <?php echo $total_duration; ?> days</p>
        <p>Remaining Balance: <?php echo $remaining_balance; ?> days</p>
    </div>
    -->
    <h2>Send Leave Request</h2>
    <?php if (!empty($message)) : ?>
        <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="sendleave.php">
        <div class="form-group">
            <label for="start_date">Req from (Date):</label>
            <input type="date" id="start_date" name="start_date" required>
        </div>
        <div class="form-group">
            <label for="end_date">Required to (Date):</label>
            <input type="date" id="end_date" name="end_date" required>
        </div>
        <div class="form-group">
            <label for="leave_type">Type:</label>
            <select id="leave_type" name="leave_type" required>
                <option value="1">Full Day</option>
                <option value="0.5">Half Day</option>
            </select>
        </div>
        <div class="form-group">
            <label for="purpose">Purpose:</label>
            <textarea id="purpose" name="purpose" required></textarea>
        </div>
        <div class="form-group">
            <label for="address_during_leave">Address during Leave:</label>
            <input type="text" id="address_during_leave" name="address_during_leave" required>
        </div>
        <div class="form-group">
            <label for="mobile_number">Mobile Number:</label>
            <input type="text" id="mobile_number" name="mobile_number" required>
        </div>
        <div class="form-group">
            <label for="project_id">Project Currently Working:</label>
            <select id="project_id" name="project_id" required>
                <option value="">Select Project</option>
                <?php while ($row = $result_projects->fetch_assoc()) : ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['project_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
    </form>
</div>

</body>
</html>
<?php include 'footer.php'; ?>
