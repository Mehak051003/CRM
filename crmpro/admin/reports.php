<?php
session_start();
include '../includes/db_connect.php';


if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'morning';
$selected_date = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');
$selected_user = isset($_POST['selected_user']) ? $_POST['selected_user'] : '';


$stmt_users = $conn->prepare("SELECT id, name, email FROM users");
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$stmt_users->close();

if ($report_type === 'morning') {
    $query_reports = "
        SELECT mr.*, u.name as user_name, u.email as user_email, p.project_name
        FROM morningreports mr
        JOIN users u ON mr.user_id = u.id
        JOIN project p ON mr.project_id = p.id
        WHERE DATE(mr.report_date) = ? AND (u.id = ? OR ? = '')
    ";
} else {
    $query_reports = "
        SELECT er.*, u.name as user_name, u.email as user_email, p.project_name
        FROM eveningreports er
        JOIN users u ON er.user_id = u.id
        JOIN project p ON er.project_id = p.id
        WHERE DATE(er.report_date) = ? AND (u.id = ? OR ? = '')
    ";
}

$stmt_reports = $conn->prepare($query_reports);
$stmt_reports->bind_param("sis", $selected_date, $selected_user, $selected_user);
$stmt_reports->execute();
$result_reports = $stmt_reports->get_result();
$stmt_reports->close();

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
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.form-container {
    margin-bottom: 20px;
}

h2 {
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
.form-group input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%;
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

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

th {
    background-color: #2c3e50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f4f4f4;
}

button[type="button"] {
    margin-right: 10px;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

button#morning-report {
    background-color: #3498db;
    color: white;
    border: none;
}

button#evening-report {
    background-color: #e74c3c;
    color: white;
    border: none;
}

button#morning-report:hover {
    background-color: #2980b9;
}

button#evening-report:hover {
    background-color: #c0392b;
}
</style>

<div class="main-content">
    <h2>Admin Reports</h2>

    <div class="form-container">
        <form method="post" action="reports.php">
            <div class="form-group">
                <label for="report_type">Select Report Type:</label>
                <select id="report_type" name="report_type">
                    <option value="morning" <?php echo ($report_type === 'morning') ? 'selected' : ''; ?>>Morning Report</option>
                    <option value="evening" <?php echo ($report_type === 'evening') ? 'selected' : ''; ?>>Evening Report</option>
                </select>
            </div>
            <div class="form-group">
                <label for="selected_date">Select Date:</label>
                <input type="date" id="selected_date" name="selected_date" value="<?php echo $selected_date; ?>">
            </div>
            <div class="form-group">
                <label for="selected_user">Select User:</label>
                <select id="selected_user" name="selected_user">
                    <option value="">All Users</option>
                    <?php while ($user = $result_users->fetch_assoc()) : ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($selected_user == $user['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit">Filter Reports</button>
        </form>
    </div>

    <div class="table-container">
        <?php if ($result_reports->num_rows > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>User Email</th>
                        <th>Project Name</th>
                        <?php if ($report_type === 'morning') : ?>
                            <th>Expected Hours</th>
                            <th>Task List</th>
                            <th>Time In</th>
                            <th>Location</th>
                            <th>Report Date</th>
                        <?php else : ?>
                            <th>Task Type</th>
                            <th>Hours Worked</th>
                            <th>Task List</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Location</th>
                            <th>Report Date</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($report = $result_reports->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($report['project_name']); ?></td>
                            <?php if ($report_type === 'morning') : ?>
                                <td><?php echo htmlspecialchars($report['expected_hours']); ?></td>
                                <td><?php echo htmlspecialchars($report['task_list']); ?></td>
                                <td><?php echo htmlspecialchars($report['time_in']); ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td><?php echo htmlspecialchars($report['report_date']); ?></td>
                            <?php else : ?>
                                <td><?php echo htmlspecialchars($report['task_type']); ?></td>
                                <td><?php echo htmlspecialchars($report['hours_worked']); ?></td>
                                <td><?php echo htmlspecialchars($report['task_list']); ?></td>
                                <td><?php echo htmlspecialchars($report['time_in']); ?></td>
                                <td><?php echo htmlspecialchars($report['time_out']); ?></td>
                                <td><?php echo htmlspecialchars($report['location']); ?></td>
                                <td><?php echo htmlspecialchars($report['report_date']); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No reports found for the selected criteria.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
