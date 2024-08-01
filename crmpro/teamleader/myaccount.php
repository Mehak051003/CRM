<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'Team Leader') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];

// Prepare and execute query to fetch user details
$stmt = $conn->prepare("SELECT name, email, role, designation, gender, dob, image FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$name = isset($user['name']) ? htmlspecialchars($user['name']) : 'Name not found';
$email = isset($user['email']) ? htmlspecialchars($user['email']) : 'Email not found';
$role = isset($user['role']) ? htmlspecialchars($user['role']) : 'Role not found';
$designation = isset($user['designation']) ? htmlspecialchars($user['designation']) : 'Designation not found';
$gender = isset($user['gender']) ? htmlspecialchars($user['gender']) : 'Gender not found';
$dob = isset($user['dob']) ? htmlspecialchars($user['dob']) : 'DOB not found';
$image = isset($user['image']) ? htmlspecialchars($user['image']) : 'default.jpg'; // Fallback image if none is found

$conn->close();
?>

<?php include 'header.php'; ?>
<style>
    .main-content {
        margin-left: 120px;
        margin-top: 30px;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        height: calc(100vh - 60px);
    }

    .user-box {
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        width: 500px;
        max-width: 600px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .profile-image {
        border-radius: 50%;
        width: 150px;
        height: 150px;
        object-fit: cover;
        margin-bottom: 20px;
    }

    .user-info {
        width: 100%;
    }

    .user-info table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .user-info th, .user-info td {
        padding: 10px;
        text-align: left;
    }

    .user-info th {
        background-color: #2c3e50;
        color: white;
        font-size: 16px;
    }

    .user-info td {
        background-color: #f1f1f1;
        font-size: 16px;
    }

    .user-info td:first-child {
        border-right: 2px solid #2c3e50; /* Vertical line between columns */
    }

    .edit-button {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        background-color: #007bff;
        color: white;
        font-size: 16px;
        cursor: pointer;
        text-decoration: none;
    }

    .edit-button:hover {
        background-color: #0056b3;
    }
</style>
<div class="main-content">
    <div class="user-box">
        <img src="../uploads/<?php echo $image; ?>" alt="Profile Image" class="profile-image">
        <div class="user-info">
            <table>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><?php echo $name; ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo $email; ?></td>
                </tr>
                <tr>
                    <td>Role</td>
                    <td><?php echo $role; ?></td>
                </tr>
                <tr>
                    <td>Designation</td>
                    <td><?php echo $designation; ?></td>
                </tr>
                <tr>
                    <td>Gender</td>
                    <td><?php echo $gender; ?></td>
                </tr>
                <tr>
                    <td>Date of Birth</td>
                    <td><?php echo $dob; ?></td>
                </tr>
            </table>
        </div>
        <a href="editprofile.php" class="edit-button">Edit Profile</a>
    </div>
</div>

<?php include 'footer.php'; ?>