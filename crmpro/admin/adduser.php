<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = ($_POST['password']);
    $role = $_POST['role'];
    $designation = $_POST['designation'];
    $team_leader_id = $_POST['team_leader'];
    $project_manager_id = $_POST['project_manager'];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, designation, team_leader_id, project_manager_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $password, $role, $designation, $team_leader_id, $project_manager_id);

    if ($stmt->execute()) {
        $message = "User added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$team_leaders = $conn->query("SELECT id, name FROM users WHERE role = 'Team Leader'");
$project_managers = $conn->query("SELECT id, name FROM users WHERE role = 'Project Manager'");

$conn->close();
?>

<?php include 'header.php'; ?>

<style>
    .form-container {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 300px;
        margin: auto;
    }

    .form-container h2 {
        margin-bottom: 20px;
        text-align: center;
    }

    .form-container input[type="text"],
    .form-container input[type="email"],
    .form-container input[type="password"],
    .form-container select {
        width: 90%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #bdc3c7;
        border-radius: 4px;
    }

    .form-container button {
        width: 100%;
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
</style>

<div class="form-container">
    <h2>Add User</h2>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    <form method="post" action="adduser.php">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="User">User</option>
            <option value="Team Leader">Team Leader</option>
            <option value="Project Manager">Project Manager</option>
        </select>
        <select name="designation" required>
            <option value="">Select Designation</option>
            <option value="Developer">Developer</option>
            <option value="Designer">Designer</option>
            <option value="QA">QA</option>
            <option value="SEO">SEO</option>
            <option value="Sysadmin">System Admin</option>
        </select>
        <select name="team_leader" required>
            <option value="">Select Team Leader</option>
            <?php while ($tl = $team_leaders->fetch_assoc()) : ?>
                <option value="<?php echo $tl['id']; ?>"><?php echo $tl['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <select name="project_manager" required>
            <option value="">Select Project Manager</option>
            <?php while ($pm = $project_managers->fetch_assoc()) : ?>
                <option value="<?php echo $pm['id']; ?>"><?php echo $pm['name']; ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Add User</button>
    </form>
</div>

<?php include 'footer.php'; ?>
