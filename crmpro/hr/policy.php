<?php
session_start();
include '../includes/db_connect.php';
include '../timeout_check.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'hr') {
    header('Location: ../login.php');
    exit();
}

$message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['policy_file'])) {
    $policy_type = $_POST['policy_type'];
    $new_policy_type = $_POST['new_policy_type'];
    $file = $_FILES['policy_file'];
    $upload_dir = '../uploads/';
    $file_path = $upload_dir . basename($file['name']);
    
    // Check if uploads directory exists, if not create it
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Use new policy type if "Other" is selected
    if ($policy_type === 'Other' && !empty($new_policy_type)) {
        $policy_type = $new_policy_type;
    }

    // Check if the policy type already exists
    $stmt = $conn->prepare("SELECT id FROM policies WHERE policy_type = ?");
    $stmt->bind_param("s", $policy_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Policy type exists, update the existing record
        $row = $result->fetch_assoc();
        $policy_id = $row['id'];
        
        $stmt = $conn->prepare("UPDATE policies SET file_path = ? WHERE id = ?");
        $stmt->bind_param("si", $file_path, $policy_id);
        
        if ($stmt->execute()) {
            // Remove old file if exists
            $stmt = $conn->prepare("SELECT file_path FROM policies WHERE id = ?");
            $stmt->bind_param("i", $policy_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $old_file = $result->fetch_assoc()['file_path'];
                if (file_exists($old_file)) {
                    unlink($old_file); // Delete the old file from the server
                }
            }
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $message = "Policy updated successfully!";
            } else {
                $message = "Failed to upload file.";
            }
        } else {
            $message = "Error: " . $stmt->error;
        }
    } else {
        // Policy type does not exist, insert new record
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $stmt = $conn->prepare("INSERT INTO policies (policy_type, file_path) VALUES (?, ?)");
            $stmt->bind_param("ss", $policy_type, $file_path);
            
            if ($stmt->execute()) {
                $message = "Policy uploaded successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
        } else {
            $message = "Failed to upload file.";
        }
    }
    $stmt->close();
}

// Handle policy deletion
if (isset($_GET['delete'])) {
    $policy_id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT file_path FROM policies WHERE id = ?");
    $stmt->bind_param("i", $policy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_path = $row['file_path'];
        $stmt = $conn->prepare("DELETE FROM policies WHERE id = ?");
        $stmt->bind_param("i", $policy_id);
        if ($stmt->execute()) {
            if (file_exists($file_path)) {
                unlink($file_path); // Delete the file from the server
            }
            $message = "Policy deleted successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } 
}

// Fetch existing policy types
$policiesQuery = "SELECT DISTINCT policy_type FROM policies";
$policyTypesResult = $conn->query($policiesQuery);

// Fetch all policies
$policiesQuery = "SELECT * FROM policies";
$policiesResult = $conn->query($policiesQuery);

$conn->close();
?>

<?php include 'header.php'; ?>

<style>
    .main-content {
            margin-left: 120px;
            margin-top: 60px;
            padding: 20px;
        }

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
    .form-container select,
    .form-container input[type="text"] {
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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .policy-item a {
        text-decoration: none;
        color: #3498db;
    }

    .policy-item button {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    .policy-item button:hover {
        background-color: #c0392b;
    }
</style>

<div class="form-container">
    <h2>Upload Policy</h2>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    <form method="post" enctype="multipart/form-data">
        <select name="policy_type" required>
            <option value="">Select Policy Type</option>
            <?php while ($row = $policyTypesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['policy_type']); ?>">
                    <?php echo htmlspecialchars($row['policy_type']); ?>
                </option>
            <?php endwhile; ?>
            <option value="Other">Other</option>
        </select>
        <input type="text" name="new_policy_type" placeholder="If 'Other', specify type">
        <input type="file" name="policy_file" accept="application/pdf" required>
        <button type="submit">Upload/Update Policy</button>
    </form>
</div>

<div class="policy-list">
    <h2>Existing Policies</h2>
    <?php while ($row = $policiesResult->fetch_assoc()): ?>
        <div class="policy-item">
            <p><strong><?php echo htmlspecialchars($row['policy_type']); ?></strong></p>
            <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View Policy</a>
            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this policy?');">
                <button>Delete</button>
            </a>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'footer.php'; ?>
