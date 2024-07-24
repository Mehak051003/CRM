<?php
session_start();
include '../includes/db_connect.php';
include '../includes/daily_reset.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'User') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id, report_send, permission_request FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    exit("User not found.");
}

$user_id = $user['id'];
$report_send = $user['report_send'];
$permission_request = $user['permission_request'];

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

date_default_timezone_set('Asia/Kolkata'); 
$date_today = date('Y-m-d');
$stmt_check_report = $conn->prepare("SELECT * FROM morningreports WHERE user_id = ? AND DATE(report_date) = ?");
$stmt_check_report->bind_param("is", $user_id, $date_today);
$stmt_check_report->execute();
$result_check_report = $stmt_check_report->get_result();
$report_exists = $result_check_report->num_rows > 0;
$stmt_check_report->close();

$message = '';
$show_content = true; 

if (!isset($_SESSION['added_records'])) {
    $_SESSION['added_records'] = [];
}

$current_time = date('H:i');
$time_limit = '11:00';

if ($current_time > $time_limit) {
    if ($report_send == 1) {
        $message = "You have already sent your morning report for today.";
        $show_content = false;
    } elseif ($permission_request == 'Approved') {
        $show_content = true;
    } else {
        $show_content = false; 
        $message = "Time limit exceeded. You cannot add or send reports after 11:00 AM without permission.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_record'])) {
        $project_id = $_POST['project_id'];
        $expected_hours = $_POST['expected_hours'];
        $task_list = $_POST['task_list'];

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
            'expected_hours' => $expected_hours,
            'task_list' => $task_list
        ];

        $_SESSION['added_records'][] = $added_record;
        $message = "Record added successfully!";
    } elseif (isset($_POST['send_report'])) {
        $time_in = $_POST['time_in'];
        $location = $_POST['location'];
        $added_records = $_SESSION['added_records'];

        foreach ($added_records as $record) {
            $stmt = $conn->prepare("INSERT INTO morningreports (user_id, project_id, expected_hours, task_list, time_in, location, report_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiisss", $user_id, $record['project_id'], $record['expected_hours'], $record['task_list'], $time_in, $location);

            if ($stmt->execute()) {
                $message = "Report sent successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        // Update the report_send status
        $stmt_update_report_send = $conn->prepare("UPDATE users SET report_send = 1 WHERE id = ?");
        $stmt_update_report_send->bind_param("i", $user_id);
        $stmt_update_report_send->execute();
        $stmt_update_report_send->close();

        $_SESSION['added_records'] = [];
        header('Location: morning.php');
        exit();
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
    color: red; /* Changed to red for time limit message */
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

#time_in {
    width: 80px;
}
</style>
<div class="main-content">
    <?php if (!empty($message)) : ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($show_content) : ?>
        <?php if ($report_exists) : ?>
            <div class="message">You have already sent your morning report for today.</div>
        <?php else : ?>
            <div class="form-container">
                <h2>Morning Status Report</h2>
                <form method="post" action="morning.php">
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
                        <label for="expected_hours">Expected Hours:</label>
                        <input type="number" id="expected_hours" name="expected_hours" required>
                    </div>

                    <div class="form-group">
                        <label for="task_list">Task List:</label>
                        <textarea id="task_list" name="task_list" required></textarea>
                    </div>

                    <button type="submit" name="add_record">Add Record</button>
                </form>
            </div>

            <div class="timein-container">
                <h3>Time In</h3>
                <form method="post" action="morning.php">
                    <div class="form-group">
                        <label for="time_in">Time In:</label>
                        <input type="time" id="time_in" name="time_in" max="12:00" required>
                        <small>Time in should not be greater than 12:00 PM</small>
                    </div>

                    <div class="form-group">
                        <label for="location">Location:</label>
                        <div class="radio-group">
                            <input type="radio" id="office" name="location" value="Office" required>
                            <label for="office">Work from Office</label>
                        </div>
                    </div>

                    <button type="submit" name="send_report">Send Report</button>
                </form>
            </div>

            <div class="report-display">
                <h3>Added Records</h3>
                <?php if (!empty($_SESSION['added_records'])) : ?>
                    <table>
                        <tr>
                            <th>Project Name</th>
                            <th>Expected Hours</th>
                            <th>Task List</th>
                        </tr>
                        <?php foreach ($_SESSION['added_records'] as $record) : ?>
                            <tr>
                                <td><?php echo $record['project_name']; ?></td>
                                <td><?php echo $record['expected_hours']; ?></td>
                                <td><?php echo $record['task_list']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
