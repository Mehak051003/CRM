<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
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
        height: auto; /* Adjusted to fit content */
    }

    .profile-image {
        border-radius: 50%;
        width: 150px;
        height: 150px;
        object-fit: cover;
        margin-bottom: 15px;
    }

    .user-info p {
        margin: 10px 0;
        font-size: 16px;
    }

    .user-info strong {
        font-size: 18px;
        margin-bottom: 5px;
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
            <p><strong>Name:</strong> <?php echo $name; ?></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>Role:</strong> <?php echo $role; ?></p>
            <p><strong>Gender:</strong> <?php echo $gender; ?></p>
            <p><strong>Date of Birth:</strong> <?php echo $dob; ?></p>
        </div>
        <a href="editprofile.php" class="edit-button">Edit Profile</a>
    </div>
</div>

<?php include 'footer.php'; ?>
