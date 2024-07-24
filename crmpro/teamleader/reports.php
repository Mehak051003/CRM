<?php
session_start();
include '../includes/db_connect.php';  

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader') {
    header('Location: ../login.php');
    exit();
}


$email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$user_id = $user['id'];


$query_projects = "SELECT project_name FROM projects WHERE user_id = ?";
$stmt_projects = $conn->prepare($query_projects);
$stmt_projects->bind_param("i", $user_id);
$stmt_projects->execute();
$result_projects = $stmt_projects->get_result();
$stmt_projects->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project = $_POST['project'];
    $hours = $_POST['hours'];
    $task_list = $_POST['task_list'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'];
    $date = date('Y-m-d');
    
    $stmt_report = $conn->prepare("INSERT INTO reports (user_id, user_name, project, hours, task_list, time_in, time_out, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_report->bind_param("isssssss", $user_id, $user['name'], $project, $hours, $task_list, $time_in, $time_out, $date);
    $stmt_report->execute();
    $stmt_report->close();
    
    echo "<script>alert('Report submitted');</script>";
}
?>


<?php include 'header.php'; ?>

<div class="main-content">
    <form action="reports.php" method="POST">
        <h2>Submit Your Report</h2>
        <label for="project">Project:</label>
        <select name="project" required>
            <?php while ($row = $result_projects->fetch_assoc()) : ?>
                <option value="<?php echo $row['project_name']; ?>"><?php echo $row['project_name']; ?></option>
            <?php endwhile; ?>
        </select>
        
        <label for="hours">Hours Worked:</label>
        <input type="number" name="hours" required>
        
        <label for="task_list">Task List:</label>
        <textarea name="task_list" required></textarea>
        
        <label for="time_in">Time In:</label>
        <input type="time" name="time_in" required>
        
        <label for="time_out">Time Out:</label>
        <input type="time" name="time_out" required>
        
        <button type="submit">Submit</button>
    </form>
</div>


<?php include 'footer.php'; ?>

</body>
</html>
