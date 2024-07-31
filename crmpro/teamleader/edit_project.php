<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: editproject.php');
    exit();
}

$id = $_GET['id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = $_POST['project_name'];
    $project_designation = $_POST['project_designation'];
    $live_url = $_POST['live_url'];
    $technology = $_POST['technology'];
    $job_terms = $_POST['job_terms'];
    $project_source = $_POST['project_source'];
    $profile_hired = $_POST['profile_hired'];
    $project_duration = $_POST['project_duration'];
    $allocated_hours = $_POST['allocated_hours'];
    $rate_per_hour = $_POST['rate_per_hour'];
    $developer_hours = $_POST['developer_hours'];
    $design_hours = $_POST['design_hours'];
    $qa_hours = $_POST['qa_hours'];
    $seo_hours = $_POST['seo_hours'];
    $sysadmin_hours = $_POST['sysadmin_hours'];
    $project_start_date = $_POST['project_start_date'];
    $qc_delivery_date = $_POST['qc_delivery_date'];
    $project_end_date = $_POST['project_end_date'];
    $project_manager_id = $_POST['project_manager_id'];
    $team_leader_id = $_POST['team_leader_id'];
    $associated_client = $_POST['associated_client'];
    $developer_id = $_POST['developer_id'];
    $designer_id = $_POST['designer_id'];
    $qa_id = $_POST['qa_id'];
    $seo_id = $_POST['seo_id'];
    $sysadmin_id = $_POST['sysadmin_id'];
    $communication_mode = $_POST['communication_mode'];
    $communication_done_as = $_POST['communication_done_as'];
    $project_status = $_POST['project_status'];

    $stmt = $conn->prepare("UPDATE project SET project_name = ?, project_designation = ?, live_url = ?, technology = ?, job_terms = ?, project_source = ?, profile_hired = ?, project_duration = ?, allocated_hours = ?, rate_per_hour = ?, developer_hours = ?, design_hours = ?, qa_hours = ?, seo_hours = ?, sysadmin_hours = ?, project_start_date = ?, qc_delivery_date = ?, project_end_date = ?, project_manager_id = ?, team_leader_id = ?, associated_client = ?, developer_id = ?, designer_id = ?, qa_id = ?, seo_id = ?, sysadmin_id = ?, communication_mode = ?, communication_done_as = ?, project_status = ? WHERE id = ?");
    $stmt->bind_param("ssssssssidiiiiisssiisiiiiisssi", $project_name, $project_designation, $live_url, $technology, $job_terms, $project_source, $profile_hired, $project_duration, $allocated_hours, $rate_per_hour, $developer_hours, $design_hours, $qa_hours, $seo_hours, $sysadmin_hours, $project_start_date, $qc_delivery_date, $project_end_date, $project_manager_id, $team_leader_id, $associated_client, $developer_id, $designer_id, $qa_id, $seo_id, $sysadmin_id, $communication_mode, $communication_done_as, $project_status, $id);

    if ($stmt->execute()) {
        $message = "Project details updated successfully.";
    } else {
        $message = "Error updating project details: " . $conn->error;
    }

    $stmt->close();
}

$query = "SELECT * FROM project WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

?>



