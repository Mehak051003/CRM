<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['policy_file'])) {
    $policy_type = $_POST['policy_type'];
    $file = $_FILES['policy_file'];
    $upload_dir = '../uploads/';
    $file_path = $upload_dir . basename($file['name']);
    
    // Check if uploads directory exists, if not create it
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $stmt = $conn->prepare("INSERT INTO policies (policy_type, file_path) VALUES (?, ?)");
        $stmt->bind_param("ss", $policy_type, $file_path);
        
        if ($stmt->execute()) {
            $message = "Policy uploaded successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Failed to upload file.";
    }
}

// Fetch existing policies
$policiesQuery = "SELECT * FROM policies";
$policiesResult = $conn->query($policiesQuery);

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
        margin-bottom: 20px;
    }

    .form-container h2 {
        margin-bottom: 20px;
        text-align: center;
    }

    .form-container input[type="file"],
    .form-container select {
        width: 100%;
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

    .policy-list {
        width: 80%;
        margin: auto;
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .policy-item {
        margin: 10px 0;
        padding: 10px;
        border-bottom: 1px solid #bdc3c7;
    }

    .policy-item a {
        text-decoration: none;
        color: #3498db;
    }
</style>

<div class="form-container">
    <h2>Upload Policy</h2>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    <form method="post" enctype="multipart/form-data">
        <select name="policy_type" required>
            <option value="">Select Policy Type</option>
            <option value="Company Policy">Company Policy</option>
            <option value="Buddy Referral Policy">Buddy Referral Policy</option>
        </select>
        <input type="file" name="policy_file" accept="application/pdf" required>
        <button type="submit">Upload Policy</button>
    </form>
</div>

<div class="policy-list">
    <h2>Existing Policies</h2>
    <?php while ($row = $policiesResult->fetch_assoc()): ?>
        <div class="policy-item">
            <p><strong><?php echo $row['policy_type']; ?></strong></p>
            <a href="<?php echo $row['file_path']; ?>" target="_blank">View Policy</a>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>
