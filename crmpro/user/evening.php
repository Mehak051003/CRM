<?php
session_start();
include '../includes/db_connect.php';

// Redirect to login page if user is not authenticated or not a User
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'User') {
    header('Location: ../login.php');
    exit();
}

// Fetch user details based on session email
$email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id , report_send FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user exists
if (!$user) {
    // Handle case where user is not found
    exit("User not found.");
}

$user_id = $user['id'];
$report_send = $user['report_send'];

// Fetch projects assigned to the user
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

// Check if the user has already submitted a report today
$date_today = date('Y-m-d');
$stmt_check_report = $conn->prepare("SELECT * FROM eveningreports WHERE user_id = ? AND DATE(report_date) = ?");
$stmt_check_report->bind_param("is", $user_id, $date_today);
$stmt_check_report->execute();
$result_check_report = $stmt_check_report->get_result();
$report_exists = $result_check_report->num_rows > 0;
$stmt_check_report->close();

// Initialize message variable
$message = '';

// Initialize added records in session if not already set
if (!isset($_SESSION['added_records_evening'])) {
    $_SESSION['added_records_evening'] = [];
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($report_send == 0) {
        $message = "You have not sent the morning report, so you cannot send the evening report.";
    } else {
    if (isset($_POST['add_record'])) {
        // Adding record logic
        $project_id = $_POST['project_id'];
        $task_type = $_POST['task_type'];
        $hours_worked = $_POST['hours_worked'];
        $task_list = $_POST['task_list'];
        $status = $_POST['status'];

        // Fetch project name based on project ID
        $stmt_project_name = $conn->prepare("SELECT project_name FROM project WHERE id = ?");
        $stmt_project_name->bind_param("i", $project_id);
        $stmt_project_name->execute();
        $result_project_name = $stmt_project_name->get_result();
        $project = $result_project_name->fetch_assoc();
        $stmt_project_name->close();

        $added_record = [
            'user_id' => $user_id,
            'project_id' => $project_id,
            'project_name' => $project['project_name'],
            'task_type' => $task_type,
            'hours_worked' => $hours_worked,
            'task_list' => $task_list,
            'status' => $status
        ];

        // Add record to session
        $_SESSION['added_records_evening'][] = $added_record;

        $message = "Record added successfully!";
    } elseif (isset($_POST['send_report'])) {
        // Sending report logic
        $time_in = $_POST['time_in'];
        $time_out = $_POST['time_out'];
        $location = $_POST['location'];
        $added_records = $_SESSION['added_records_evening'];

        foreach ($added_records as $record) {
            $stmt = $conn->prepare("INSERT INTO eveningreports (user_id, project_id, task_type, hours_worked, task_list, status, time_in, time_out, location, report_date) VALUES (?, ?, ?, ?, ?,?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiissssss", $user_id, $record['project_id'], $record['task_type'], $record['hours_worked'], $record['task_list'], $record['status'],$time_in, $time_out, $location);

            if ($stmt->execute()) {
                $message = "Report sent successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        // Clear added records after sending report
        $_SESSION['added_records_evening'] = [];

        // Redirect to prevent form resubmission
        header('Location: evening.php');
        exit();
    }
}
}

$conn->close();
?>

<?php include 'header.php'; ?>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.main-content {
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.form-container,
.timein-container,
.report-display {
    margin-bottom: 40px;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

h2, h3 {
    color: #333;
}

.message {
    color: green;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #555;
}

.form-group select,
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #888;
}

button[type="submit"] {
    background-color: #2c3e50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

button[type="submit"]:hover {
    background-color: #34495e;
}

.radio-group {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.radio-group input {
    margin-right: 10px;
}

.report-display table {
    width: 100%;
    border-collapse: collapse;
}

.report-display table,
.report-display th,
.report-display td {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: left;
}

.report-display th {
    background-color: #2c3e50;
    color: white;
}

.report-display tr:nth-child(even) {
    background-color: #f4f4f4;
}

.hidden {
    display: none;
}

#time_in,#time_out
{
    width: 80px;
}
</style>
<div class="main-content">
    <?php if (!empty($message)) : ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($report_send == 0) : ?>
        <div class="message">You have not sent the morning report, so you cannot send the evening report.</div>
    <?php else : ?>

    <?php if ($report_exists) : ?>
        <div class="message">You have already sent your evening report for today.</div>
    <?php else : ?>
        <div class="form-container">
            <h2>Evening Status Report</h2>
            <form method="post" action="evening.php">
                <div class="form-group">
                    <label for="project_id">Select Project:</label>
                    <select id="project_id" name="project_id" required>
                        <option value="">Select Project</option>
                        <?php while ($row = $result_projects->fetch_assoc()) : ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['project_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="task_type">Task Type:</label>
                    <select id="task_type" name="task_type" required>
                        <option value="">Select Task Type</option>
                        <option value="analysis">Analysis</option>
                        <option value="coding">Coding</option>
                        <option value="design">Design</option>
                        <option value="testing">Testing</option>
                        <option value="bug">Bug Fixing</option>
                        <!-- Add more task types as needed -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="hours_worked">Hours Worked:</label>
                    <input type="number" id="hours_worked" name="hours_worked" required>
                </div>

                <div class="form-group">
                    <label for="task_list">Task List:</label>
                    <textarea id="task_list" name="task_list" required></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Select Status:</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                        <!-- Add more task types as needed -->
                    </select>
                </div>

                <button type="submit" name="add_record">Add Record</button>
            </form>
        </div>

        <div class="timein-container">
            <h3>Time In/Out and Location</h3>
            <form method="post" action="evening.php">
                <div class="form-group">
                    <label for="time_in">Time In:</label>
                    <input type="time" id="time_in" name="time_in" required>
                    <small>Time in should not be greater than 12:00 PM</small>
                </div>

                <div class="form-group">
                    <label for="time_out">Time Out:</label>
                    <input type="time" id="time_out" name="time_out" required>
                    <small>Time out should not be greater than 06:00 PM</small>
                </div>

                <div class="radio-group">
                    <input type="radio" id="office" name="location" value="office" required>
                    <label for="office">Work from Office</label>
                </div>
                <br>
                <button type="submit" name="send_report">Send Report</button>
            </form>
        </div>

        <div class="report-display <?php echo $report_exists ? 'hidden' : ''; ?>">
            <h2>Today's Evening Report</h2>
            <table>
                <tr>
                    <th>Project Name</th>
                    <th>Task Type</th>
                    <th>Hours Worked</th>
                    <th>Task List</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($_SESSION['added_records_evening'] as $record) : ?>
                    <?php if (isset($record['user_id']) && $record['user_id'] == $user_id) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['project_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['task_type']); ?></td>
                            <td><?php echo htmlspecialchars($record['hours_worked']); ?></td>
                            <td><?php echo htmlspecialchars($record['task_list']); ?></td>
                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
