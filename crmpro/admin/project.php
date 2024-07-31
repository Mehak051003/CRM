<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}


$clientQuery = "SELECT id, firstname,lastname FROM clients";
$clientResult = $conn->query($clientQuery);
$client = [];
while ($row = $clientResult->fetch_assoc()) {
    $client[] = $row;
}


$userQuery = "SELECT id, name, role, designation FROM users";
$userResult = $conn->query($userQuery);
$users = [];
while ($row = $userResult->fetch_assoc()) {
    $users[] = $row;
}

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
    

    $stmt = $conn->prepare("INSERT INTO project (project_name, project_designation, live_url, technology, job_terms, project_source, profile_hired, project_duration, allocated_hours, rate_per_hour, developer_hours, design_hours, qa_hours, seo_hours, sysadmin_hours, project_start_date, qc_delivery_date, project_end_date, project_manager_id, team_leader_id, associated_client, developer_id, designer_id, qa_id, seo_id, sysadmin_id, communication_mode, communication_done_as, project_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssssssiiiiiiisssiisiiiiisss",
        $project_name,$project_designation,$live_url,$technology,$job_terms,$project_source,$profile_hired,$project_duration,$allocated_hours,
        $rate_per_hour,$developer_hours,$design_hours,$qa_hours,$seo_hours,$sysadmin_hours,$project_start_date,$qc_delivery_date,$project_end_date,
        $project_manager_id,$team_leader_id,$associated_client,$developer_id,$designer_id,$qa_id,$seo_id,$sysadmin_id,$communication_mode,$communication_done_as,$project_status
    );

    if ($stmt->execute()) {
        $message = "Project added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<?php include 'header.php'; ?>

<style>
    .form-container {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 80%;
        margin: auto;
        margin-left:200px;
    }

    .form-container h2 {
        margin-bottom: 20px;
        text-align: center;
    }

    .form-container label {
        display: block;
        margin-bottom: 5px;
    }

    .form-container input[type="text"],
    .form-container input[type="number"],
    .form-container input[type="date"],
    .form-container select,
    .form-container textarea {
        width: 80%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #bdc3c7;
        border-radius: 4px;
    }

    .form-container button {
        width: 20%;
        padding: 10px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .form-container button:hover {
        background-color: #2980b9;
    }

    .message {
        margin: 20px 0;
        text-align: center;
    }
    .main-content {
            margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
        }
</style>

<div class="form-container">
    <h2>Add Project</h2>
    <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
    <form method="post" action="project.php">
        <label for="project_name">Project Name:</label>
        <input type="text" id="project_name" name="project_name" required>

        <label for="project_designation">Project Description:</label>
        <input type="text" id="project_designation" name="project_designation" required>

        <label for="live_url">Live URL:</label>
        <input type="text" id="live_url" name="live_url">

        <label for="technology">Technology:</label>
        <input type="text" id="technology" name="technology" required>

        <label for="job_terms">Job Terms:</label>
        <textarea id="job_terms" name="job_terms" required></textarea>

        <label for="project_source">Project Source:</label>
        <input type="text" id="project_source" name="project_source" required>

        <label for="profile_hired">Profile Hired:</label>
        <input type="text" id="profile_hired" name="profile_hired" required>

        <label for="project_duration">Project Duration:</label>
        <input type="text" id="project_duration" name="project_duration" required>

        <label for="allocated_hours">Allocated Hours:</label>
        <input type="number" id="allocated_hours" name="allocated_hours" required>

        <label for="rate_per_hour">Rate/Hour:</label>
        <input type="number" id="rate_per_hour" name="rate_per_hour" required>

        <label for="developer_hours">Developer Hours:</label>
        <input type="number" id="developer_hours" name="developer_hours" required>

        <label for="design_hours">Design Hours:</label>
        <input type="number" id="design_hours" name="design_hours" required>

        <label for="qa_hours">QA Hours:</label>
        <input type="number" id="qa_hours" name="qa_hours" required>

        <label for="seo_hours">SEO Hours:</label>
        <input type="number" id="seo_hours" name="seo_hours" required>

        <label for="sysadmin_hours">Sysadmin Hours:</label>
        <input type="number" id="sysadmin_hours" name="sysadmin_hours" required>

        <label for="project_start_date">Project Start Date:</label>
        <input type="date" id="project_start_date" name="project_start_date" required>

        <label for="qc_delivery_date">QC Delivery Date:</label>
        <input type="date" id="qc_delivery_date" name="qc_delivery_date" required>

        <label for="project_end_date">Project End Date:</label>
        <input type="date" id="project_end_date" name="project_end_date" required>

        <label for="project_manager_id">Select Project Manager:</label>
        <select id="project_manager_id" name="project_manager_id" required>
            <option value="">Select Project Manager</option>
            <?php foreach ($users as $user): ?>
                <?php if ($user['role'] == 'Project Manager'): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="team_leader_id">Select Team Leader:</label>
        <select id="team_leader_id" name="team_leader_id" required>
            <option value="">Select Team Leader</option>
            <?php foreach ($users as $user): ?>
                <?php if ($user['role'] == 'Team Leader'): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="associated_client">Associated Client:</label>
        <select id="associated_client" name="associated_client" required>
            <option value="">Select Associated Client</option>
            <?php foreach ($client as $cl): ?>
                <option value="<?php echo $cl['id']; ?>"><?php echo $cl['firstname'] . ' ' . $cl['lastname']; ?></option>
            <?php endforeach; ?>
        </select>


        <label for="developer_id">Select Developer:</label>
        <select id="developer_id" name="developer_id" required>
            <option value="">Select Developer</option>
            <?php foreach ($users as $user): ?>
                <?php if ($user['designation'] == 'Developer'): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="designer_id">Select Designer:</label>
        <select id="designer_id" name="designer_id" required>
            <option value="">Select Designer</option>
            <?php foreach ($users as $user): ?>
                <?php if ($user['designation'] == 'Designer'): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="qa_id">Select QA:</label>
        <select id="qa_id" name="qa_id" required>
            <option value="">Select QA</option>
            <?php foreach ($users as $user): ?>
                <?php if ($user['designation'] == 'QA'): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="seo_id">Select SEO:</label>
        <select id="seo_id" name="seo_id" required>
            <option value="">Select SEO</option>
            <?php foreach ($users as $user): ?>
                <?php if ($user['designation'] == 'SEO'): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="sysadmin_id">Select Sysadmin:</label>
        <select id="sysadmin_id" name="sysadmin_id" required>
            <option value="">Select Sysadmin</option>
            <?php foreach ($users as $user): ?>
                <?php if ($user['designation'] == 'Sysadmin'): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="communication_mode">Communication Mode:</label>
        <input type="text" id="communication_mode" name="communication_mode" required>

        <label for="communication_done_as">Communication Done As:</label>
        <input type="text" id="communication_done_as" name="communication_done_as" required>

        <label for="project_status">Select Project Status:</label>
        <select id="project_status" name="project_status" required>
            <option value="">Select Project Status</option>
            <option value="Ongoing">Ongoing</option>
            <option value="Completed">Completed</option>
            <option value="On Hold">On Hold</option>
        </select>
        <center>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
        </center>
    </form>
</div>

<?php include 'footer.php'; ?>
