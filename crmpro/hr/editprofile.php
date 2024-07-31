<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'hr') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];
$message = '';

// Fetch current user details
$stmt = $conn->prepare("SELECT name, email, dob, image, gender, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$name = $user['name'];
$current_email = $user['email'];
$current_dob = $user['dob'];
$current_image = $user['image'];
$current_gender = $user['gender'];
$current_role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $new_email = $_POST['email'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];

    // Handle file upload
    if ($_FILES['image']['error'] == 0) {
        $image = $_FILES['image']['name'];
        $target = "../uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        $image = $current_image;
    }

    // Update user details
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, dob = ?, image = ?, gender = ? WHERE email = ?");
    $update_stmt->bind_param("ssssss", $name, $new_email, $dob, $image, $gender, $email);

    if ($update_stmt->execute()) {
        $_SESSION['user'] = $new_email; 
        $message = "Profile updated successfully.";
        $email = $new_email; 
    } else {
        $message = "Error updating profile: " . $conn->error;
    }

    $update_stmt->close();
}

$conn->close();
?>

<?php include 'header.php'; ?>
<style>
    .main-content {
        margin-left: 120px;
        margin-top: 50px;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        height: calc(100vh - 60px); /* Adjust height to avoid overlap with header/footer */
    }
    .profile-box {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 1000px;
        max-width: 500px;
        background-color: #fff;
    }
    .message {
        color: green;
        font-weight: bold;
    }
    label {
        display: block;
        margin: 10px 0 5px;
    }
    input[type="text"],
    input[type="email"],
    input[type="date"],
    input[type="file"],
    select {
        width: 400px;
        padding: 8px;
        margin-bottom: 15px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    button {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        background-color: #28a745;
        color: white;
        cursor: pointer;
    }
    button:hover {
        background-color: #218838;
    }
</style>
<div class="main-content">
    <div class="profile-box">
        <h2>Edit Profile</h2>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="editprofile.php" method="post" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
            
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($current_dob); ?>" required>
            
            <label for="image">Profile Image:</label>
            <input type="file" id="image" name="image">
            <?php if ($current_image): ?>
                <img src="../uploads/<?php echo htmlspecialchars($current_image); ?>" alt="Profile Image" width="100">
            <?php endif; ?>
            
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="Male" <?php echo ($current_gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($current_gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($current_gender == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
            
            
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
