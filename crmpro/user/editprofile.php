<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'User') {
    header('Location: ../login.php');
    exit();
}

$email = $_SESSION['user'];
$message = '';

// Fetch current user details
$stmt = $conn->prepare("SELECT name, email, role, designation, gender, dob, image FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$name = $user['name'];
$current_email = $user['email'];
$current_role = $user['role'];
$current_designation = $user['designation'];
$current_gender = $user['gender'];
$current_dob = $user['dob'];
$current_image = $user['image'];

// Fetch designation options
$designations = ["Developer", "Designer", "QA", "SEO", "System Admin"];
$genders = ["Male", "Female", "Other"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $new_email = $_POST['email'];
    $role = $_POST['role'];
    $designation = $_POST['designation'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    
    // Handle image upload if a new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $tmp_name = $_FILES['image']['tmp_name'];
        $image = basename($_FILES['image']['name']);
        $upload_file = $upload_dir . $image;

        // Move uploaded file to the designated folder
        if (move_uploaded_file($tmp_name, $upload_file)) {
            $image_path = $image;
        } else {
            $message = "Error uploading image.";
        }
    } else {
        $image_path = $current_image; // Keep current image if no new image is uploaded
    }

    // Update user details
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, designation = ?, gender = ?, dob = ?, image = ? WHERE email = ?");
    $update_stmt->bind_param("ssssssss", $name, $new_email, $role, $designation, $gender, $dob, $image_path, $email);

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
        margin-top: 80px;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        height: calc(100vh - 60px); /* Adjust height to avoid overlap with header/footer */
    }
    .profile-box {
        border: 1px solid #ddd;
        margin-top: 90px;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 1000px;
        max-width: 600px;
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
    select {
        width: 70%;
        padding: 8px;
        margin-bottom: 15px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    input[type="file"] {
        margin-bottom: 15px;
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
        <h2><center>Edit Profile</center></h2>
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="editprofile.php" method="post" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
            
            <label for="role">Role:</label>
            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($current_role); ?>" required readonly>
            
            <label for="designation">Designation:</label>
            <select id="designation" name="designation" required>
                <?php foreach ($designations as $designation): ?>
                    <option value="<?php echo htmlspecialchars($designation); ?>" <?php echo $designation === $current_designation ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($designation); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <?php foreach ($genders as $gender): ?>
                    <option value="<?php echo htmlspecialchars($gender); ?>" <?php echo $gender === $current_gender ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($gender); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($current_dob); ?>" required>
            <br>
            <label for="image">Profile Image:</label>
            <br>
            <input type="file" id="image" name="image">
            <br>
            <img src="../uploads/<?php echo $current_image; ?>" alt="Profile Image" style="width: 150px; height: 150px; object-fit: cover; margin-bottom: 15px;">
            <br><br>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