<!DOCTYPE html>
<html>
<head>
    <title>Edit Project</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .main-content {
            max-width: 2000px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #555555;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        p {
            text-align: center;
            color: #28a745;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .main-content {
                margin: 10px;
                padding: 15px;
            }

            input[type="text"],
            input[type="number"],
            input[type="date"],
            input[type="submit"] {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="main-content">
        <h2>Edit Project</h2>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <label for="project_name">Project Name:</label>
            <input type="text" name="project_name" value="<?php echo $project['project_name']; ?>"><br>
            
            <label for="project_designation">Project Description:</label>
            <input type="text" name="project_designation" value="<?php echo $project['project_designation']; ?>"><br>
            
            <label for="live_url">Live URL:</label>
            <input type="text" name="live_url" value="<?php echo $project['live_url']; ?>"><br>
            
            <label for="technology">Technology:</label>
            <input type="text" name="technology" value="<?php echo $project['technology']; ?>"><br>
            
            <label for="job_terms">Job Terms:</label>
            <input type="text" name="job_terms" value="<?php echo $project['job_terms']; ?>"><br>
            
            <label for="project_source">Project Source:</label>
            <input type="text" name="project_source" value="<?php echo $project['project_source']; ?>"><br>
            
            <label for="profile_hired">Profile Hired:</label>
            <input type="text" name="profile_hired" value="<?php echo $project['profile_hired']; ?>"><br>
            
            <label for="project_duration">Project Duration:</label>
            <input type="text" name="project_duration" value="<?php echo $project['project_duration']; ?>"><br>
            
            <label for="allocated_hours">Allocated Hours:</label>
            <input type="number" name="allocated_hours" value="<?php echo $project['allocated_hours']; ?>"><br>
            
            <label for="rate_per_hour">Rate per Hour:</label>
            <input type="number" name="rate_per_hour" value="<?php echo $project['rate_per_hour']; ?>"><br>
            
            <label for="developer_hours">Developer Hours:</label>
            <input type="number" name="developer_hours" value="<?php echo $project['developer_hours']; ?>"><br>
            
            <label for="design_hours">Design Hours:</label>
            <input type="number" name="design_hours" value="<?php echo $project['design_hours']; ?>"><br>
            
            <label for="qa_hours">QA Hours:</label>
            <input type="number" name="qa_hours" value="<?php echo $project['qa_hours']; ?>"><br>
            
            <label for="seo_hours">SEO Hours:</label>
            <input type="number" name="seo_hours" value="<?php echo $project['seo_hours']; ?>"><br>
            
            <label for="sysadmin_hours">Sysadmin Hours:</label>
            <input type="number" name="sysadmin_hours" value="<?php echo $project['sysadmin_hours']; ?>"><br>
            
            <label for="project_start_date">Project Start Date:</label>
            <input type="date" name="project_start_date" value="<?php echo $project['project_start_date']; ?>"><br>
            
            <label for="qc_delivery_date">QC Delivery Date:</label>
            <input type="date" name="qc_delivery_date" value="<?php echo $project['qc_delivery_date']; ?>"><br>
            
            <label for="project_end_date">Project End Date:</label>
            <input type="date" name="project_end_date" value="<?php echo $project['project_end_date']; ?>"><br>
            
            <label for="project_manager_id">Project Manager ID:</label>
            <input type="text" name="project_manager_id" value="<?php echo $project['project_manager_id']; ?>"><br>
            
            <label for="team_leader_id">Team Leader ID:</label>
            <input type="text" name="team_leader_id" value="<?php echo $project['team_leader_id']; ?>"><br>
            
            <label for="associated_client">Associated Client:</label>
            <input type="text" name="associated_client" value="<?php echo $project['associated_client']; ?>"><br>
            
            <label for="developer_id">Developer ID:</label>
            <input type="text" name="developer_id" value="<?php echo $project['developer_id']; ?>"><br>
            
            <label for="designer_id">Designer ID:</label>
            <input type="text" name="designer_id" value="<?php echo $project['designer_id']; ?>"><br>
            
            <label for="qa_id">QA ID:</label>
            <input type="text" name="qa_id" value="<?php echo $project['qa_id']; ?>"><br>
            
            <label for="seo_id">SEO ID:</label>
            <input type="text" name="seo_id" value="<?php echo $project['seo_id']; ?>"><br>
            
            <label for="sysadmin_id">Sysadmin ID:</label>
            <input type="text" name="sysadmin_id" value="<?php echo $project['sysadmin_id']; ?>"><br>
            
            <label for="communication_mode">Communication Mode:</label>
            <input type="text" name="communication_mode" value="<?php echo $project['communication_mode']; ?>"><br>
            
            <label for="communication_done_as">Communication Done As:</label>
            <input type="text" name="communication_done_as" value="<?php echo $project['communication_done_as']; ?>"><br>
            
            <label for="project_status">Project Status:</label>
            <input type="text" name="project_status" value="<?php echo $project['project_status']; ?>"><br>
            
            <input type="submit" value="Update Project">
        </form>
        
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>


