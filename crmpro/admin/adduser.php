<?php
session_start();
include '../includes/db_connect.php';
include '../timeout_check.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $designation = $_POST['designation'];
    $team_leader_id = $_POST['team_leader'];
    $project_manager_id = $_POST['project_manager'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];

    // Handle file upload
    $profile_image = '';
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is an image
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check === false) {
            $message = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["profile_image"]["size"] > 500000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $message = "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_image = basename($_FILES["profile_image"]["name"]);
            } else {
                $message = "Sorry, there was an error uploading your file.";
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, designation, team_leader_id, project_manager_id, gender, dob, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $name, $email, $password, $role, $designation, $team_leader_id, $project_manager_id, $gender, $dob, $profile_image);

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
        width: 500px;
        margin: auto;
    }

    .form-container h2 {
        margin-bottom: 20px;
        text-align: center;
    }

    .form-container input[type="text"],
    .form-container input[type="email"],
    .form-container input[type="password"],
    .form-container input[type="date"],
    .form-container select,
    .form-container input[type="file"] {
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
    .main-content{
        margin-left: 120px;
        margin-top: 60px;
        padding: 20px;
    }
</style>

<div class="form-container">
    <h2>Add User</h2>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    <form method="post" action="adduser.php" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="User">User</option>
            <option value="Team Leader">Team Leader</option>
            <option value="Project Manager">Project Manager</option>
            <option value="hr">HR</option>
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
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <input type="date" name="dob" placeholder="Date of Birth" required>
        <input type="file" name="profile_image" accept="image/*">
        <button type="submit">Add User</button>
    </form>
</div>

<?php include 'footer.php'; ?>
