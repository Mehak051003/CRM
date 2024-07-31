<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];

// Get the Team Leader ID from the session
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$team_leader_id = $user['id'];
$stmt->close();

// Query to get projects assigned to the Team Leader
$query = "SELECT * FROM project WHERE team_leader_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $team_leader_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<?php include 'header.php'; ?>

<style>
    .main-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 20px auto;
      
    }

    .main-content {
        width: 2900px;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-left: 120px;
        margin-top: 60px;
        padding: 20px;
    }

    table {
        width: 2700px;
        border-collapse: collapse;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th, td {
        padding: 10px;
        text-align: right;
    }

    th {
        background-color: #f2f2f2;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .edit-button {
        padding: 5px 10px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .edit-button:hover {
        background-color: #2980b9;
    }
</style>

<div class="main-content">
    <h2>View Projects</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Edit</th>
            <th>Project ID</th>
            <th>Project Name</th>
            <th>Project Description</th>
            <th>Live URL</th>
            <th>Technology</th>
            <th>Job Terms</th>
            <th>Project Source</th>
            <th>Profile Hired</th>
            <th>Project Duration</th>
            <th>Allocated Hours</th>
            <th>Rate per Hour</th>
            <th>Developer Hours</th>
            <th>Design Hours</th>
            <th>QA Hours</th>
            <th>SEO Hours</th>
            <th>Sysadmin Hours</th>
            <th>Project Start Date</th>
            <th>QC Delivery Date</th>
            <th>Project End Date</th>
            <th>Project Manager ID</th>
            <th>Team Leader ID</th>
            <th>Associated Client</th>
            <th>Developer ID</th>
            <th>Designer ID</th>
            <th>QA ID</th>
            <th>SEO ID</th>
            <th>Sysadmin ID</th>
            <th>Communication Mode</th>
            <th>Communication Done As</th>
            <th>Project Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <a href="edit_project.php?id=<?php echo $row['id']; ?>" class="edit-button">Edit</a>
                </td>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['project_name']; ?></td>
                <td><?php echo $row['project_designation']; ?></td>
                <td><?php echo $row['live_url']; ?></td>
                <td><?php echo $row['technology']; ?></td>
                <td><?php echo $row['job_terms']; ?></td>
                <td><?php echo $row['project_source']; ?></td>
                <td><?php echo $row['profile_hired']; ?></td>
                <td><?php echo $row['project_duration']; ?></td>
                <td><?php echo $row['allocated_hours']; ?></td>
                <td><?php echo $row['rate_per_hour']; ?></td>
                <td><?php echo $row['developer_hours']; ?></td>
                <td><?php echo $row['design_hours']; ?></td>
                <td><?php echo $row['qa_hours']; ?></td>
                <td><?php echo $row['seo_hours']; ?></td>
                <td><?php echo $row['sysadmin_hours']; ?></td>
                <td><?php echo $row['project_start_date']; ?></td>
                <td><?php echo $row['qc_delivery_date']; ?></td>
                <td><?php echo $row['project_end_date']; ?></td>
                <td><?php echo $row['project_manager_id']; ?></td>
                <td><?php echo $row['team_leader_id']; ?></td>
                <td><?php echo $row['associated_client']; ?></td>
                <td><?php echo $row['developer_id']; ?></td>
                <td><?php echo $row['designer_id']; ?></td>
                <td><?php echo $row['qa_id']; ?></td>
                <td><?php echo $row['seo_id']; ?></td>
                <td><?php echo $row['sysadmin_id']; ?></td>
                <td><?php echo $row['communication_mode']; ?></td>
                <td><?php echo $row['communication_done_as']; ?></td>
                <td><?php echo $row['project_status']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php include 'footer.php'; ?>

<?php $conn->close(); ?>
